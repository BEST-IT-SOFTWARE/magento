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
class BEST_BackgroundTask_Model_Task extends Mage_Core_Model_Abstract
{
	const MAX_FAILED_ATTEMPTS = 5;
	public function _construct()
	{
		parent::_construct();
		$this->_init('BackgroundTask/Task');
	}
	/**
	 * Execute task
	 *
	 */
	public function execute()
	{
		$error = false;
		$status = 'success';
		$chronometer = mage::helper('BackgroundTask/Chronometer');
		$chronometer->bwruntime();
		$this->logStart();

		try
		{
			set_time_limit(360);		//Collect helper
			$helper = mage::helper($this->getbt_helper());
			$params = $this->getParams();
			$result = $helper->{$this->getbt_method()}($params);
                        $this->aggregate($result);

                        if (mage::getStoreConfig('backgroundtask/general/immediate_mode') != 1) {
                            mage::helper("BackgroundTask")->getBeanstalkdRunTask()->delete($this->job);
                        }
			set_time_limit(0);
		}
		catch (Exception  $ex)
		{
			$error = $ex->getMessage();
			$error .= $ex->getTraceAsString();
			$status = 'error';

			//if error, notify developer
//			$developerEmail = mage::getStoreConfig('backgroundtask/general/developer_email');
//			if ($developerEmail)
//			{
//				$body = 'Error for task id #'.$this->getId()."\n";
//				$body .= 'Method : '.$this->getbt_helper().' / '.$this->getbt_method()."\n";
//				$body .= $ex->getMessage()."\n";
//				$body .= $ex->getMessage()."\n";
//				$body .= $ex->getTraceAsString()."\n";
//				mail($developerEmail, 'Backgroundtask error', $body);
//			}
	                $this->reschedule($error);
		}

		$duration = (int)($chronometer->totaltime() * 1000);

		//Save execution information
		$this->setbt_executed_at(date('Y-m-d H:i:s'))
			 ->setbt_result_description($error)
			 ->setbt_result($status)
			 ->setbt_duration($duration);
//			 ->save();
		$correctDbStatus = $this->resetDbConnection();
		$this->logResult();
                $this->releaseLock();
//                $this->clearStorageInfo();
		return $correctDbStatus;
	}
        function clearStorageInfo(){
            $this->storage()->del($this->getbt_params());
        }
        public function getParams(){
            $bt_params = $this->getbt_params();
            $uns = unserialize($bt_params);
            $ser_false = serialize(false);
            if ($uns || $bt_params==$ser_false){
                return $uns;
            } else {
                return unserialize($this->storage()->get($bt_params));
            }
        }
        function storage(){
            return Mage::helper("BackgroundTask")->storage();
        }
        function aggregate($result, $error=null){
            $aggregator = $this->getAggregator();
            if ($aggregator){
                $this->pushResult($result, $error, $aggregator);
                if ($this->isCompleted($aggregator)){
                    $this->runAggregator($aggregator);
                }
            }
        }
        function runAggregator($aggregator){
            if (Mage::app()->getCache()->getBackend()->lock("LOCK_".$aggregator["queue"])){
                $serialized_results = $this->storage()->lrange($aggregator["queue"], 0, -1);
                $results = array();
                $errors = array();
                foreach ($serialized_results as $sresult){
                    $complete_result=unserialize($sresult);
                    if ($complete_result["result"]!==null){
                        $results[] = $complete_result["result"];
                    } else {
                        $errors[] = $complete_result["error"];
                    }
                }
                $this->storage()->del($aggregator["queue"]."_length");
                $this->storage()->del($aggregator["queue"]);
                Mage::helper("BackgroundTask")->AddTask("Aggregator", $aggregator["helper"], $aggregator["method"], array($results, $errors), $this->getbt_group_code());
            }
        }
        function isCompleted($aggregator){
            $executed = $this->getTotalResults($aggregator);
            $expected = $this->getTotalResultsExpected($aggregator);
            return $executed >=$expected;
        }
        function pushResult($result, $error, $aggregator){
            $all_result=array("result"=>$result, "error"=>$error);
            return $this->storage()->lpush($aggregator["queue"], serialize($all_result));
        }
        function getTotalResults($aggregator){
            return $this->storage()->llen($aggregator["queue"]);
        }
        function getTotalResultsExpected($aggregator){
            return $this->storage()->get($aggregator["queue"]. "_length");
        }
	function resetDbConnection(){
		$conn = Mage::getSingleton('core/resource')->getConnection('core_write');
		if ($conn->isConnected() && $conn->getTransactionLevel()>0){
			//$conn->rollback();
			//$conn->closeConnection();
			return false;
		} else {
			return true;
		}
	}

    function logStart(){
        if($this->job){
            $log_file = str_replace("/","_",$this->getbt_helper()).'-tasks.log';
            Mage::log(
                    str_pad($this->getbt_duration(), 10, " ", STR_PAD_LEFT).
                    str_pad($this->job->getId(), 10).
                    str_pad("STARTING!", 10).
                    str_pad("0", 10, " ", STR_PAD_LEFT).
                    " ".str_pad($this->getbt_helper(), 20).
                    $this->getbt_method().
                    "\n".
                    $this->getbt_description().
                    ": ".$this->getbt_params()
                    , null, $log_file);
          }
    }

    function logResult(){

       if($this->job){
            $log_file = str_replace("/","_",$this->getbt_helper()).'-tasks.log';
            Mage::log(
                    str_pad($this->job->getId(), 10).
                    str_pad($this->getbt_result(), 10).
                    str_pad($this->getbt_duration(), 10, " ", STR_PAD_LEFT).
                    " ".str_pad($this->getbt_helper(), 20).
                    $this->getbt_method().
                    "\n".
                    $this->getbt_description().
                    ": ".$this->getbt_params().
                    "\n".
                    $this->getbt_result_description()
                    , null, $log_file);
	}

    }

    public function getAggregator(){
        return $this->getData("bt_task_key/aggregator");
    }
    public function getTaskKey(){
        return $this->getData("bt_task_key/key");
    }
    function aquireLock(){
        if ($this->getTaskKey()){
            return Mage::app()->getCache()->getBackend()->lock($this->getTaskKey());
        } else {
            return true;
        }
    }
    public function releaseLock(){
        if ($this->getTaskKey()){
            Mage::app()->getCache()->getBackend()->unlock($this->getTaskKey());
        }
    }
    function reschedule($error){
        if (mage::getStoreConfig('backgroundtask/general/immediate_mode') != 1) {
	    $failed_attempts = $this->storage()->incr("TASK_FAILED_".$this->job->getId());
	    $this->storage()->set("TASK_FAILED_MESSAGE_".$this->job->getId(), @$error);
	    if ($failed_attempts>=self::MAX_FAILED_ATTEMPTS){
               mage::helper("BackgroundTask")->getBeanstalkdRunTask()->delete($this->job);
               $this->storage()->del("TASK_FAILED_MESSAGE_".$this->job->getId());
	       $this->storage()->del("TASK_FAILED_".$this->job->getId());
               $this->aggregate(null, array($this->getbt_params(), $error));
	    }else{
               mage::helper("BackgroundTask")->getBeanstalkdRunTask()->release($this->job, Pheanstalk::DEFAULT_PRIORITY ,rand(1,5*$failed_attempts));
	    }
        } else {
               $this->aggregate(null, array($this->getbt_params(), $error));
        }
    }
}
