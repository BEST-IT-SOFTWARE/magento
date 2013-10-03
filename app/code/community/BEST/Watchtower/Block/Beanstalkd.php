<?php

class BEST_Watchtower_Block_Beanstalkd extends BEST_Watchtower_Block_Abstract
{

    static $CACHE_KEY = "WATCHTOWSER_BEANSTALKD";

    private $phean = null;

    protected function loadInfo()
    {
        $this->phean = Mage::getSingleton("watchtower/beanstalkd");
        $stats = $this->phean->stats();
        $tubes = $this->phean->listTubes();
        return array("stats" => $stats, "tubes" => $tubes);
    }

    protected function storage()
    {
        return Mage::app()->getCache()->getBackend()->getRedis();
    }

    protected function smallMode($info)
    {
        $table = "<table>";
        $keys = array(
            "ready"    => "current-jobs-ready",
            "reserved" => "current-jobs-reserved",
            "delayed"  => "current-jobs-delayed",
            "buried"   => "current-jobs-buried",
            "workers"  => "current-watching",
        );
        $table .= "<tr><th>Tube</th>";
        foreach ($keys as $n => $dt) {
            $table .= "<td>$n</td>";
        }
        $table .= "</tr>";
        foreach ($info["tubes"] as $tube) {
            $st = $this->phean->statsTube($tube);
            if (($st["current-jobs-ready"] + $st["current-jobs-delayed"] + $st["current-jobs-buried"]
                    + $st["current-jobs-reserved"]) == 0
            ) {
                continue;
            }
            $table .= "<tr><th>$tube</th>";
            foreach ($keys as $dt) {
                $dd = $st[$dt];
                $table .= "<td>$dd</td>";
            }
            $table .= "</tr>";
        }
        $table .= "</table>";
        return $table;
    }

    protected function largeMode($info)
    {
        $table = "<h1>" . $info['stats']["pid"] . "</h1>";
        $table .= "<table>";
        foreach ($info['stats'] as $key => $data) {
            $table .= "<tr><th>$key</th><td>$data</td></tr>";
        }
        $delete_url = $this->getUrl("watchtower/adminhtml_beanstalkd/delete");
        $delete_all_buried_url = $this->getUrl("watchtower/adminhtml_beanstalkd/deleteAllBuried");
        $delete_all_delayed_url = $this->getUrl("watchtower/adminhtml_beanstalkd/deleteAllDelayed");
        $kick_url = $this->getUrl("watchtower/adminhtml_beanstalkd/kick");
        $kick_all_url = $this->getUrl("watchtower/adminhtml_beanstalkd/kickAll");
        foreach ($info['tubes'] as $tube) {
            $table .= "</table><table><tr><th colspan=2 style='text-align:center'><h2>$tube</h2></th></tr>";
            foreach ($this->phean->statsTube($tube) as $key => $data) {
                $table .= "<tr><th>$key</th><td>$data</td></tr>";
            }
            try {
                $task = $this->phean->peekReady($tube);
                $table .= "<tr><th>Ready</th><td>{$task->getData()}</td>";
                $table .= "<td><a href='{$delete_url}id/{$task->getId()}'>Delete</a></td></tr>";
            } catch (Exception $e) {
            }
            try {
                $task = $this->phean->peekDelayed($tube);
                $table .= "<tr><th>Delayed</th><td>{$task->getData()}</td>";
                $errors = $this->storage()->get("TASK_FAILED_" . $task->getId());
                $table .= "<td>$errors</td>";
                $table .= "<td><a href='{$delete_url}id/{$task->getId()}'>Delete</a></td>";
                $table .= "<td><a href='{$delete_all_delayed_url}tube/{$tube}'>Delete All</a></td>";
                if (!$info['stats']['current-jobs-buried']) {
                    $table .= "<td><a href='{$kick_url}id/{$task->getId()}'>Kick</a></td>";
                    $table .= "<td><a href='{$kick_all_url}tube/{$tube}'>Kick All</a></td>";
                }
                $table .= "</tr>";

            } catch (Exception $e) {
            }
            try {
                $task = $this->phean->peekBuried($tube);
                $table .= "<tr><th>Buried</th><td>{$task->getData()}</td>";
                $error = $this->storage()->get("TASK_FAILED_MESSAGE_" . $task->getId());
                $table .= "<td>$error</td>";
                $table .= "<td><a href='{$delete_url}id/{$task->getId()}'>Delete</a></td>";
                $table .= "<td><a href='{$delete_all_buried_url}tube/{$tube}'>Delete All</a></td>";
                $table .= "<td><a href='{$kick_url}id/{$task->getId()}'>Kick</a></td>";
                $table .= "<td><a href='{$kick_all_url}tube/{$tube}'>Kick All</a></td>";
                $table .= "</tr>";
            } catch (Exception $e) {
            }
        }

        $table .= "</table>";
        return $table;
    }

}

?>
