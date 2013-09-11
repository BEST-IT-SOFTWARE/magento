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
class MDN_BackgroundTask_Model_Mysql4_Taskgroup extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('BackgroundTask/Taskgroup', 'btg_id');
    }
    
    public function loadByGroupCode($groupCode)
    {
        $select = $this->_getReadAdapter()->select()->from($this->getTable('BackgroundTask/Taskgroup'))
            ->where('btg_code=:btg_code');
        $datas = $this->_getReadAdapter()->fetchRow($select, array('btg_code'=>$groupCode));
        if ($datas)
        	return mage::getModel('BackgroundTask/Taskgroup')->load($datas['btg_id']);
        else 
        	return null;
    }
    
    public function deleteAllGroups()
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "");
    	return $this;    	
    }
}