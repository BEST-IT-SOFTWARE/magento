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
class MDN_BackgroundTask_Block_Task_GroupExecute extends Mage_Adminhtml_Block_Widget_Form
{
	private $_groupCode = null;
	private $_group = null;

	/**
	 * Constructor, load group
	 *
	 */
	public function __construct()
    {
        parent::__construct();
        $this->_groupCode = $this->getRequest()->getParam('group_code');
    }

    public function getGroup()
    {
    	if ($this->_group == null)
    		$this->_group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($this->_groupCode);
    	return $this->_group;
    }
    
    public function getAjaxUrl()
    {
    	return $this->getUrl('BackgroundTask/Admin/AjaxExecuteTaskGroup', array('group_code' => $this->_groupCode));
    }
    
    public function getFinishedUrl()
    {
    	return $this->getUrl('BackgroundTask/Admin/confirmTaskGroupExecution', array('group_code' => $this->_groupCode));
    }
    
    
}