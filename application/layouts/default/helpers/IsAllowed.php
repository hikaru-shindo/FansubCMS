<?php
class FansubCMS_View_Helper_IsAllowed extends Zend_View_Helper_Abstract
{
    /**
     * The acl object
     *
     * @var Zend_Acl
     */
    protected static $_acl;

    /**
     * Checks if user has the right to do privilege on resource
     *
     * @param Zend_Acl_Resource $resource
     * @param string $privilege
     * @return boolean
     */
    public function isAllowed($resource, $privilege) {
        if(empty(self::$_acl)) {
            self::$_acl = Zend_Registry::get('Zend_Acl');
        }

        if(!self::$_acl->has($resource)) {
            return true;
        }

        return self::$_acl->isAllowed('fansubcms_user_custom_role_logged_in_user', $resource, $privilege);
    }
}
