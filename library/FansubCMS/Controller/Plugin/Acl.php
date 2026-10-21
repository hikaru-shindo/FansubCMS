<?php
/*
 *  This file is part of FansubCMS.
 *
 *  FansubCMS is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  FansubCMS is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with FansubCMS.  If not, see <http://www.gnu.org/licenses/>
 */

class FansubCMS_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    /**
     *
     * @var Zend_Auth
     */
    protected $_auth = null;
    /**
     *
     * @var Zend_Acl
     */
    protected $_acl;
    /**
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    public function __construct(Zend_Auth $auth)
    {
        $this->_auth = $auth;
        $this->_session = Zend_Registry::get('applicationSessionNamespace');
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_initAcl();

        if($this->_auth->hasIdentity()) {
            $ident = $this->_auth->getIdentity();
            $date = new Zend_Date();
            $ident->last_login = $date->get(DATABASE_DATE_FORMAT);
            $ident->save();
        }
        if($request->getControllerName() != 'admin' && $request->getModuleName() != 'admin') return; // if this is not admin skip the rest
        if(!$this->_auth->hasIdentity() && !($request->getControllerName() == 'auth' && $request->getActionName() == 'login' && $request->getModuleName() == 'admin')) {
            $redirect = new Zend_Controller_Action_Helper_Redirector();
            $redirect->gotoSimple('login','auth','admin');
        }
        if($request->getModuleName() == 'user' && $request->getControllerName() == 'admin' && $request->getActionName() == 'profile') return; // the profile is a free resource
        $resource = $request->getModuleName().'_'.$request->getControllerName();

        $hasResource = $this->_acl->has($resource);
        if($hasResource && !$this->_acl->isAllowed('fansubcms_user_custom_role_logged_in_user',$resource, $request->getActionName())) {
            throw new FansubCMS_Exception_Denied('The user is not allowd to do this');
        }
    }

    /**
     * init acl
     * @return void
     */
    protected function _initAcl()
    {
        $ch = FansubCMS_Cache_Helper::getInstance();
        # add a navigation cache
        if(!$ch->hasCacheTemplate('Acl_Settings')) {
            $frontend = array(
                    'name' => 'Core',
                    'options' => array(
                        'lifetime' => 300,
                        'automatic_serialization' => true
                    )
                );
            # add a new cache template for this module
            $ch->setCacheTemplate('Acl_Settings', $frontend);
        }
        $cache = $ch->getCache('Acl_Settings');

        $config = $cache->load('Acl');
        if (!$config) {
            $config = array();
            $modules = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini');
            foreach ($modules as $module) {
                $cleanName = str_replace(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR, '', $module);
                $cleanName = str_replace(DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini', '', $cleanName);

                try {
                    $ini = new Zend_Config_Ini($module, 'acl');
                    $config[$cleanName] = $ini->toArray();
                } catch (Zend_Config_Exception $e) {
                    // there is just no config or no acl block
                }
            }
            $cache->save($config);
        }

        $acl = new FansubCMS_Acl();
        foreach ($config as $options) {
            $acl->setOptions($options);
        }
        if ($this->_auth->hasIdentity()) {
            $ident = $this->_auth->getIdentity();

            $role = new Zend_Acl_Role('fansubcms_user_custom_role_logged_in_user');
            $inherit = $ident->getRoles();
            $inherit[] = 'fansubcms_custom_role_default'; // every user is in this role

            foreach ($inherit as $key => $value) {
                if (!$acl->hasRole($value)) {
                    unset($inherit[$key]);
                }
            }

            $acl->addRole($role, $inherit);
        }
        Zend_Registry::set('Zend_Acl', $acl);
        $this->_acl = $acl;
    }
}
