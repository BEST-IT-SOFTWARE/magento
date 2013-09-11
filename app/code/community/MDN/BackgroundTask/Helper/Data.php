<?php

require_once Mage::getBaseDir("lib").DS."pheanstalk".DS. "pheanstalk_init.php";

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
class MDN_BackgroundTask_Helper_Data extends Mage_Core_Helper_Abstract {
    static $_beanstalkd_write=null;
    static $_beanstalkd_read=null;
    static $_storage=null;
    private $delayedTasks =null;
    const MAX_RUN_TASKS=5000;
    /**
     * Add multiple task
     */
    public function AddMultipleTask($ids, $description, $helper, $method, $groupCode) {
        //build single query
        $singleQuery = "
							insert into " . Mage::getConfig()->getTablePrefix() . "backgroundtask
							(
								bt_created_at,
								bt_description,
								bt_helper,
								bt_method,
								bt_params,
								bt_group_code
							)
							values
							(
								'" . date('Y-m-d H:i') . "',
								'" . $description . "',
								'" . $helper . "',
								'" . $method . "',
								'{value}',
								'" . $groupCode . "'
							);
						";


        //multiple query for all ids
        $allQueries = '';
        foreach ($ids as $id) {
            $value = serialize($id);
            $currentQuery = str_replace('{id}', $id, $singleQuery);
            $currentQuery = str_replace('{value}', $value, $currentQuery);
            $allQueries .= $currentQuery . "\n";
        }

        //run query
        mage::getResourceModel('cataloginventory/stock_item_collection')->getConnection()->query($allQueries);

        //update group task count
        if ($groupCode) {
            $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
            if ($group == null)
                throw new Exception('Task group ' . $groupCode . ' doesnt exist');

            $group->updateTaskCount();
        }

        return true;
    }
    public function getBeanstalkdWrite(){
        if (!self::$_beanstalkd_write){
//            '127.0.0.1'
            self::$_beanstalkd_write =  new Pheanstalk(
                    Mage::getConfig()->getNode("global/beanstalkd/server_write"),
                    Mage::getConfig()->getNode("global/beanstalkd/server_write_port")*1);
        }
        return self::$_beanstalkd_write;
    }
    public function getBeanstalkdRunTask(){
        if (!self::$_beanstalkd_read){
//            '127.0.0.1'
            self::$_beanstalkd_read =  new Pheanstalk(
                    Mage::getConfig()->getNode("global/beanstalkd/server_read"),
                    Mage::getConfig()->getNode("global/beanstalkd/server_read_port")*1);
        }
        return self::$_beanstalkd_read;
    }
    public function storage(){
        if (!self::$_storage){
//            '127.0.0.1'
            self::$_storage =  Mage::app()->getCache()->getBackend()->getRedis();
        }
        return self::$_storage;
    }
    /**
     * Add a task to execute
     *
     * @param unknown_type $task
     */
    public function getUniqueToken(){
        return "TASK_AGGREGATOR_QUEUE_".$this->storage()->incr("bt_unique_token");
    }
    public function getUniqueParam(){
        return "TASK_PARAM_".$this->storage()->incr("bt_unique_token");
    }
    public function AddAggregatedTasks($description, $helperMap, $methodMap, $helperReduce, $methodReduce, $params, $groupCode = null, $task_params = array()) {
        if (count($params)==0) return;
        $token = $this->getUniqueToken();
        $this->storage()->set($token."_length", count($params));
        $aggregator=array("helper"=>$helperReduce, "method"=>$methodReduce,"queue"=>$token);
        $task_params["aggregator"] = $aggregator;
        foreach($params as $param){
            $this->addTask(sprintf($description, $param), $helperMap, $methodMap, $param, $groupCode, $task_params);
        }
    }
    public function AddTask($description, $helper, $method, $params, $groupCode = null, $task_params = array()) {
        //if group is set, check  if group exists
        if ($groupCode != null) {
//            $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
//            if ($group == null)
//                throw new Exception('Task group ' . $groupCode . ' doesnt exist for task '.$description);
        }
        else {
            $groupCode = "default";
            //if task doesn't belong to group, check if is the same as last task
//            if ($skipIfAlreadyPlanned) {
//                if ($this->alreadyPlaned($helper, $method, $params))
//                    return true;
//            }
        }


        //define stack trace
        $stackTrace = '';
        if (mage::getStoreConfig('backgroundtask/general/store_stack_trace') == 1) {
            foreach (debug_backtrace () as $key => $value) {
                if (isset($value['file']) && isset($value['line']) && isset($value['function']))
                    $stackTrace .= $value['file'] . ' (' . $value['line'] . ') : ' . $value['function'] . "\n";
            }
        }
        $ser_params = serialize($params);
        if (strlen($ser_params)>1024){ //1kb of data
            $param_storage = $this->getUniqueParam();
            $this->storage()->set($param_storage,$ser_params);
            $bt_param = $param_storage;
        } else {
            $bt_param = $ser_params;
        }
        //insert task
        $task = mage::getModel('BackgroundTask/task')
                        ->setbt_created_at(date('Y-m-d H:i:s'))
                        ->setbt_description($description)
                        ->setbt_helper($helper)
                        ->setbt_method($method)
                        ->setbt_params($bt_param)
                        ->setbt_group_code($groupCode)
                        ->setbt_stacktrace($stackTrace);
        if ($task_params===true){
            $task_params=array("key"=>$description);
        }
        if (!is_array($task_params)){
            $task_params=array("key"=>$task_params);
        }
        $bs = $this->getBeanstalkdWrite();
        if (!isset($task_params["priority"])) $task_params["priority"]= $bs::DEFAULT_PRIORITY;
        ;
        if (!isset($task_params["delay"])) $task_params["delay"]=0;

        if ($task_params){
            $task->setbt_task_key($task_params);
        }
        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        if ($conn->isConnected() && $conn->getTransactionLevel()>0){
            if (!$this->delayedTasks){
                $cr = Mage::getResourceModel("core/resource");
                $cr->addCommitCallback(array($this,'queueDelayedTasks'));
            }
            $this->delayedTasks[$groupCode][]=$task;
        } else {
            $this->queueTask($groupCode, $task);
        }
        return $task;
    }

