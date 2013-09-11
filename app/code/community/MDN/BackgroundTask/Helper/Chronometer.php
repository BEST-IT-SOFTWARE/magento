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
class MDN_BackgroundTask_Helper_Chronometer extends Mage_Core_Helper_Abstract
{
    private $timestart; 
    private $digits; 

    public function bwruntime($digits = "") 
    { 
        $this->timestart    = explode (' ', microtime()); 
        $this->digits       = $digits; 
    } 

    public function totaltime() 
    { 
        $timefinish         = explode (' ', microtime()); 
        if($this->digits == ""){ 
            $runtime_float  = $timefinish[0] - $this->timestart[0]; 
        }else{ 
            $runtime_float  = round(($timefinish[0] - $this->timestart[0]), $this->digits); 
        } 
        $runtime = ($timefinish[1] - $this->timestart[1]) + $runtime_float; 
        return $runtime; 
    } 
} 
?>