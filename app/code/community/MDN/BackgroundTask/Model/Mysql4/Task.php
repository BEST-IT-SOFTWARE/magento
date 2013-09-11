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
class MDN_BackgroundTask_Model_Mysql4_Task extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('BackgroundTask/Task', 'bt_id');
    }
    
    /**
     * Delete tasks depending of config
     *
     */
    public function deleteTasks()
    {
    	$dbh = $this->_getWriteAdapter();
    	$ExpireTimeStamp = time() - mage::getStoreConfig('backgroundtask/general/history_duration') * 3600;
    	$ExpireDate = date('Y-m-d H:i', $ExpireTimeStamp);
		$deleteErrorTask = mage::getStoreConfig('backgroundtask/general/delete_error_tasks');
	    	$condition = ' bt_executed_at is not null ';
    	if (!$deleteErrorTask)
	    	$condition .= " and bt_result <> 'error'";
	    $condition .= " and bt_executed_at < '".$ExpireDate."'";
	    try 
	    {
		    $dbh->delete($this->getTable('BackgroundTask/Task'), $condition);	    	
	    }
		catch (Exception $ex)
		{
			throw new Exception("Error while deleting tasks : ".$ex->getMessage());
		}
    }
    
        
    /**
     * delete tasks for a group
     *
     */
    public function deleteGroupTasks($groupCode)
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "bt_group_code='".$groupCode."'");
    	return $this;
    }
    
    public function deleteAllTasks()
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "");
    	return $this;    	
    }
    
    public function deleteAllGroupTasks()
    {
    	$this->_getWriteAdapter()->delete($this->getMainTable(), "bt_group_code <> ''");
    	return $this;    	
    }
}
?>