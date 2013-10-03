<?php

class BEST_Watchtower_Helper_Db extends Mage_Core_Helper_Abstract
{
    function killTransaction($code)
    {
        try {
            $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
            $conn->query("KILL ?", $code);
            return "The fixing code was executed for $code";
        } catch (Exception $e) {
            return "Error for $code: " . $e->getMessage();
        }
    }

    function killTransactions($transactions, $not_a_drill)
    {
        $report = "";
        foreach ($transactions as $t) {
            if ($not_a_drill) {
                $this->killTransaction($t['trx_mysql_thread_id']);
            }
//            $report .= "Transaction id:".$t['trx_mysql_thread_id']."\n";
            $report .= "Killed TXN running query:" . $t['trx_query'] . "\n";
            $report .= "  State:" . $t['trx_state'] . "\n";
            $report .= "  Time:" . (time() - strtotime($t['trx_started'])) . " seconds\n";
            $report .= "  Additional Info \n";
            $report .= "  Lock Memory Bytes:" . $t['trx_lock_memory_bytes'] . "\n";
            $report .= "  Rows locked:" . $t['trx_rows_locked'] . "\n";
            $report .= "  Tables locked:" . $t['trx_tables_locked'] . "\n";
        }
        $count = count($transactions);
        if ($count) {
            echo $report;
            Mage::helper("watchtower/trautman")->report("Rogue transactions killed: $count", $report);
        } else {
            echo "No rogue transactions, sir!" . PHP_EOL;
        }
    }
}

?>
