<?php
class BEST_Watchtower_Model_Validation_Totalproducts extends BEST_Watchtower_Model_Validation
{
    public $_tooltip
        = "Total Orders ";

    public function fix()
    {
        $res = $this->getResults();
     /*   $this->exec(
            'update sales_flat_order set state=status where (state,status) in (("processing","complete"));', false
        );
      */
        foreach ($res as $data) {
           //do stuff here
           $id = 1;
         //TO DO 
          /*  Mage::helper('BackgroundTask')->AddTask(
                'Dispatch fixingbird #' . $id,
                '<MODULE HELPER>',
                '<FUNCTION>',
                <PARAMS>,
                "fixing_bird",
                array("key" => <TASK KEY>)
            );
*/
        }
    }

    public function getQuery()
    {
        $master = $this->getMaster();
        $sql = "select
                entity_id, type_id, sku
            from
                $master.catalog_product_entity;";
        return $sql;
    }

    public function getSubject()
    {
        return "total orders";

    }

    public function convertResults($results)
    {
        $content = "<ul>";
        foreach ($results as $item) {
            $id = $item["entity_id"];
            $type_id = $item["type_id"];
            $sku = $item["sku"];
            $content .= "<li>The Product $id has type $type_id and sku $sku</li>";
        }
        $content .= "</ul>";
        return $content;
    }
}

?>
