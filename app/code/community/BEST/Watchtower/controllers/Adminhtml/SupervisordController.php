<?php

class BEST_Watchtower_Adminhtml_SupervisordController extends BEST_Watchtower_Controller_Abstract
{
    public function startAction()
    {
        $this->execTask("startProcess");
    }

    public function stopAction()
    {
        $this->execTask("stopProcess");
    }

    public function startGroupAction()
    {
        $this->execTask("startProcessGroup");
    }

    public function stopGroupAction()
    {
        $this->execTask("stopProcessGroup");
    }

    private function execTask($cmd)
    {
        $task = $this->getRequest()->getParam("task");
        $super = new Supervisor_Supervisord();
        try {
            $super->$cmd($task);
            $this->notice("$cmd of $task successful");
        } catch (Exception $e) {
            $this->notice($e->getMessage());
        }
        $this->_redirect("watchtower/adminhtml_dashboard/show/s/supervisord");
    }
}

?>