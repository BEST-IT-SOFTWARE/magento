<?php

require_once Mage::getBaseDir("lib") . DS . "pheanstalk" . DS . "pheanstalk_init.php";

class BEST_Watchtower_Helper_Data extends Mage_Core_Helper_Abstract
{

    const MAX_TRANSACTION_TIME = 10; //minutes
    const MAX_MEM_PROCESS = 5;
    const MAX_TIME_PROCESS = 10;

    static $watchedProcesses
        = array(
            "/bgtask/RunBeanstalkdTasks.php",
            "/bgtask/Watchtower.php",
        );
    static $validations
        = array(
            "backend" => array(
               "totalproducts"   => "watchtower/validation_totalproducts",
            ),
            );

    public function call($args)
    {
        $script = array_shift($args);
        $type = array_shift($args);
        if ($type == "projections") {
            $this->callProjections($args);
        } else {
            if ($type == "nonrt_projections") {
                $this->callNonrtProjections($args);
            } else {
                if ($type == "validations") {
                    $this->callValidations($args);
                } else {
                    if ($type == "agent47") {
                        $this->callAgent47($args);
                    } else {
                        echo "Unknown WT group $type" . PHP_EOL;
                    }
                }
            }
        }
    }

   
    public function callValidations($args)
    {
        if (!empty($args[1]) && $args[1] != "all") {
            $validations = explode(",", $args[1]);
        } else {
            $validations = array_keys($this->getValidations());
        }

        $this->{$args[0] . "Validations"}($validations);
    }

    public function getValidations()
    {
        $validations = array();
        foreach (self::$validations as $validationGroup) {
            $validations = array_merge($validations, $validationGroup);
        }
        return $validations;
    }

    public function callAgent47($args)
    {
        $notADrill = $args[0];
        $this->hitRogueDbTransactions($notADrill);
        $this->hitRoguePhpProcesses($notADrill);
    }

    public function hitRogueDbTransactions($notADrill)
    {
        //get transactions running for more than x minutes
        $time = self::MAX_TRANSACTION_TIME;
        $query = "SELECT *
               FROM information_schema.innodb_trx
               WHERE trx_started <= (NOW() - INTERVAL $time MINUTE)
               ORDER BY trx_started ASC;";
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $transactions = $conn->fetchAll($query);
        Mage::helper("watchtower/db")->killTransactions($transactions, $notADrill);
    }

    public function hitRoguePhpProcesses($notADrill)
    {
        $processes = shell_exec("ps aux | awk '{ print $2, $12, $4,$10, $13 }' | grep '.php'");
        $processList = explode("\n", $processes);
        $count = 0;
        $total = 0;
        $report = "";
        foreach ($processList as $p) {
			$p = explode(" ", $p);
			// default missing values to null to avoid undefined indexes
			$default = array_fill(0, 4, '');
			$p = array_merge($default, $p);
            list($pid, $fullScript, $mem, $time, $params) = $p;
            $script = substr($fullScript, strlen(Mage::getBaseDir()));
            if (in_array($script, self::$watchedProcesses)) {
                $total++;
                if ($mem >= self::MAX_MEM_PROCESS
                    && $time >= self::MAX_TIME_PROCESS
                ) { //Process is rogue
                    if ($mem >= self::MAX_MEM_PROCESS * 2
                        || $time >= self::MAX_TIME_PROCESS * 2
                    ) { //Process is on a spree!
                        $report .= "Killing $script $params: Hardly" . PHP_EOL;
                        if ($notADrill) {
                            $result = posix_kill($pid, SIGKILL);
                        }
                    } else {
                        $report .= "Killing $script $params: Softly" . PHP_EOL;
                        if ($notADrill) {
                            $result = posix_kill($pid, SIGTERM);
                        }
                    }
                    $count++;

                    $report .= "  Status $result: Process was using $mem % of memory " .
                        "and had been running for $time" . PHP_EOL;
                }
            }
        }
        if ($count > 0) {
            echo $report;
            Mage::helper("watchtower/trautman")->report("Rogue processes killed: $count", $report);
        } else {
            echo $total . " processes checked, all clear!" . PHP_EOL;
        }
    }

  
    public function fixValidations($validations)
    {
        foreach ($validations as $val) {
            $v = $this->getValidation($val);
            if ($v->canAutoFix()) {
                $this->log("Fixing bird clicked on $val");
                $this->queueFix($val);
            }
        }
    }

    public function getValidation($code)
    {
        $validations = $this->getValidations();
        $validation = Mage::getSingleton($validations[$code]);
        $validation->setCode($code);
        return $validation;
    }

    public function log($message)
    {
        echo $message . PHP_EOL;
        Mage::log($message, null, "watchtower_fixingbird.log");
    }

    public function queueFix($val)
    {
        Mage::helper("BackgroundTask")->addTask(
            "Launch fix for validation $val", "watchtower", "doFix", $val, "fixing_bird"
        );
    }

    public function doFix($val)
    {
        $this->getValidation($val)->doFix();
    }

   
    public function processExplains($explains)
    {
        foreach ($explains as $query => $exp) {
            foreach ($exp as $row) {
                if (
                    ($row["type"] == "ALL" && $row["rows"] > 1000000) || ($row["Extra"] == 'const row not found')
                    || (strpos($row["Extra"], 'filesort') !== false)
                    || (strpos($row["Extra"], 'Impossible') !== false)
                    || (strpos($row["Extra"], 'temporary') !== false)
                ) {
                    $this->problems[] = array($query, $row);
                    print_r($this->problems);
                    exit;
                } else {
                    if (
                        ($row["type"] == "ref" && $row["key_len"] < "1000") || ($row["select_type"] == "UNION RESULT")
                        || ($row["select_type"] == "PRIMARY" && $row["rows"] < "10000")
                        || ($row["type"] == "range" && $row["key_len"] < "1000")
                        || ($row["type"] == "ALL" && $row["rows"] < "100000")
                        || ($row["type"] == "ALL" && $row["rows"] < "1000000" && strpos($row["Extra"], "where")
                            && $row["select_type"] == "PRIMARY")
                        || ($row["type"] == "ALL" && $row["rows"] < "1000000" && strpos($row["Extra"], "where")
                            && $row["select_type"] == "UNION")
                        || ($row["type"] == "index" && $row["key_len"] < "1000")
                        || ($row["type"] == "index_subquery" && $row["key_len"] < "1000")
                        || ($row["type"] == "eq_ref")
                    ) {
                        $this->ok[] = $row;
                    } else {
                        echo "Do not know how to classify the row of $query";
                        print_r($row);
                        exit;
                    }
                }
            }
        }
    }

    public function showExplainResults()
    {
        $errors = count($this->problems);
        $oks = count($this->ok);
        foreach ($this->problems as $error) {
            print_r($error);
        }
        echo "\n$oks rows ok, and $errors errors\n";
    }

  
    public function getGroupedValidations()
    {
        return self::$validations;
    }

}