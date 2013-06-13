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
 * System config data transfer key field backend model
 *
 * @category    Enterprise
 * @package     Enterprise_Pbridge
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Pbridge_Model_System_Config_Backend_Data_Transfer_Key extends Mage_Core_Model_Config_Data
{
    /**
     * Checks data transfer key length
     *
     * @return Enterprise_Pbridge_Model_System_Config_Backend_Data_Transfer_Key
     */
    protected function _beforeSave()
    {
        /**
         * Maximum allowed length is hardcoded because currently we use only CIPHER_RIJNDAEL_256
         * @see Enterprise_Pci_Model_Encryption::_getCrypt
         */
        if (strlen($this->getValue()) > 32) { // strlen() intentionally, to count bytes rather than characters
            Mage::throwException(Mage::helper('enterprise_pbridge')->__('Maximum data transfer key length is 32. Please correct your settings.'));
        }

        return $this;
    }
}
