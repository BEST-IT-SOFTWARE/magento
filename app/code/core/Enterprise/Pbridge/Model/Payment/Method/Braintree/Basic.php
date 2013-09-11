<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Braintree payment method model
 */
class Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic extends Mage_Payment_Model_Method_Cc
{
    /**
     * Payment method code
     * @var string
     */
    const METHOD_CODE = 'braintree_basic';

    protected $_code  = self::METHOD_CODE;
    protected $_allowCurrencyCode = array('USD');

    /**
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;

    /**
     * Form block type for the frontend
     *
     * @var string
     */
    protected $_formBlockType = 'enterprise_pbridge/checkout_payment_braintree_basic';

    /**
     * Form block type for the backend
     *
     * @var string
     */
    protected $_backendFormBlockType = 'enterprise_pbridge/adminhtml_sales_order_create_braintree_basic';

    /**
     * Payment Bridge Payment Method Instance
     *
     * @var Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    protected $_pbridgeMethodInstance = null;
    /**
     * Return that current payment method is dummy
     * @return boolean
     */
    public function getIsDummy()
    {
        return true;
    }

    /**
     * Return Payment Bridge method instance
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Pbridge
     */
    public function getPbridgeMethodInstance()
    {
        if ($this->_pbridgeMethodInstance === null) {
            $this->_pbridgeMethodInstance = Mage::helper('payment')->getMethodInstance('pbridge');
            if ($this->_pbridgeMethodInstance) {
                $this->_pbridgeMethodInstance->setOriginalMethodInstance($this);
            }
        }
        return $this->_pbridgeMethodInstance;
    }

    /**
     * Retrieve original payment method code
     *
     * @return string
     */
    public function getOriginalCode()
    {
        return parent::getCode();
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        return Mage::helper('enterprise_pbridge')->isEnabled($quote ? $quote->getStoreId() : null)
            && Mage_Payment_Model_Method_Abstract::isAvailable($quote);
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/'.$this->getOriginalCode().'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Assign data to info model instance
     *
     * @param  mixed $data
     * @return Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $this->getPbridgeMethodInstance()->assignData($data);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function validate()
    {
        $this->getPbridgeMethodInstance()->validate();
        return $this;
    }

    /**
     * PSi Gate method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->authorize($payment, $amount);
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Capturing method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->capture($payment, $amount);
        if (!$response) {
            $response = $this->getPbridgeMethodInstance()->authorize($payment, $amount);
        }
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Refunding method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $response = $this->getPbridgeMethodInstance()->refund($payment, $amount);
        $payment->addData((array)$response);
        $payment->setShouldCloseParentTransaction(false);
        return $this;
    }

    /**
     * Voiding method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function void(Varien_Object $payment)
    {
        $response = $this->getPbridgeMethodInstance()->void($payment);
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Cancel method being executed via Payment Bridge
     *
     * @param Varien_Object $payment
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function cancel(Varien_Object $payment)
    {
        $response = $this->getPbridgeMethodInstance()->void($payment);
        $payment->addData((array)$response);
        return $this;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
         return $this->_canRefund;
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        return Mage::app()->getStore()->isAdmin() ?
            $this->_backendFormBlockType :
            $this->_formBlockType;
    }
    /**
     * Store id setter, also set storeId to helper
     * @param int $store
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        Mage::helper('enterprise_pbridge')->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }
    /**
     * Set capture transaction ID to invoice for informational purposes
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Enterprise_Pbridge_Model_Payment_Method_Braintree_Basic
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }
}
