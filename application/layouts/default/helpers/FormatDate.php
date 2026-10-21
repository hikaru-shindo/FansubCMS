<?php
/**
 * formats dates
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 * @version SVN: $Id
 *
 */
class FansubCMS_View_Helper_FormatDate {
    /**
     * existing date formats
     * @var array
     */
    private $date_formats = array(
            "date" => Zend_Date::DATE_FULL,
            "datetime" => Zend_Date::DATETIME_FULL,
            "shortdate" => Zend_Date::DATE_SHORT,
            "mediumdate" => Zend_Date::DATE_MEDIUM,
            "longdate" => Zend_Date::DATE_LONG);

    /**
     * formats the given date according to $formatName or $formatString
     *
     * Existing format names:
     *  * date - complete date (localized)
     *  * datetime - complete date with time (localized)
     *
     * The date's format has to be in a format, that Zend_Date can handle.
     *
     * @see Zend_Date
     * @param string $date
     * @param string $formatName
     * @param string $formatStr
     * @return string
     */
    public function formatDate($date, $formatName = null, $formatStr = null) {
                $date = empty($date) ? Zend_Date::now() : new Zend_Date(strtotime($date));
        if(($formatName !== null) && (array_key_exists($formatName,$this->date_formats))) {
            return $date->toString($this->date_formats[$formatName]);
        } if(!empty($formatStr)) {
                    return $date->toString($formatStr);
                } else {
            if(($formatName===null && $formatStr === null) || !array_key_exists($formatName,$this->date_formats)) $formatStr = Zend_Date::DATE_FULL.' '.Zend_Date::TIMES;
            return $date->toString($formatStr);
        }
    }
}
