<?php

class BEST_Watchtower_Block_Supervisord extends BEST_Watchtower_Block_Abstract
{

    function loadInfo()
    {
        $super = new Supervisor_Supervisord();
        return $super->getAllProcessInfo();
    }

    protected function smallMode($info)
    {
        $groups = array();
        foreach ($info as $proc) {
            @$groups[$proc["group"]][$proc["statename"]]++;
        }


        $table = "<table>";
        $table .= "<tr><th>GROUP</th><td>STATE</td><td>COUNT</td></tr>";
        $url_start = $this->getUrl("watchtower/adminhtml_supervisord/startGroup");
        $url_stop = $this->getUrl("watchtower/adminhtml_supervisord/stopGroup");
        foreach ($groups as $name => $data) {
            foreach ($data as $state => $count) {
                $table .= "<tr><th>{$name}</th><td>{$state}</td><td>{$count}</td>";
                $table .= "<td><a href='#' onclick='new Ajax.Request(\"{$url_start}task/$name\")'>Start</a>
                                <a href='#' onclick='new Ajax.Request(\"{$url_stop}task/$name\")'>Stop</a>
                          </td></tr>";
            }
        }
        $table .= "</table>";
        return $table;
    }

    protected function largeMode($info)
    {
        $text = $this->smallMode($info);
        $table = "<table>";
        $rows = array_keys($info[0]);
        $url_start = Mage::helper('adminhtml')->getUrl("watchtower/adminhtml_supervisord/start");
        $url_stop = Mage::helper('adminhtml')->getUrl("watchtower/adminhtml_supervisord/stop");
        foreach ($info as $proc) {
            $name = $proc["group"] . ":" . $proc["name"];
            $table .= "<tr><th colspan=2><h2>$name</h2></th></tr>";
            foreach ($rows as $dt) {
                $dd = $proc[$dt];
                $table .= "<tr><th>$dt</th><td>$dd</td></tr>";
            }
            $table .= "<tr><th>Commands</th>
                        <td><a href='#' onclick='new Ajax.Request(\"{$url_start}task/$name\")'>Start</a>
                            <a href='#' onclick='new Ajax.Request(\"{$url_stop}task/$name\")'>Stop</a>
                      </td></tr>";
        }
        $table .= "</table>";
        return $text . $table;
    }

}

?>
