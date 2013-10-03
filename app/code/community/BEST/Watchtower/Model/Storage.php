<?php

class BEST_Watchtower_Model_Storage
{

    private function getRedis()
    {
        return Mage::app()->getCache()->getBackend();
    }

    public function __call($method, $params)
    {
        $ph = $this->getRedis();
        return call_user_func_array(array($ph, $method), $params);
    }

}

?>
