<?php

require_once Mage::getBaseDir("lib") . DS . "pheanstalk" . DS . "pheanstalk_init.php";

class BEST_Watchtower_Model_Beanstalkd
{

    private $phean = null;

    private function getPh()
    {
        if (!$this->phean) {
            $this->phean = new Pheanstalk(Mage::getConfig()->getNode("global/beanstalkd/server_write"),
                Mage::getConfig()->getNode("global/beanstalkd/server_write_port") * 1);
        }
        return $this->phean;
    }

    public function __call($method, $params)
    {
        $ph = $this->getPh();
        return call_user_func_array(array($ph, $method), $params);
    }

}

?>
