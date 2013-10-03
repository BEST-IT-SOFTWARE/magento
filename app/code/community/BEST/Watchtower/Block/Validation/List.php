<?php

class BEST_Watchtower_Block_Validation_List extends BEST_Watchtower_Block_Abstract
{

    protected function loadInfo()
    {
        return $this->helper("watchtower")->getGroupedValidations();
    }

    protected function smallMode($info)
    {
        $return = "";
//        $return .= $this->renderAjaxLink("Re-check All!", "watchtower/adminhtml_validation/clearAll");
        foreach ($info as $group => $vs) {
            $return .= "<ul><h5>$group</h5>";
            foreach ($vs as $code => $v) {
                $return .= "<li>" . $this->renderValidationSmall($code, $v) . "</li>";
            }
            $return .= "</ul>";
        }
        return $return;
    }

    protected function largeMode($info)
    {
        $return = "";
//        $return .= $this->renderAjaxLink("Re-check All!", "watchtower/adminhtml_validation/clearAll");
        foreach ($info as $code => $v) {
            $return .= $this->renderValidationLarge($code, $v);
        }
        return $return;
    }

    protected function renderValidationSmall($code, $val)
    {
        $v = Mage::getModel($val);
        $results = $v->getResultsSummary();
        if ($results) {
            $text = "<b><em>" . $results . "</em></b>";
            return $this->renderValidation($v, $code, $results, $text);
        } else {
            return "";
        }

    }

    protected function renderValidationLarge($code, $val)
    {
        $v = Mage::getModel($val);
        $text = $v->_tooltip . "<br/>" . $v->getResultsText();
        return $this->renderValidation($v, $code, true, $text);
    }

    protected function renderValidation($v, $code, $has_results, $results_text)
    {
        $return = "";
//        $return .= "<br/>";
        $return .= $this->renderLink($code, "watchtower/adminhtml_validation/show/v/$code", $v->_tooltip);
        $return .= $results_text;
//	if ($v->duration){
//		$d = $v->duration;
//		$return .= "(took $d seconds)";
//	}
        if ($has_results && $v->hasFix()) {
            if ($v->canFix()) {
                $return .= " " . $this->renderAjaxLink("Fix!", "watchtower/adminhtml_validation/fix/v/$code");
            } else {
                $return .= " <i>Fixing...</i>";
            }
        }
//        $return .= " ".$this->renderAjaxLink("Re-check!", "watchtower/adminhtml_validation/clear/v/$code");
        return $return;
    }


}

?>
