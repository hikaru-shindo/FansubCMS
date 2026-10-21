<?php
/**
 * formats texts
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 * @version SVN: $Id
 *
 */
class FansubCMS_View_Helper_FormatText extends Zend_View_Helper_Abstract {
    /**
     * formats texts
     * @param string $text
     * @return string
     */
    public function formatText($text) {
        $text = str_replace("\xC2\xA0",' ',$text); // replace nbsp with regular spaces
        $text = trim($text,"\n"); // remove useless letters on start and end
        //$this->_wrapText($text); // deactivated because it breaks some things
        $text = $this->view->escape($text);
        $text = nl2br($text); // Add line breaks
        // Link urls
        $pattern = "!\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))!";
   	    $text = preg_replace($pattern, '<a href="\1" target="_blank">\1</a>', $text); // add links to urls

   	    return $text;
    }

    private function _wrapText(&$text) {
        $lines = explode("\n",$text);
        $text = '';
        foreach($lines as $k => $line) {
            $words = explode(" ",$line);
            foreach($words as $j => $word) {
                $words[$j] = wordwrap($word, 35, " ", true);
            }
            $line = implode(" ",$words);
            $lines[$k] = $line;
        }
        $text = implode("\n",$lines);
    }
}
