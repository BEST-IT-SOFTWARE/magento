<?php

class BEST_Watchtower_Block_Abstract extends Mage_Core_Block_Text
{
    const CACHE_LIFETIME = 3;

    protected function _construct()
    {
        parent::_construct();
        $this->_setCacheKeys();
    }

    public function getCacheKeyTimer()
    {
        return $this::$CACHE_KEY . get_class($this) . "_TIMER";
    }

    public function getUrl($route = '', $params = array())
    {
        return Mage::helper('adminhtml')->getUrl($route, $params);
    }

    protected function _setCacheKeys()
    {
        if (isset($this::$CACHE_KEY)) {
            if ($this->hasSmallMode()) {
                $this->addData(
                    array(
                         "cache_lifetime" => self::CACHE_LIFETIME * 10,
                         "cache_key"      => $this::$CACHE_KEY . "_SMALL"
                    )
                );
            } else {
                $this->addData(
                    array(
                         "cache_lifetime" => self::CACHE_LIFETIME * 10,
                         "cache_key"      => $this::$CACHE_KEY . "_LARGE"
                    )
                );
            }
            $timer = Mage::app()->loadCache($this->getCacheKeyTimer());
            if (!$timer) {
                Mage::app()->removeCache($this->getCacheKey());
            }
        }
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if (isset($this::$CACHE_KEY)) {
            Mage::app()->saveCache(true, $this->getCacheKeyTimer(), array($this::$CACHE_KEY), self::CACHE_LIFETIME);
        }
        try {
            if (!$this->info) {
                $this->info = $this->loadInfo();
            }
            if ($this->hasSmallMode()) {
                $text = $this->showSmallMode($this->info);
            } else {
                $text = $this->largeMode($this->info);
            }
            $this->setText($text);
            return true;
        } catch (Exception $e) {
            $this->setText($e->getMessage());
            return true;
        }
    }

    protected function showSmallMode($info)
    {
        $title = $this->getTitle();
        $url = $this->getWtUrl();

        $return = $this->renderLink("<div class='title'>$title</div>", $url);
        $return .= "<div class='timestamp'>" . date("H:i:s") . "</div>";
        $return = "<div class='header'>$return</div>";
        return $return . $this->smallMode($info);
    }

    protected function smallMode($info)
    {
        return $this->largeMode($info);
    }

    protected function largeMode($info)
    {
        return print_r($info, true);
    }

    public function setSmallMode($bool)
    {
        $this->setData("small_mode", $bool);
        $this->_setCacheKeys();
    }

    protected function renderLink($title, $url, $tooltip = "")
    {
        return "<a href='{$this->getUrl($url)}' title='$tooltip'> $title </a>";
    }

    protected function renderAjaxLink($title, $url)
    {
        return "<a href='#' onclick='new Ajax.Request(\"{$this->getUrl(
            $url
        )}?isAjax=true\");return false;'> $title </a>";
    }

}

?>
