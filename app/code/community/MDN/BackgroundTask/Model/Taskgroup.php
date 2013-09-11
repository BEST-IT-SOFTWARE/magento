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
class MDN_BackgroundTask_Model_Taskgroup extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('BackgroundTask/Taskgroup');
	}	
	
	/**
	 * Return progress percent
	 *
	 */
	public function getProgressPercent()
	{
		if ($this->getbtg_task_count() > 0)
			$value = $this->getbtg_executed_tasks() / $this->getbtg_task_count() * 100;
		else 
			$value = 100;
		return (int)$value;
	}
	
	/**
	 * Execute tasks (limited by max execution time)
	 *
	 */
	public function execute()
	{
		$startTime = time();
		$hasTask = true;
		$maxExecutionTime = mage::getStoreConfig('backgroundtask/general/max_execution_time');
		$result = '';
		while (((time() - $startTime) < $maxExecutionTime) && ($hasTask))
		{
			//collect next task to execute
			$task = $this->getNextTaskToExecute();

			//execute task
			if ($task)
			{
				$task->execute();
				$this->setbtg_executed_tasks($this->getbtg_executed_tasks() + 1)->save();
				if ($task->getbt_result() == 'error')
					$result .= $task->getbt_result_description();
			}
			else 
			{
				//no task to execute, quit loop
				$hasTask = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Return next task to execute
	 *
	 */
	public function getNextTaskToExecute()
	{
		$collection = mage::getResourceModel('BackgroundTask/Task_Collection')->getNextTaskToExecute($this->getbtg_code());
		foreach($collection as $item)
		{
			return $item;
		}
	}
	
	/**
	 * Delete group's tasks
	 *
	 */
	protected function _afterDelete()
	{
		mage::getResourceModel('BackgroundTask/Task')->deleteGroupTasks($this->getbtg_code());
	}
	
	/**
	 * Update task count
	 */
	public function updateTaskCount()
	{
		$sql = "select count(*) from ".Mage::getConfig()->getTablePrefix()."backgroundtask where bt_group_code = '".$this->getbtg_code()."'";
		$count = mage::getResourceModel('cataloginventory/stock_item_collection')->getConnection()->fetchOne($sql);
		$this->setbtg_task_count($count)->save();
		return $this;
	}

}