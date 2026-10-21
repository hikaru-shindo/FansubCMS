<?php
/**
 * helper for use of gravatar.com
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 * @version SVN: $Id
 *
 */
class FansubCMS_View_Helper_Gravatar extends Zend_View_Helper_Abstract {

    /**
     * returns gravatar url for given values
     * @param string $email
     * @param integer $size
     * @param string $default
     * @return string
     */
    public function gravatar($email,$size=80,$default=null) {
        $avatar_url = 'http://www.gravatar.com/avatar.php?gravatar_id='.md5(strtolower($email)).'&amp;size='.$size;
        if($default !== null) $avatar_url .= '&amp;default='.urlencode($default);
        return $avatar_url;
    }
}
