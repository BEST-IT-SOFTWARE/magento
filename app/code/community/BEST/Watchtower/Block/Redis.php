<?php

class BEST_Watchtower_Block_Redis extends BEST_Watchtower_Block_Abstract
{

    static $CACHE_KEY = "WATCHTOWSER_REDIS";

    protected function loadInfo()
    {
        $redis = Mage::app()->getCache()->getBackend()->getRedis();
        return $redis->info();
    }

    protected function smallMode($info)
    {
        return $this->createTable($info, array("connected_clients", "used_memory_human", "used_memory_peak_human"));
    }

    protected function largeMode($info)
    {
        return $this->createTable($info, array_keys($info));
    }

    protected function createTable($info, $rows)
    {
        $table = "<table>";
        foreach ($rows as $dt) {
            $dd = $info[$dt];
            $table .= "<tr><th>$dt</th><td>$dd</td></tr>";
        }
        $table .= "</table>";
        return $table;
    }
}

?>