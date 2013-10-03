<?php

class BEST_Watchtower_Adminhtml_DashboardController extends BEST_Watchtower_Controller_Abstract
{

    static $_blocks
        = array(
            "supervisor"  => array("name"   => "Supervisor", "code" => "watchtower/supervisord",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/supervisord"),
            "beanstalkd"  => array("name"   => "Beanstalkd", "code" => "watchtower/beanstalkd",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/beanstalkd"),
            "indexing"    => array("name"   => "Indexing", "code" => "watchtower/indexing",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/indexing"),
            "redis"       => array("name"   => "Redis", "code" => "watchtower/redis",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/redis"),
            "dbstatus"    => array("name"   => "DB Status", "code" => "watchtower/dbstatus",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/dbstatus"),
            "helpdesk"    => array("name"   => "Helpdesk Mail Server", "code" => "watchtower/helpdesk",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/helpdesk"),
            "scalr"       => array("name"   => "Scalr", "code" => "watchtower/scalr",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/scalr"),
            "analytics"   => array("name"   => "Analytics", "code" => "watchtower/analytics",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/analytics"),
            "validations" => array("name"   => "Validations", "code" => "watchtower/validation_list",
                                   "action" => "watchtower/adminhtml_validation/list"),
            "reports"     => array("name"   => "Reports", "code" => "watchtower/reports",
                                   "action" => "watchtower/adminhtml_dashboard/show/s/reports")
        );

    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('watchtower');
        return $this;
    }

    private function renderBlocks()
    {
        $blocks = Mage::getConfig()->getNode("global/watchtower/blocks");
        foreach ($blocks->children() as $block) {
            $code = $block->getName();
            $this->renderSmall(
                self::$_blocks[$code]["name"], self::$_blocks[$code]["code"], self::$_blocks[$code]["action"]
            );
        }
        $this->renderText(
            "<style>
                        .ajax_wrap{min-width:250px; border:1px inset black; float: left;background-color: #FFF9E9;padding: 3px;margin: 3px;overflow:hidden}
                        .timestamp{float:right}
                        .title{float:left}
                        .ajax_wrap .header{background:none}
                        </style>", "styles"
        );
    }

    private function renderAjax()
    {
        $this->addAjaxBlock("watchtower/adminhtml_dashboard/ajax", "contents");
    }

    private function renderValidations()
    {
        $this->renderSmall();
    }

    public function ajaxAction()
    {
        $this->_initAction();
        $this->addRootBlock("core/text_list");
        $this->renderBlocks();
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderTitle("");
//
        $this->renderAjax();
        $this->renderLayout();
    }

    public function renderSmall($title, $block, $url)
    {
        $this->renderBoxStart();
        $small = $this->addBlock($block, $block . "_small");
        $small->setSmallMode(true);
        $small->setTitle($title);
        $small->setWtUrl($url);
        $this->renderBoxEnd();
    }

    public function showAction()
    {
        $this->_initAction();
        $code = $this->getRequest()->getParam("s");
        $ajax = $this->getRequest()->getParam("isAjax");
        $this->renderTitle($code);
        if ($ajax) {
            $block = $this->addRootBlock("watchtower/$code");
        } else {
            $block = $this->addBlock("watchtower/$code", $code);
        }
        $small = $this->getRequest()->getParam("small", false);
        if ($small) {
            $block->setSmallMode(true);
        }
        $this->renderLayout();
    }
}

?>
