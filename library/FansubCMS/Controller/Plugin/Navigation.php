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

class FansubCMS_Controller_Plugin_Navigation extends Zend_Controller_Plugin_Abstract
{
    public $layout;

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->layout = Zend_Layout::getMvcInstance();
        $ch = FansubCMS_Cache_Helper::getInstance();
        # add a navigation cache
        if(!$ch->hasCacheTemplate('Navigation_Settings')) {
            $frontend = array(
                    'name' => 'Core',
                    'options' => array(
                        'lifetime' => 300,
                        'automatic_serialization' => true
                    )
                );
            # add a new cache template for this module
            $ch->setCacheTemplate('Navigation_Settings', $frontend);
        }
        $cache = $ch->getCache('Navigation_Settings');
        if(($request->getParam('module') == 'admin' || $request->getParam('controller') == 'admin') && Zend_Auth::getInstance()->hasIdentity()) {
            $config = $cache->load('Navigation_Backend_Settings');
            if(!$config) {
                // add the module and addon admin menus
                $modConf = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'configs'. DIRECTORY_SEPARATOR . 'module.ini');
                foreach($modConf as $nav) {
                    try {
                        $nav = new Zend_Config_Ini($nav,'adminnav',true);
                        if(isset($adminNav) && $adminNav instanceof Zend_Config_Ini)
                            $adminNav->merge($nav);
                        else
                            $adminNav = $nav;
                    } catch(Zend_Config_Exception $e) {
                        // do nothing on error, just ignore
                    }
                }
                $config = $adminNav->toArray();
                $cache->save($config);
            }
            $navigation = new Zend_Navigation($config);
        } else {
            $config = $cache->load('Navigation_Frontend_Settings');

            if(!$config) {
                $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/navigation.ini','nav');
                $config = $config->toArray();
                $cache->save($config);
            }

            if(Zend_Auth::getInstance()->hasIdentity()) {
                $adminPage = array(
                    'administrationLinkAdministration' => array(
                        'label' => 'administration',
                        'module' => 'admin',
                        'order' => 999999999,
                        'route' => 'default',
                    ));
                $config = array_merge($config, $adminPage);
            }
            $navigation = new Zend_Navigation($config);
        }
        $this->layout->getView()->navigation($navigation);
    }
}
