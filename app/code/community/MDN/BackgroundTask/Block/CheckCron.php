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
class MDN_BackgroundTask_Block_CheckCron extends Mage_Adminhtml_Block_Widget_Form {

    protected function _toHtml() {
        $html = '<div class="notification-global"> ';

        //get the latest cron execution date
        $sql = "select max(executed_at) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
        $lastExecutionTime = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
        ;

        //if return empty, check if there are records in table
        if ($lastExecutionTime == '') {
            $sql = "select count(*) from " . Mage::getConfig()->getTablePrefix() . "cron_schedule";
            $count = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
            ;
            if ($count == 0) {
                //if no records in cron_schedule table, check if there are background tasks
                $sql = "select count(*) from " . Mage::getConfig()->getTablePrefix() . "backgroundtask";
                $count = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
                ;
                if ($count == 0) {
                    $html .= '<font color=red><b>Caution !! It seems that cron is not working on your server, ERP requires cron to work properly.</b></font>';
                    ;
                    $html .= '</div>';
                    return $html;
                }
            }
        }
        $timeStamp = strtotime($lastExecutionTime);
        if ((time() - $timeStamp) > 60 * 5) {
            $html .= '<font color=red><b>Caution !! It seems that cron is not working on your server, ERP requires cron to work properly.</b></font>';
        }
        else
            return '';
        $html .= '</div>';
        return $html;
    }

}