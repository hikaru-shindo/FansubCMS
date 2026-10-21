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
class FansubCMS_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public $module;
    public $autoloader;
    /**
     * The front controller
     * @var Zend_Controller_Front
     */
    public $frontController;
    public $settings;
    public $moduleSettings;
    public $envSettings;
    public $path;

    protected function _initModule ()
    {
        $this->path = APPLICATION_PATH . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . strtolower($this->getModuleName());
        $this->settings = Zend_Registry::get('settings');

        $this->bootstrap("FrontController");
        $this->frontController = $this->getResource("FrontController");
        $this->envSettings = Zend_Registry::get('environmentSettings');
        $iniPath = $this->path . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini';

        if(file_exists($iniPath)) {
            $this->moduleSettings = new Zend_Config_Ini($iniPath);
        }
    }

    protected function _initModuleAutoload ()
    {
        $this->bootstrap('module');

        $options = array(
            'namespace' => ucfirst(strtolower($this->getModuleName())),
            'basePath' => $this->path
        );
        $this->autoloader = new Zend_Loader_Autoloader_Resource($options);
        # autoload the api and delegates
        $options = array(
            'api' => array(
                'path' => "api" . DIRECTORY_SEPARATOR,
                'namespace' => "Api"
            ),
            'baseDelegate' => array(
                'path'      => 'delegates' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR,
                'namespace' => 'Delegate_Default'
            ),
            'delegate' => array(
                'path'      => 'delegates' . DIRECTORY_SEPARATOR . $this->envSettings->page->layout . DIRECTORY_SEPARATOR,
                'namespace' => 'Delegate_' . ucfirst($this->envSettings->page->layout)
            ),
        );
        $this->autoloader->addResourceTypes($options);
    }

    protected function _initRoute ()
    {
        $this->bootstrap('module');
        if (!empty($this->moduleSettings->routes) && $this->moduleSettings->routes->router instanceof Zend_Config) {
            $router = $this->frontController->getRouter();
            $router->addConfig($this->moduleSettings->routes->router, 'routes');
        }
    }

    protected function _initI18n()
    {
        $ch = FansubCMS_Cache_Helper::getInstance();
        if(!$ch->hasCacheTemplate('I18n_Settings')) {
            # life time in development 30 seconds in other mode a half hour
            $frontend = array(
                    'name' => 'Core',
                    'options' => array(
                        'lifetime' => 1800,
                        'automatic_serialization' => true
                    )
                );
            # add a new cache template for this module
            $ch->setCacheTemplate('I18n_Settings', $frontend);
        }
        $cache = $ch->getCache('I18n_Settings');

        $trans = $cache->load(ucfirst($this->getModuleName()));
        // there are no translations or cache is invalid - generate cache!
        $locale = $this->envSettings->locale;
        if (!$trans) {
            $module = APPLICATION_PATH . '/modules/'. strtolower($this->getModuleName()) .'/locale/';
            if(file_exists($module . $locale . '.ini')) {
                $trans = new Zend_Config_Ini($module . $locale . '.ini');
            } else {
                $trans = new Zend_Config_Ini($module . 'en.ini');
            }
            $arr = $trans->toArray();
            $ret = array();
            foreach($arr as $val) {
                $ret = array_merge($ret, $val);
            }

            $trans = $ret;
            $cache->save($trans);
        }
        # get translation object
        $transObj = Zend_Registry::get('Zend_Translate');
        # add the modules translation
        $transObj->addTranslation($trans, $locale);
        # save the transjation to registry
        Zend_Registry::set('Zend_Translate',$transObj);

        return $transObj;
    }
}
