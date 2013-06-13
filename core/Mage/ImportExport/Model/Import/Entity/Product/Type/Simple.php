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
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Import entity simple product type
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Import_Entity_Product_Type_Simple
    extends Mage_ImportExport_Model_Import_Entity_Product_Type_Abstract
{
    const ERROR_INVALID_PRISM_SKU            = 'invalidPrismSku';
    /**
     * Attributes' codes which will be allowed anyway, independently from its visibility property.
     *
     * @var array
     */
    protected $_forcedAttributesCodes = array(
        'related_tgtr_position_behavior', 'related_tgtr_position_limit',
        'upsell_tgtr_position_behavior', 'upsell_tgtr_position_limit'
    );

        /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::ERROR_INVALID_PRISM_SKU            => 'Invalid value in Prism Sku column',
    );
    
    /**
     * Check product website belonging.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    protected function _isPrismSkuValid(array $rowData, $rowNum)
    {
        if (!empty($rowData['prism_sku']) && !preg_match("/^[A-Z0-9]{14}$/",$rowData['prism_sku'])) {
            $this->_entityModel->addRowError(self::ERROR_INVALID_PRISM_SKU, $rowNum);
            return false;
        }
        return true;
    }    
    protected function _isParticularAttributesValid(array $rowData, $rowNum) {
        return parent::_isParticularAttributesValid($rowData, $rowNum)
                &&  $this->_isPrismSkuValid($rowData, $rowNum);
    }    
}
