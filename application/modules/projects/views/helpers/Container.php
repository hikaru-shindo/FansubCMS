<?php
/**
 * This view helper is a table helper for ussage with the codec field in table action
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 *
 */

class FansubCMS_View_Helper_Container extends Zend_View_Helper_Abstract
{
    /**
     * Generate container string for table
     * @param Projects_Model_Episode $record
     * @param string $format Used by sprintf()
     * @return string
     */
    public function container(Projects_Model_EpisodeRelease $record, $format = '%s[%s,%s]') {
        return sprintf($format, $record->container, $record->vcodec, $record->acodec);
    }

}
