<?php

class BEST_Watchtower_Block_Reports extends BEST_Watchtower_Block_Abstract
{

    static $CACHE_KEY = "WATCHTOWER_REPORTS";

    protected function loadInfo()
    {
        $info["cube"] = array();
        $cube = Mage::helper("spreports/cube");
        $factory = $cube->getFactory();
        foreach ($cube->getFacts(null) as $fact) {
            $info["cube"][$fact]
                = array(
                "Missing" => $factory->instantiateMissingFactsFinder($fact)->countMissingValues(1),
            );
        }
        $info["cached"] = array();
        $cached = Mage::helper("spreports");
        $info["cached"]["Info"]
            = array(
            "Outdated" => $cached->countInvalidReports()
        );
        return $info;
    }

    protected function smallMode($info)
    {
        $table = "<table>";
        foreach ($info as $name => $reports) {
            $table .= "<tr><th colspan=3><h3>{$name}</h3></th>" .
                "</td></tr>";
            foreach ($reports as $title => $data) {
                foreach ($data as $key => $value) {
                    $table .= "<tr><th>{$title}</th><td>{$key}</td><td>{$value}</td>" .
                        "</td></tr>";
                    $title = "";
                }
            }
        }
        $table .= "</table>";
        return $table;
    }
}