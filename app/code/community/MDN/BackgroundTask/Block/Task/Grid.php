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
class MDN_BackgroundTask_Block_Task_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('BackgroundTaskGrid');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Shipping/List.phtml');	
        $this->setEmptyText($this->__('No items'));
        $this->setDefaultSort('bt_id', 'desc');
    }

    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        //charge
        $collection = Mage::getModel('BackgroundTask/Task')
                        ->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('bt_id', array(
            'header' => Mage::helper('BackgroundTask')->__('Id'),
            'index' => 'bt_id'
        ));

        $this->addColumn('bt_created_at', array(
            'header' => Mage::helper('BackgroundTask')->__('Created At'),
            'index' => 'bt_created_at',
            'type' => 'datetime'
        ));

        $this->addColumn('bt_group_code', array(
            'header' => Mage::helper('BackgroundTask')->__('Group'),
            'index' => 'bt_group_code'
        ));


        $this->addColumn('bt_description', array(
            'header' => Mage::helper('BackgroundTask')->__('Description'),
            'index' => 'bt_description'
        ));

        $this->addColumn('bt_executed_at', array(
            'header' => Mage::helper('BackgroundTask')->__('Executed At'),
            'index' => 'bt_executed_at',
            'type' => 'datetime'
        ));

        $this->addColumn('bt_result', array(
            'header' => Mage::helper('BackgroundTask')->__('Result'),
            'index' => 'bt_result'
        ));

        $this->addColumn('bt_result_description', array(
            'header' => Mage::helper('BackgroundTask')->__('Result description'),
            'index' => 'bt_result_description'
        ));

        $this->addColumn('bt_duration', array(
            'header' => Mage::helper('BackgroundTask')->__('Duration'),
            'index' => 'bt_duration'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('BackgroundTask/Admin/View', array('bt_id' => $row->getId()));
    }

    public function getGridUrl() {
        return ''; //$this->getUrl('*/*/wishlist', array('_current'=>true));
    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative' => true));
        return $this->fetchView($templateName);
    }

    public function getClearAllTasksUrl() {
        return $this->getUrl('BackgroundTask/Admin/ClearAllTasks');
    }

    public function getClearGroupTasksUrl() {
        return $this->getUrl('BackgroundTask/Admin/ClearGroupTasks');
    }

    public function getStatUrl() {
        return $this->getUrl('BackgroundTask/Admin/Stats');
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('bt_id');
        $this->getMassactionBlock()->setFormFieldName('bt_ids');

        $this->getMassactionBlock()->addItem('replay', array(
            'label' => Mage::helper('sales')->__('Replay'),
            'url' => $this->getUrl('*/*/MassReplay')
        ));

        return $this;
    }

}
