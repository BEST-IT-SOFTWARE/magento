<?php

class BEST_Watchtower_Block_Scalr extends BEST_Watchtower_Block_Abstract
{

//    static $CACHE_KEY = "WATCHTOWSER_SCALR";

    protected function loadInfo()
    {
        $this->scalr = new Scalr_Api();
        $stats = $this->scalr->FarmGetDetails(array("FarmID" => 9718));
        return $stats;
    }

    protected function smallMode($info)
    {
        $table = "<table>";
        $table .= "</table>";
        return $table;

    }

    protected function largeMode($info)
    {
        $table = "<table>";
        foreach ($info['stats'] as $key => $data) {
            $table .= "<tr><th>$key</th><td>$data</td></tr>";
        }

        $table .= "</table>";
        return $table;
    }

}

?>
