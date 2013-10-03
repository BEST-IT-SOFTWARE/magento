<?php

class BEST_Watchtower_Helper_Trautman extends Mage_Core_Helper_Abstract
{

    static $admins
        = array(
            "alexandru.halus@gmail.com"
        );

    const EMAIL_FROM = "no-reply@magento.com";
    const EMAIL_FROM_NAME = "magento!";

    function report($subject, $content, $to = null)
    {
        if ($to == null) {
            $to = self::$admins;
        }
        try {
            Mage::log("Magento says: $subject",null,'email-errors.log');
            Mage::log($content,null,'email-errors.log');
            return TRUE;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

}

?>