    public function queueDelayedTasks() {
        foreach($this->delayedTasks as $group =>$tasks){
            foreach($tasks as $task){
                $this->queueTask($group, $task);
            }
        }
        $this->delayedTasks = null;
    }
    public function queueTask($groupCode, $task) {
        if (mage::getStoreConfig('backgroundtask/general/immediate_mode') == 1) {
            $this->setCurrentTask($task);
            $task->execute();
        } else {
            $bs = $this->getBeanstalkdWrite();
            $task_params = $task->getbt_task_key();
            $bs->useTube($groupCode)->put(serialize($task->getData()), $task_params["priority"], $task_params["delay"]);
        }
    }
    public function getLogsLines() {
        return file(Mage::getBaseDir("log").DS."tasks.log");
    }

    public function isFirstLine($line) {
            $extract = substr($line, 26, 10);
            return $extract=="DEBUG (7):";
    }
    public function getCreateTaskLogItem($line) {
        $fields = array_values(array_filter(explode(" ",$line), function($s){return $s!=="";}));
        return array(
            "date"=>$fields[0],
            "id"=>$fields[3],
            "status"=>$fields[4],
            "duration"=>$fields[5],
            "method"=>$fields[6],
        );

    }
    public function getLogs() {
        $lines = $this->getLogsLines();
        $tasks = array();
        foreach($lines as $line){
            if ($this->isFirstLine($line)){
                $task = $this->getCreateTaskLogItem($line);
                if (isset($tasks[$task["id"]])){
                    $task["lines"] = $tasks[$task["id"]]["lines"];
                    $task["status_count"] = $tasks[$task["id"]]["status_count"];
                }
                $task["status_count"][$task["status"]]++;
            }
            $task["lines"][]=$line;
            $tasks[$task["id"]]=$task;
        }
        return $tasks;
    }

    /**
     * Add a new task group
     *
     * @param unknown_type $groupCode
     * @param unknown_type $description
     * @param unknown_type $redirectUrl
     */
    public function AddGroup($groupCode, $description, $redirectUrl) {
        //if group exists, exit
        $group = mage::getResourceModel('BackgroundTask/Taskgroup')->loadByGroupCode($groupCode);
        if (!$group) {
            $group = mage::getModel('BackgroundTask/Taskgroup')
                            ->setbtg_code($groupCode)
                            ->setbtg_description($description)
                            ->setbtg_redirect_url($redirectUrl)
                            ->save();
        }
        return $group;
    }

    /**
     * Execute a task group
     * redirect to controller
     *
     * @param unknown_type $groupName
     */
    public function ExecuteTaskGroup($groupCode) {
        $url = Mage::helper('adminhtml')->getUrl('BackgroundTask/Admin/executeTaskGroup', array('group_code' => $groupCode));
        Mage::app()->getResponse()->setRedirect($url);
    }

