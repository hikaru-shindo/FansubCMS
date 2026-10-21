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

/**
 * Bootstrap.php
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @version 0.1
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var Zend_Controller_Front
     */
    public $frontController;
    /**
     * @var string
     */
    public $applicationStatus;
    /**
     * @var Zend_Config_Ini
     */
    public $settings;
    /**
     * @var Zend_Config_Ini
     */
    public $mailsettings;
    /**
     * @var Zend_Config_Ini
     */
    public $databasesettings;
    /**
     * @var Zend_Config_Ini
     */
    public $environmentsettings;
    /**
     * @var Zend_Config_Ini
     */
    public $routes;
    /**
     * @var Zend_Cache_Manager
     */
    public $cacheManager;

    /**
     * init
     */

    /**
     * init autoloading
     * @return void
     */
    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    "namespace" => "",
                    "basePath" => APPLICATION_PATH));

        return $moduleLoader;
    }

    /**
     * init autoloading of foreign dependencies
     * (have a look on the application.ini for further foreign dependencies autoloading)
     * @return void
     */
    protected function _initAutoloadForeignDependencies()
    {
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        $autoLoader->registerNamespace('Doctrine')
                ->pushAutoloader(array('Doctrine', 'autoload'));
    }

    /**
     * init configuration
     * @return void
     */
    protected function _initConfig()
    {
        $this->applicationStatus = getenv('APPLICATION_ENV');

        # fetch the application settings
        $this->settings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", "settings", true);
        $this->routes = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", "routes");
        $this->mailsettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/email.ini", "email");
        $this->databasesettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/database.ini", "database");
        $this->environmentsettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/environment.ini", "environment");

        # merge settings in main settings obj
        $this->settings->merge($this->mailsettings);
        $this->settings->merge($this->databasesettings);
        $this->settings->merge($this->environmentsettings);
    }

    /**
     * init doctrine
     * @return void
     */
    protected function _initDoctrine()
    {
        if (empty($this->settings->db->dsn)) {
            $dbDefaultDsn = $this->settings->db->dbms . "://" . $this->settings->db->user . ":" . $this->settings->db->password . "@" . $this->settings->db->host . "/" . $this->settings->db->database;
        } else {
            $dbDefaultDsn = $this->settings->db->dsn;
        }

        $conn = Doctrine_Manager::connection($dbDefaultDsn, $this->settings->db->database, "defaultConnection");
        $conn->execute("SET NAMES 'UTF8'");
        $conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);

        if(!defined('DATABASE_DATE_FORMAT')) {
            define("DATABASE_DATE_FORMAT", !empty($this->settings->db->dateformat) ? $this->settings->db->dateformat : "YYYY-MM-dd HH:mm:ss");
        }

        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute('model_loading', 'conservative');
        $manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
    }

    /**
     * init application
     * @return void
     */
    protected function _initApplication()
    {
        Zend_Session::setOptions(array(
                    'name' => $this->settings->page->session->name,
                    'save_path' => realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'sessions')
                ));

        Zend_Session::start(); // sollte wenn name feststeht ge�ndert werden
        # get an instance of the frontend controller
        $this->bootstrap('FrontController');
        $this->frontController = Zend_Controller_Front::getInstance();

        # now add our own dispatcher
        $this->frontController->setDispatcher(new FansubCMS_Controller_Dispatcher_Standard());

        # prefix default module as well
        $this->frontController->setParam('prefixDefaultModule', true);

        # set default and module controller directrories
        //  $this->frontController->setControllerDirectory($this->settings->controllers->toArray());
        $this->frontController->addModuleDirectory(APPLICATION_PATH . "/modules");
        $this->frontController->removeControllerDirectory('default');

        # set default module
        $this->frontController->setDefaultModule("news");

        # Init application-wide Session
        $applicationSessionNamespace = new Zend_Session_Namespace('application');
        $applicationSessionNamespace->tstamp = (!isset($applicationSessionNamespace->tstamp)) ? time() : $applicationSessionNamespace->tstamp;

        # add it to the registry
        Zend_Registry::set('applicationSessionNamespace', $applicationSessionNamespace);

        # Init authentication Session
        $authSessionNamespace = new Zend_Session_Namespace('Zend_Auth');

        # add it to the registry
        Zend_Registry::set('AuthSessionNamespace', $authSessionNamespace);

        # set timezone
        date_default_timezone_set(empty($this->settings->page->timezone) ? 'Europe/Berlin' : $this->settings->page->timezone);

        # hook to settings
        $this->settings->applicationStatus = $this->applicationStatus;

        # hook needed objects/values to the zend registry
        Zend_Registry::set('settings', $this->settings);
        Zend_Registry::set('applicationStatus', $this->applicationStatus);
        Zend_Registry::set('environmentSettings', $this->environmentsettings);
        Zend_Registry::set('emailSettings', $this->mailsettings);
    }

    /**
     * init router
     * @return void
     */
    protected function _initRouter()
    {
        if ($this->routes->router != null) {
            $router = $this->frontController->getRouter();
            $router->addConfig($this->routes->router, 'routes');
        }
    }

    /**
     * init cache manager
     * @return Zend_Cache_Manager
     */
    protected function _initCache()
    {
        if(!defined('CACHE_PATH')) {
            define('CACHE_PATH', realpath(APPLICATION_PATH . '/data/cache'));
        }
        $cm = new Zend_Cache_Manager();

        Zend_Registry::set('Zend_Cache_Manager', $cm);
        $this->cacheManager = $cm;
        return $cm;
    }

    /**
     * init I18n
     * @return void
     */
    protected function _initI18n()
    {
        $locale = new Zend_Locale($this->settings->locale);
        $trans = new Zend_Translate('Array',array(''=>''), $this->settings->locale);

        # the translations itself will be added in module bootstraps

        // save the translation in the registry
        Zend_Registry::set('Zend_Locale', $locale);
        Zend_Registry::set('Zend_Translate', $trans);
    }

    /**
     * init plugins
     * @return void
     */
    protected function _initPlugins()
    {
        # only embed the debugging plugin if application status is development or testing
        if ($this->applicationStatus == "development" && class_exists("ZFDebug_Controller_Plugin_Debug")) {
            # embed the ZFDebug Plugin/console
            $debug = new ZFDebug_Controller_Plugin_Debug(array(
                        'jquery_path' => '',
                        'plugins' => array(
                            'Variables',
                            'File' => array('basePath' => APPLICATION_PATH),
                            'Html',
                            'Exception'
                        )
                    ));
            $this->frontController->registerPlugin($debug);
        }
        # init error handler
        $this->frontController->throwExceptions(false);
        $errorhandler = new Zend_Controller_Plugin_ErrorHandler();
        $errorhandler->setErrorHandler(array('module' => 'cms', 'controller' => 'error', 'action' => 'error'));
        $this->frontController->registerPlugin($errorhandler);

        # gadget plugin
        $gadgetPlugin = new FansubCMS_Controller_Plugin_Gadget();
        $this->frontController->registerPlugin($gadgetPlugin);

        # not-logged-in-so-go-to-login plugin
        $aclPlugin = new FansubCMS_Controller_Plugin_Acl(Zend_Auth::getInstance()->setStorage(new FansubCMS_Auth_Storage_DoctrineSession));
        $this->frontController->registerPlugin($aclPlugin);

        # check if install or update is needed
        $installPlugin = new FansubCMS_Controller_Plugin_InstallCheck();
        $this->frontController->registerPlugin($installPlugin);

        # the navigation plugin
        $navPlugin = new FansubCMS_Controller_Plugin_Navigation();
        $this->frontController->registerPlugin($navPlugin);
    }

}
