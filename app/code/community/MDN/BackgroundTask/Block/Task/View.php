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
class MDN_BackgroundTask_Block_Task_View extends Mage_Adminhtml_Block_Widget_Form
{
	public function getTask()
	{
		$btId = mage::app()->getRequest()->getParam('bt_id');
		return mage::getModel('BackgroundTask/Task')->load($btId);
	}
	
	public function getBackUrl()
	{
		return $this->getUrl('BackgroundTask/Admin/Grid');
	}
	
	public function getReplayUrl()
	{
		return $this->getUrl('BackgroundTask/Admin/Replay', array('bt_id' => $this->getTask()->getId()));
	}
}