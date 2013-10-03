<?php

class BEST_Watchtower_Block_Indexing extends BEST_Watchtower_Block_Abstract
{

    static $CACHE_KEY = "WATCHTOWSER_INDEXING";

    protected function loadInfo()
    {
        $eventsCollection = Mage::getResourceModel('index/process_collection');
        $eventsCollection->getSelect()->joinInner(
            array("pe" => "index_process_event"), "pe.process_id = main_table.process_id"
        );
        $eventsCollection->getSelect()->where("pe.status='new'");
        //$eventsCollection->addProcessFilter(array(1,2,3,4,5,6,7,8,9,10,11), Mage_Index_Model_Process::EVENT_STATUS_NEW);
        return $eventsCollection;
    }

    protected function largeMode($info)
    {
//        $this->renderLink("Indexing", "admin/process/list");
        $select = $info->getSelect();
        $select->group("main_table.process_id");
        $select->reset("columns");
        $select->columns(array("main_table.indexer_code", "c" => "COUNT(*)"));
        $indexes = $info->getData();
        $table = "<table>";
        foreach ($indexes as $data) {
            $table .= "<tr><th>{$data["indexer_code"]}</th><td>{$data["c"]}</td></tr>";
        }

        $table .= "</table>";
        return $table;
    }

}

?>
