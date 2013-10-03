<?php

class BEST_Watchtower_Adminhtml_ValidationController extends BEST_Watchtower_Controller_Abstract
{
    public function listAction()
    {
        $this->_initAction();
        $ajax = $this->getRequest()->getParam("isAjax");
        if ($ajax) {
            $this->addRootBlock("core/text_list");
        }
        foreach (Mage::helper("watchtower")->getGroupedValidations() as $group => $vals) {
            $this->renderText("<h3>$group</h3>", $group . "_group");
            foreach ($vals as $key => $val) {
                $this->renderValidation($key, true);
            }
        }
        $this->renderLayout();
    }

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    private function renderValidation($code, $small, $all)
    {

        $val = Mage::helper("watchtower")->getValidation($code);
        if ($small) {
            $results = $val->getResultsSummary();
            if ($results) {
                $resultsText = "<b><em>" . $results . "</em></b>";
            } else {
                $resultsText = ":)";
            }
            $this->renderLink($code, "watchtower/adminhtml_validation/show/v/$code", $code . "_title", $val->_tooltip);
        } else {
            $results = $resultsText = $val->getResultsText($all);
            $resultsText = $val->_tooltip . "<br/>" . $resultsText;
        }
        $this->renderText($resultsText, $code . "_results");
        if ($results) {
             if (!$all) {
               
                $this->renderLink(
                    " Show All", "watchtower/adminhtml_validation/show/v/$code/all/true", $code . "_show_all"
                );
                 $this->renderText('<br><br>', 'blankspace' . "_results");
            }
            if ($val->hasFix()) {
                $this->renderAjaxLink("Fix!", "watchtower/adminhtml_validation/fix/v/$code", $code . "_fix");
            }
           
        }
        $this->renderBr();
    }

    public function showAction()
    {
        $this->_initAction();
        $code = $this->getRequest()->getParam("v");
        $this->renderTitle($code);
        $small = $this->getRequest()->getParam("small", false);
        $all = $this->getRequest()->getParam("all", false);
        $this->renderValidation($code, $small, $all);
        $this->renderLayout();
    }

    public function clearAllAction()
    {
        foreach (array_keys(Mage::helper("watchtower")->getValidations()) as $code) {
            Mage::helper("watchtower")->getValidation($code)->clearCache();
        }
    }

    public function clearAction()
    {
        $code = $this->getRequest()->getParam("v");
        $isAjax = $this->getRequest()->getParam("isAjax");
        $val = Mage::helper("watchtower")->getValidation($code);
        $val->clearCache();
        $this->notice("The cache was cleared for $code");
        if (!$isAjax) {
            $this->_redirect("watchtower/adminhtml_validation/show/v/$code");
        } else {
            echo "OK!";
        }
    }

    public function fixAction()
    {
        $code = $this->getRequest()->getParam("v");
        $isAjax = $this->getRequest()->getParam("isAjax");
        Mage::helper("watchtower")->queueFix($code);
        sleep(1);
        if (!$isAjax) {
            $this->_redirect("watchtower/adminhtml_validation/show/v/$code");
        } else {
            echo "OK!";
        }
    }
}

?>
