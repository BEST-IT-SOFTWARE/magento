<?php

class BEST_Watchtower_Block_Dbstatus extends BEST_Watchtower_Block_Abstract
{

    protected function loadInfo()
    {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $info["locks"] = $conn->fetchAll(
            "SELECT r.trx_id waiting_trx_id,
                                r.trx_mysql_thread_id waiting_thread,
                                r.trx_wait_started trx_wait_started,
                                r.trx_query waiting_query,
                                b.trx_id blocking_trx_id,
                                b.trx_mysql_thread_id blocking_thread,
                                b.trx_query blocking_query
                            FROM       information_schema.innodb_lock_waits w
                            INNER JOIN information_schema.innodb_trx b  ON
                             b.trx_id = w.blocking_trx_id
                           INNER JOIN information_schema.innodb_trx r  ON
                         r.trx_id = w.requesting_trx_id order by r.trx_wait_started asc;"
        );
        $info["trx"] = $conn->fetchAll("SELECT * from information_schema.innodb_trx order by trx_started asc");
        $url = $this->getUrl("watchtower/adminhtml_database/kill");
        foreach ($info["trx"] as $id => $trx) {
            $info["trx"][$id]["Kill"]
                = "<a href='#' onclick='new Ajax.Request(\"" . $url . "t/" . $trx["trx_mysql_thread_id"]
                . "\")'>Kill</a>";
        }

        return $info;
    }

    protected function smallMode($info)
    {
        $table = "<table>";
        $table .= "<tr><th>Current Open Transactions</th><td>" . count($info["trx"]) . "</td></tr>";
        if (isset($info["trx"][0])) {
            $table
                .= "<tr><th>Oldest transaction</th><td>" . (time() - strtotime($info["trx"][0]["trx_started"])) . "s "
                . $info["trx"][0]["Kill"] . "</td></tr>";
        }
        $table .= "<tr><th>Current Locks</th><td>" . count($info["locks"]) . "</td></tr>";
        if (isset($info["locks"][0])) {
            $table .= "<tr><th>Oldest Lock</th><td>" . (time() - strtotime(
                        $info["locks"][0]["trx_wait_started"]
                    )) . "s</td></tr>";
        }
        $table .= "</table>";
        return $table;
    }

    protected function largeMode($info)
    {
        $table = "";
        $table .= $this->getTable("Current Transactions", $info["trx"]);
        $table .= $this->getTable("Current Locks", $info["locks"]);
        return $table;
    }

    function getTable($title, $rows)
    {
        $table = "<table><tr><th>$title</th></tr>";
        array_unshift($rows, array_keys($rows[0]));
        foreach ($rows as $data) {
            $table .= "<tr>";
            foreach ($data as $datum) {
                $table .= "<td>" . $datum . "</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</table>";
        return $table;
    }

}

?>
