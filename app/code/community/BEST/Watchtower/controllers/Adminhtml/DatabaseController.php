<?php

class BEST_Watchtower_Adminhtml_DatabaseController extends BEST_Watchtower_Controller_Abstract
{
    public function killAction()
    {
        $code = $this->getRequest()->getParam("t");
        Mage::helper("watchtower/db")->killTransaction($code);
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/dbstatus");
    }
}

?>
