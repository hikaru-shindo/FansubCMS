<?php
class FansubCMS_View_Helper_ShortenText extends Zend_View_Helper_Abstract {
    public function shortenText($text,$length = 100) {
        if(strlen($text) > $length)
            $text = substr($text,0,$length).'...';
        return $text;
    }
}
