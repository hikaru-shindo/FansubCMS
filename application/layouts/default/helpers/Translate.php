<?php
/**
 * Translation view helper
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 */
class FansubCMS_View_Helper_Translate extends Zend_View_Helper_Translate
{
    /**
     * Translate a message
     * You can give an array of params.
     * If you want to output another locale just set it as last single parameter
     * Example 1: translate('Some text', $locale);
     * Example 2: translate('%key1% + %key2%', array('key1' => $value1, 'key2' => $value2), $locale);
     *
     * @param  string $messageid Id of the message to be translated
     * @param  array  $values Values for translation placeholders as assoc array
     * @return string|Zend_View_Helper_Translate Translated message
     */
    public function translate($messageid = null, $values = array())
    {
        if ($messageid === null) {
            return $this;
        }

        $translate = $this->getTranslator();
        $options   = func_get_args();

        array_shift($options);
        $count  = count($options);
        $locale = null;
        if ($count > 0) {
            if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
                $locale = array_pop($options);
            }
        }

        if ($translate !== null) {
            $messageid = $translate->translate($messageid, $locale);
        }

        if (count($values) === 0) {
            return $messageid;
        }

        if(count($values)) {
            foreach($values as $key => $value) {
                $messageid = str_replace('%' . $key . '%', $value, $messageid);
            }
        }

        return $messageid;
    }
}
