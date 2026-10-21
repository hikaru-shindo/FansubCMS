<?php
class FansubCMS_View_Helper_PrefixTranslate extends Zend_View_Helper_Abstract
{
    /**
     * Translate a value with a prefix
     * @param string $value
     * @param string $prefix
     * @param boolean $isSuffix
     * @return string
     */
    public function prefixTranslate($value, $prefix = null, $isSuffix = false)
    {
        if($isSuffix) {
            $identifier = $value.$prefix;
        } else {
            $identifier = $prefix.$value;
        }
        return $this->view->translate($identifier);
    }
}
