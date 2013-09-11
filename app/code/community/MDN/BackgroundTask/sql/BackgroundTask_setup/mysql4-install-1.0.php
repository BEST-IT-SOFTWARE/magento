<?php
 
$installer = $this;
 
$installer->startSetup();
 
$installer->run("
 
CREATE TABLE `{$this->getTable('backgroundtask')}` (
  `bt_id` int(11) unsigned NOT NULL auto_increment,
  `bt_created_at` datetime NOT NULL,
  `bt_executed_at` datetime default NULL,
  `bt_description` varchar(255) NOT NULL default '',
  `bt_helper` varchar(255) NOT NULL default '',
  `bt_method` varchar(255) NOT NULL,
  `bt_params` text,
  `bt_result` varchar(50) default NULL,
  `bt_result_description` text,
  `bt_group_code` varchar(50) default NULL,
  PRIMARY KEY  (`bt_id`),
  KEY `bt_result` (`bt_result`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `{$this->getTable('backgroundtask_group')}` (
  `btg_id` int(11) unsigned NOT NULL auto_increment,
  `btg_code` varchar(50) NOT NULL,
  `btg_description` varchar(255) NOT NULL,
  `btg_redirect_url` TEXT NOT NULL,
  PRIMARY KEY  (`btg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('backgroundtask_group')}` ADD `btg_executed_tasks` INT NOT NULL DEFAULT '0',
ADD `btg_task_count` INT NOT NULL ;

    ");
 
$installer->endSetup();
