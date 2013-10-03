<?php

class BEST_Watchtower_Controller_Abstract extends Mage_Adminhtml_Controller_Action
{
    protected function notice($message)
    {
        Mage::getSingleton('adminhtml/session')->addNotice(
            $this->__($message)
        );
    }

    protected function renderTitle($page)
    {
        $l = $this->getLayout();
        $text = $l->createBlock("core/text", "header");

        $text->setText(
            "<h1><a target='_blank' href='http://en.wikipedia.org/wiki/Panopticon'>Panopticon</a></h1><h2>$page</h2>"
        );
        $l->getBlock("content")->append($text);
    }

    protected function renderLink($title, $url, $code = null, $tooltip = "")
    {
        $this->renderText("<a href='{$this->getUrl($url)}' title='$tooltip'> $title </a>", $code);
    }

    protected function renderAjaxLink($title, $url, $code = null)
    {
        $this->renderText(
            "<a href='#' onclick='new Ajax.Request(\"{$this->getUrl($url)}?isAjax=true\")'> $title </a>", $code
        );
    }

    protected function renderBr()
    {
        $this->renderText("<br/>");
    }

    protected function addRootBlock($path)
    {
        $l = $this->getLayout();
        $bl = $l->createBlock($path, "root");
        $cont = $l->createBlock("core/text_list", "content");
        $bl->append($cont);
        return $bl;

    }

    protected function renderBoxStart()
    {
        return $this->renderText("<div class='ajax_wrap'>");
    }

    protected function renderBoxEnd()
    {
        return $this->renderText("</div>");
    }

    protected function renderText($text, $code = null)
    {
        $l = $this->getLayout();
        $bl = $l->createBlock("core/text", $code);
        $l->getBlock("content")->append($bl);
        $bl->setText($text);
        return $bl;
    }

    protected function addBlock($path, $code)
    {
        $l = $this->getLayout();
        $bl = $l->createBlock($path, $code);
        $l->getBlock("content")->append($bl);
        return $bl;
    }

    protected function addAjaxBlock($path, $code)
    {
        $url = $this->getUrl($path);
        $bl = $this->renderText(
            "<div id='$code'>Loading...</div>
                <script>new Ajax.PeriodicalUpdater('$code', '$url?isAjax=true',
                    { frequency: 3, method: 'get', loaderArea:false });</script>", $code
        );
        return $bl;
    }

}

?>
