<?php
 /**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Collection de quotation
 *
 */
class MDN_BackgroundTask_Model_Mysql4_Task_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('BackgroundTask/Task');
    }
    
    /**
     * Return next task to execute
     * Exclude grouped task
     *
     */
    public function getNextTaskToExecute($groupCode = null)
    {
    	$this->getSelect()->where('bt_executed_at is NULL');
    	$this->getSelect()->where('bt_result is NULL');
    	if ($groupCode == null)
	    	$this->getSelect()->where('bt_group_code is NULL');
	    else 
	    	$this->getSelect()->where("bt_group_code = '".$groupCode."'");
    	$this->getSelect()->order('bt_id asc');
    	$this->getSelect()->limit(1);
    	return $this;
    }


}