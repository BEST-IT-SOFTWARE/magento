<?php

require_once dirname(__FILE__) . '/../app/Mage.php';
Mage::app();

Mage::helper('watchtower')->call($argv);


?>
