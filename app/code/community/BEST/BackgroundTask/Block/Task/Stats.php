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
class BEST_BackgroundTask_Block_Task_Stats extends Mage_Adminhtml_Block_Widget_Form
{
	public function getRemainToExecuteCount()
	{
        $ret = "<ul>";
        $stats = mage::helper("BackgroundTask")->getBeanstalkdWrite()->stats();
        $ret .= "<li><h1>Beanstalkd</h1><dl>";
        $stats_to_show = array("current-jobs-reserved"=>"Working", "current-workers"=>"Working");
        foreach($stats_to_show as $key=>$name){
            $value = $stats[$key];
            $ret .= "<dt>$key</dt><dd>$value</dd>";
        }
        $ret .= "</dl></li>";
        $tubes = mage::helper("BackgroundTask")->getBeanstalkdWrite()->listTubes();
        foreach ($tubes as $tube){
            $stats = mage::helper("BackgroundTask")->getBeanstalkdWrite()->statsTube($tube);
            $ret .= "<li><h2>$tube</h2><dl>";
            $stats_to_show = array("current-jobs-ready"=>"Pending", "current-jobs-reserved"=>"Working", "current-watching"=>"Working");
            foreach($stats_to_show as $key=>$name){
                $value = $stats[$key];
                $ret .= "<dt>$key</dt><dd>$value</dd>";
            }
            $ret .= "</dl></li>";
        }

        return $ret .= "</ul>";
//		$tablePrefix = mage::getModel('BackgroundTask/Constant')->getTablePrefix();
//		$sql = '
//					SELECT COUNT( * )
//					FROM  '.$tablePrefix.'backgroundtask
//					WHERE  bt_result IS NULL
//					and bt_group_code is null;
//				';
//
//		$retour = (int)mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
//		return $retour;
	}

	public function getCronExecutionStats()
	{
		$tablePrefix = mage::getModel('BackgroundTask/Constant')->getTablePrefix();
		$sql = '
				SELECT  `bt_executed_at` , SUM( bt_duration ) as sum_duration , COUNT( * )  as count_task
				FROM  '.$tablePrefix.'backgroundtask
				WHERE bt_group_code is null
				GROUP BY bt_executed_at
				ORDER BY  bt_executed_at DESC
				LIMIT 0 , 30;
				';

		return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);
	}

	public function getStatsPerMethod()
	{
		$tablePrefix = mage::getModel('BackgroundTask/Constant')->getTablePrefix();
		$sql = '
				SELECT `bt_helper`, `bt_method`, count(*) as record_count, avg(bt_duration) as avg_duration, SUM( bt_duration ) as sum_duration
				FROM '.$tablePrefix.'backgroundtask
				WHERE 1
				group by `bt_helper`, `bt_method`
				order by avg(bt_duration) desc

				';

		return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);

	}

	public function getBackUrl()
	{
		return $this->getUrl('BackgroundTask/Admin/Grid');
	}


}