<?php

require_once dirname(__FILE__) . '/../app/Mage.php';
Mage::app();


mage::helper('BackgroundTask')->ExecuteTasks(explode(",",$argv[1]));


?>
