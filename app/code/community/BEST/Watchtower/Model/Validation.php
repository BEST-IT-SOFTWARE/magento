<?php

abstract class BEST_Watchtower_Model_Validation
{
    const CACHE_KEY = "VALIDATIONS_RESULTS";
    const VALIDATION_TIMEOUT = 10;
    protected $_autoFix = true;
    protected $_code;

    public function canAutoFix()
    {
        return $this->_autoFix && $this->canFix();
    }

    public function canFix()
    {
        return $this->hasFix()
        && !$this->getCache()->isLocked("FIX_LOCK_" . get_class($this))
        && $this->hasErrors()
        && !$this->overTaskLimit();
    }

    public function hasFix()
    {
        return method_exists($this, "fix");
    }

    public function getCache()
    {
        return Mage::app()->getCache()->getBackend();
    }

    public function hasErrors()
    {
        return count($this->getResults()) > 0;
    }

    public function getResults()
    {
        $cached = Mage::app()->loadCache($this->getCacheKey());
        
        $timer = Mage::app()->loadCache($this->getCacheKeyTimer());
       
        if ($cached && $timer) { //Data is still valid
            return unserialize($cached);
        } else {
            if ($cached && !$this->lockCalculateResults()) { //Data exists and can't get the lock
                return unserialize($cached);
            } else {
                //Recalculate
                $start = microtime();
                $sql = $this->getQuery();
                $data = $this->exec($sql);
                Mage::app()->saveCache(
                    serialize($data), $this->getCacheKey(), array(self::CACHE_KEY), self::VALIDATION_TIMEOUT * 10
                );
                Mage::app()->saveCache(
                    true, $this->getCacheKeyTimer(), array(self::CACHE_KEY), self::VALIDATION_TIMEOUT
                );
                $this->unlockCalculateResults();
                $this->duration = microtime() - $start;
            }
            return $data;
        }
    }

    public function getCacheKey()
    {
        return self::CACHE_KEY . get_class($this);
    }

    public function getCacheKeyTimer()
    {
        return self::CACHE_KEY . get_class($this) . "_TIMER";
    }

    public function lockCalculateResults()
    {
        return $this->getCache()->lock("RESULTS_LOCK_" . get_class($this), 120);
    }

    abstract public function getQuery();

    protected function exec($sql, $fetch = true)
    {
        $write = Mage::getSingleton('core/resource')->getConnection('defaul_write_connection');
        if ($fetch) {
            return $write->fetchAll($sql);
        } else {
            $write->query($sql);
        }
    }

    public function unlockCalculateResults()
    {
        return $this->getCache()->unlock("RESULTS_LOCK_" . get_class($this));
    }

    public function overTaskLimit()
    {
        $this->phean = Mage::getSingleton("watchtower/beanstalkd");
        $tubes = $this->phean->listTubes();
        $total = 0;
        foreach ($this->getObservedTubes() as $tube) {
            if (in_array($tube, $tubes)) {
                $stats = $this->phean->statsTube($tube);
                $total += $stats["current-jobs-ready"];
            }
        }
        return $total > 1000;
    }

    public function getObservedTubes()
    {
        return array("fixing_bird");
    }

    public function doFix()
    {
        if ($this->lockFix()) {
            $this->fix();
        }
    }

    public function lockFix()
    {
        return $this->getCache()->lock("FIX_LOCK_" . get_class($this), 120);
    }

    public function clearCache()
    {
        Mage::app()->removeCache($this->getCacheKey());
    }

    function getMaster()
    {
        return (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    }

    public function getResultsText($showAll)
    {
        $results = $this->getResults();
        $resultsCount = count($results);
        $subject = $this->getSubject();
        if ($resultsCount) {
            if (!$showAll) {
                $results = array_slice($results, 0, 100);
            }
            $content = $this->convertResults($results);
            $content = "<h1>There are $resultsCount $subject</h1>" . $content;
            return $content;
        } else {
            return $subject;
        }
    }

    public function getSubject()
    {
    }

    abstract public function convertResults($results);

    public function getCode()
    {
        return $this->_code;
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function getResultsSummary()
    {
        $results = $this->getResults();
        $c = count($results);
        if ($c) {
            return $this->getSubject() . ": " . $c;
        } else {
            return null;
        }
    }

    public function runValidation()
    {
        $results = $this->getResults();
        if (count($results)) {
            return $this->sendResultEmail($results);
        } else {
            return true;
        }
    }

    public function explain()
    {
        $q = $this->explainQuery();
        echo $q . PHP_EOL;
        return array($q => $this->exec($q, true));
    }

    public function explainQuery()
    {
        $select = $this->getQuery();
        return "explain $select ";
    }

    protected function execAll($sql)
    {
        $sqls = explode(";", $sql);
        foreach ($sqls as $sql) {
            if (trim($sql) != "") {
                $this->exec($sql, false);
            }
        }
    }
}

?>