    /**
     * Execute tasks (main module method)
     *
     */
    public function ExecuteTasks($groupCode="default") {
	try {
      //  $debug = '<h1>Execute Tasks</h1>';
//        $startTime = time();
        $hasTask = $success = true;
//	$run_tasks=;
//
        $maxExecutionTime = mage::getStoreConfig('backgroundtask/general/max_execution_time')*4;
        $runtime_limit = time()+$maxExecutionTime;
        while ($hasTask&&$success&&$runtime_limit>time()) {
//        while (((time() - $startTime) < $maxExecutionTime) && ($hasTask)) {
            //collect next task to execute
            $task = $this->getNextTaskToExecute($groupCode);

            //execute task
            if ($task) {
//		$run_tasks++;
                $this->setCurrentTask($task);
             //   $debug .= '<br>Executing task #' . $task->getId() . ' (' . $task->getbt_description() . ')';
                $success = $task->execute();
      //          $debug .= ' ---> ' . $task->getbt_status();
                if ($task->getbt_status() == 'error') {
                    $this->notifyDevelopper('Task #' . $task->getId() . ' failed.');
                }
            } else {
                //no task to execute, quit loop
                $hasTask = false;
            }
        }
	} catch(Exception $e){
		Mage::log($e->__toString(), null, "tasks.log");
	}
     //   $debug .= '<br>End executing tasks';

        //delete tasks
        //$debug .= '<br>Delete tasks';
        //mage::getResourceModel('BackgroundTask/Task')->deleteTasks();

        //print debug information if enabled
        //if ($refuseDebug == false) {
        //    if (mage::getStoreConfig('backgroundtask/general/debug') == 1)
        //        echo $debug;
        //}
    }
    private function lock($item){
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');

        $result = $adapter->query("update backgroundtask set bt_result=\"running\" where bt_result is NULL and bt_id={$item->getId()}");
        return $result->rowCount()==1; //Will only return true if the task was actually updated
    }
    /**
     * Collect next task to execute
     *
     */
    public function getNextTaskToExecute($groupCode) {
        if (!is_array($groupCode)){
            $groupCode = array($groupCode);
        }
	shuffle($groupCode);
        foreach($groupCode as $gc){
            $this->getBeanstalkdRunTask()->watch($gc);
        }
        if (!in_array("default", $groupCode)){
            $this->getBeanstalkdRunTask()->ignore("default");
        }
        $job = $this->getBeanstalkdRunTask()->reserve(0);
	if (!$job){ //Nothing yet, disconnect from DB
		Mage::getSingleton('core/resource')->getConnection('core_write')->closeConnection();
		$met = mage::getStoreConfig('backgroundtask/general/max_execution_time');
	        $job = $this->getBeanstalkdRunTask()->reserve(rand($met, $met*3));
	}
        if ($job){
//            $this->getBeanstalkdRead()->delete($job);
            $task = Mage::getModel("BackgroundTask/Task");
            $task->setData(unserialize($job->getData()));
            $task->setJob($job);
            if ($task->aquireLock()){
                return $task;
            } else {
                $error = "";
		$task->reschedule($error);
                return $this->getNextTaskToExecute($groupCode);
            }
        } else {
            return false;
        }
    }

    /**
     * Notify developper by email
     *
     */
    public function notifyDevelopper($msg) {
        $email = mage::getStoreConfig('backgroundtask/general/debug');
        if ($email != '') {
            mail($email, 'Magento Background Task notification', $msg);
        }
    }

    /**
     * Check if the last task is the same
     *
     * @param unknown_type $helper
     * @param unknown_type $method
     * @param unknown_type $params
     */
    protected function alreadyPlaned($helper, $method, $params) {
        $params = serialize($params);
        $collection = mage::getModel('BackgroundTask/Task')
                        ->getCollection()
                        ->addFieldToFilter('bt_helper', $helper)
                        ->addFieldToFilter('bt_method', $method)
                        ->addFieldToFilter('bt_params', $params)
                        ->addFieldToFilter('bt_result', array('null' => 1));

        return ($collection->getSize() > 0);
    }

    /**
     * Force a task execution
     */
    public function forceTaskExecution($helper, $method, $params) {
        $params = serialize($params);

        $collection = mage::getModel('BackgroundTask/Task')
                        ->getCollection()
                        ->addFieldToFilter('bt_helper', $helper)
                        ->addFieldToFilter('bt_method', $method)
                        ->addFieldToFilter('bt_params', $params)
                        ->addFieldToFilter('bt_result', array('null' => 1));
        foreach ($collection as $item) {
            $item->execute();
        }
    }
    public function setCurrentTask($task){
        $this->current_task = $task;
    }
    public function getCurrentTask(){
        return $this->current_task;
    }

}

?>
