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
class MDN_BackgroundTask_Model_Constant extends Mage_Core_Model_Abstract
{
	private $_ProductManufacturerAttributeId = null;
	private $_ProductNameAttributeId = null;
	private $_OrderStatusAttributeId = null;
	private $_ProductStatusAttributeId = null;
	private $_ProductEntityId = null;
	private $_ProductOrderedQtyAttributeId = null;
	private $_ProductReservedQtyAttributeId = null;
	private $_OrderPaymentValidatedAttributeId = null;
	private $_TablePrefix = null;

	public function getTablePrefix()
	{
		if ($this->_TablePrefix == null)
		{
			$this->_TablePrefix = (string)Mage::getConfig()->getTablePrefix();
		}
		return $this->_TablePrefix;
	}

	public function getProductEntityId()
	{
		if ($this->_ProductEntityId == null)
		{
			$this->_ProductEntityId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
		}
		return $this->_ProductEntityId;
	}

	public function GetProductManufacturerAttributeId()
	{
		if ($this->_ProductManufacturerAttributeId == null)
		{
			$this->_ProductManufacturerAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'manufacturer')->getId();
		}
		return $this->_ProductManufacturerAttributeId;
	}

	public function GetProductNameAttributeId()
	{
		if ($this->_ProductNameAttributeId == null)
		{
			$this->_ProductNameAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name')->getId();
		}
		return $this->_ProductNameAttributeId;
	}

	public function GetOrderStatusAttributeId()
	{
		if ($this->_OrderStatusAttributeId == null)
		{
			$this->_OrderStatusAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('order', 'status')->getId();
		}
		return $this->_OrderStatusAttributeId;
	}

	public function GetProductStatusAttributeId()
	{
		if ($this->_ProductStatusAttributeId == null)
		{
			$this->_ProductStatusAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'status')->getId();
		}
		return $this->_ProductStatusAttributeId;
	}

	public function GetProductOrderedQtyAttributeId()
	{
		if ($this->_ProductOrderedQtyAttributeId == null)
		{
			$this->_ProductOrderedQtyAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'ordered_qty')->getId();
		}
		return $this->_ProductOrderedQtyAttributeId;
	}

	public function GetProductReservedQtyAttributeId()
	{
		if ($this->_ProductReservedQtyAttributeId == null)
		{
			$this->_ProductReservedQtyAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'reserved_qty')->getId();
		}
		return $this->_ProductReservedQtyAttributeId;
	}

	public function GetOrderPaymentValidatedAttributeId()
	{
		if ($this->_OrderPaymentValidatedAttributeId == null)
		{
			$this->_OrderPaymentValidatedAttributeId = mage::getModel('eav/entity_attribute')->loadByCode('order', 'payment_validated')->getId();
		}
		return $this->_OrderPaymentValidatedAttributeId;
	}

}
