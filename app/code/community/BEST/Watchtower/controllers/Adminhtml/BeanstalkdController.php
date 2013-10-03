<?php
require_once Mage::getBaseDir("lib") . DS . "pheanstalk" . DS . "pheanstalk_init.php";

class BEST_Watchtower_Adminhtml_BeanstalkdController extends BEST_Watchtower_Controller_Abstract
{
    public function deleteAction()
    {
        $this->taskExec("delete");
    }

    public function kickAction()
    {
        $this->taskExec("kickJob");
    }

    public function deleteAllDelayedAction()
    {
        $tube = $this->getRequest()->getParam("tube");
        $ph = $this->getPheanstalkd();
        $complete = 0;
        $errors = 0;
        while ($job = $ph->peekDelayed($tube)) {
            try {
                $ph->delete($job);
                $complete++;
            } catch (Exception $e) {
                $errors++;
                $this->notice($e->getMessage());
                $stats = $ph->statsTube($tube);
                if ($errors > 100 || !isset($stats["current-jobs-delayed"]) || $stats["current-jobs-delayed"] <= 0) {
                    break;
                }
            }
        }
        $this->notice("Delete $complete delayed of $tube successful");
        Mage::app()->removeCache(BEST_Watchtower_Block_Beanstalkd::$CACHE_KEY . "_LARGE");
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/beanstalkd");
    }

    public function deleteAllBuriedAction()
    {
        $tube = $this->getRequest()->getParam("tube");
        $ph = $this->getPheanstalkd();
        try {
            while ($job = $ph->peekBuried($tube)) {
                $ph->delete($job);
            }
        } catch (Exception $e) {
            //$this->notice($e->getMessage());
        }
        $this->notice("Delete all buried of $tube successful");
        Mage::app()->removeCache(BEST_Watchtower_Block_Beanstalkd::$CACHE_KEY . "_LARGE");
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/beanstalkd");
    }

    public function kickAllAction()
    {
        $tube = $this->getRequest()->getParam("tube");
        $ph = $this->getPheanstalkd();
        try {
            $ph->useTube($tube)->kick(100);
            Mage::app()->removeCache(BEST_Watchtower_Block_Beanstalkd::$CACHE_KEY . "_LARGE");
            $this->notice("Kick of $tube successful");
        } catch (Exception $e) {
            $this->notice($e->getMessage());
        }
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/beanstalkd");
    }

    private function getPheanstalkd()
    {
        return new Pheanstalk(Mage::getConfig()->getNode("global/beanstalkd/server"));
    }

    private function taskExec($action)
    {
        $job_id = $this->getRequest()->getParam("id");

        $ph = $this->getPheanstalkd();
        $job = new Pheanstalk_Job($job_id, "");
        try {
            $ph->$action($job);
            Mage::app()->removeCache(BEST_Watchtower_Block_Beanstalkd::$CACHE_KEY . "_LARGE");
            $this->notice("$action of $job_id successful");
        } catch (Exception $e) {
            $this->notice($e->getMessage());
        }
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/beanstalkd");
    }
}

?>
