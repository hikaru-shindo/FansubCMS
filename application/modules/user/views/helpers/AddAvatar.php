<?php
/**
 * Will return the avatar and the email address.
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage View_Helper
 *
 */
class FansubCMS_View_Helper_AddAvatar extends Zend_View_Helper_Abstract
{
    /**
     *
     * @param string $email
     * @param boolean $append
     * @param integer $size
     * @param string $default
     * @return string
     */
    public function addAvatar($email, $append=true, $size=20,$default=null)
    {
        $avatar = '<img src="' . $this->view->gravatar($email, $size, $default) . '" alt="" />';
        if($append) {
            $return = $email . ' ' . $avatar;
        } else {
            $return = $avatar . ' ' . $email;
        }
        return $return;
    }
}
