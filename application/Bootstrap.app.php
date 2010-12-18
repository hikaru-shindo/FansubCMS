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

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    private
            $applicationStatus,
            $settings,
            $mailsettings,
            $databasesettings,
            $environmentsettings,
            $routes,
            $layout,
            $cacheManager,
            $acl;
    public
            $frontController;


    /**
     * init
     */

    /**
     * init autoloading
     * @return void
     */
    protected function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                        "namespace" => "",
                        "basePath"  => APPLICATION_PATH));

        return $moduleLoader;
    }

    /**
     * init autoloading of foreign dependencies
     * (have a look on the application.ini for further foreign dependencies autoloading)
     * @return void
     */
    protected function _initAutoloadForeignDependencies() {
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        $autoLoader->registerNamespace('Doctrine')
                ->pushAutoloader(array('Doctrine', 'autoload'));
    }

    /**
     * strip the slashes for request and cookie superglobals. We'll escape ourselves if needed
     */
    protected function _initSuperglobals() {
        if(get_magic_quotes_gpc()) {
            $this->_arrayStripslashes($_GET);
            $this->_arrayStripslashes($_POST);
            $this->_arrayStripslashes($_REQUEST);
            $this->_arrayStripslashes($_COOKIE);
        }
    }

    /**
     * init configuration
     * @return void
     */
    protected function _initConfig() {
        $this->applicationStatus = getenv('APPLICATION_ENV');

        # fetch the application settings
        $this->settings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", "settings", true);
        $this->routes = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", "routes");
        $this->mailsettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/email.ini","email");
        $this->databasesettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/database.ini","database");
        $this->environmentsettings = new Zend_Config_Ini(APPLICATION_PATH . "/configs/environment.ini","environment");
    }

    /**
     * init doctrine
     * @return void
     */
    protected function _initDoctrine() {
        if(empty($this->databasesettings->db->dsn)) {
            $dbDefaultDsn = $this->databasesettings->db->dbms . "://" . $this->databasesettings->db->user . ":" .  $this->databasesettings->db->password . "@" . $this->databasesettings->db->host . "/" . $this->databasesettings->db->database;
        } else {
            $dbDefaultDsn = $this->databasesettings->db->dsn;
        }
        $conn = Doctrine_Manager::connection($dbDefaultDsn, $this->databasesettings->db->database, "defaultConnection");
        $conn->execute("SET NAMES 'UTF8'");
        $conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);

        if(!empty($this->databasesettings->db->dbms) && $this->databasesettings->db->dbms == 'mysql') {
            $conn->setAttribute(Doctrine::ATTR_AUTOCOMMIT, false);
        }

        define("DATABASE_DATE_FORMAT", !empty($this->databasesettings->db->dateformat) ? $this->databasesettings->db->dateformat : "YYYY-MM-dd HH:mm:ss");

        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute('model_loading', 'conservative');
        $manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

        # if theres no file within the directory, Doctrine will halt. So, we check first
        # maybe this can be deleted later on
        $phpFileCount = 0;
        $fhandle = opendir(APPLICATION_PATH . $this->settings->doctrine->models->path);
        while(false != ($file = readdir($fhandle))) {
            if(strpos($file, ".php") != false) {
                $phpFileCount++;
            }
        }
        if($phpFileCount > 0) {
            # just not created for this version yet
            Doctrine::loadModels(APPLICATION_PATH . $this->settings->doctrine->models->path);
            # application.ini does now the include for the base models
            //Doctrine::loadModels(APPLICATION_PATH . $this->settings->doctrine->models->generated);
        }
    }

    /**
     * init application
     * @return void
     */
    protected function _initApplication() {
        Zend_Session::setOptions(array(
                'name' => $this->environmentsettings->page->session->name,
                'save_path' => realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'sessions')
        ));

        Zend_Session::start(); // sollte wenn name feststeht ge�ndert werden

        # get an instance of the frontend controller
        $this->bootstrap('FrontController');
        $this->frontController = $this->getResource('FrontController');

        # set default and module controller directrories
        $this->frontController->setControllerDirectory($this->settings->controllers->toArray());
        $this->frontController->addModuleDirectory(APPLICATION_PATH . "/modules");
        $this->frontController->addModuleDirectory(APPLICATION_PATH . "/addons");

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
        date_default_timezone_set(empty($this->environmentsettings->page->timezone) ? 'Europe/Berlin' : $this->environmentsettings->page->timezone);

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
    protected function _initRouter() {
        if($this->routes->router != null) {
            $router = $this->frontController->getRouter();
            $router->addConfig($this->routes->router, 'routes');
        }
    }

    /**
     * init view
     * @return void
     */
    protected function _initView() {
        # get a view object -> to set the path to view-scripts, -helpers and set a suffix
        $view = new Zend_View();

        # set some header settings
        $view->setEncoding('UTF-8');
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->headMeta()->appendHttpEquiv(
                'Content-Type', 'text/html;charset=utf-8'
        );


        $layoutPath = $this->settings->frontend->layoutpath;
        $layoutAdd = isset($this->environmentsettings->page->layout) ? $this->environmentsettings->page->layout : 'default';
        $layoutPath = $layoutPath . DIRECTORY_SEPARATOR . $layoutAdd;

        if(!file_exists($layoutPath . DIRECTORY_SEPARATOR . 'frontend.phtml'))
            die('The layout does not exist or frontend.phtml is missing!');
        if(!file_exists($layoutPath . DIRECTORY_SEPARATOR . 'admin.phtml'))
            die('The layout does not exist or admin.phtml is missing!');

        if(!empty($layoutPath)) {
            # set the default view path
            $view->addScriptPath(
                    (!empty($this->settings->view->defaultPath) ? $this->settings->view->defaultPath : APPLICATION_PATH . "/views/scripts/"));
            # set the view helper path
            $view->addHelperPath(
                    (!empty($this->settings->view->helperPath) ? $this->settings->view->helperPath : APPLICATION_PATH . "/views/helpers/") ,
                    (!empty($this->settings->view->helperNamespace) ? $this->settings->view->helperNamespace : "Zend_View_Helper_")
            );


            # view renderer
            $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
            $viewRenderer->setView($view);
            # make the view use the renderer we just configured
            Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

            $this->layout = Zend_Layout::startMvc(array('layoutPath' => $layoutPath,'layout'=>'frontend'));

            # assign layout variables
            $this->layout->assign('group', $this->environmentsettings->page->group->name);
            $this->layout->assign('group_short',  $this->environmentsettings->page->group->short);
        }
    }
    /**
     * init the gadgets
     * @return void
     */
    protected function _initGadgets() {
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/environment.ini','gadgets');
        $config = $config->toArray();
        if(!isset($config['gadget']) || !is_array($config['gadget'])) {
            $this->layout->getView()->gadgets = array();
            return;
        }
        foreach($config['gadget'] as $k => $v) {
            if(!isset($config['gadget'][$k]['params']) || !is_array($config['gadget'][$k]['params']))
                $config['gadget'][$k]['params'] = array();
        }
        $this->layout->getView()->gadgets = $config['gadget'];
    }

    /**
     * init cache manager
     * @return Zend_Cache_Manager
     */
    protected function _initCache() {
        define('CACHE_PATH', realpath(APPLICATION_PATH . '/data/cache'));
        $cm = new Zend_Cache_Manager();

        $lifetime = APPLICATION_ENV == 'development' ? 30 : 3600; # in development keep cache 30 seconds otherwise one hour

        $options = array(
                'frontend' => array(
                        'name' => 'Core',
                        'options' => array(
                                'lifetime' => $lifetime,
                                'automatic_serialization' => true
                        )
                ),
                'backend' => array(
                        'name' => 'File',
                        'options' => array(
                                'cache_dir' => CACHE_PATH
                        )
        ));


        $cm->setCacheTemplate('FansubCMS', $options);

        Zend_Registry::set('Zend_Cache_Manager', $cm);
        $this->cacheManager = $cm;
        return $cm;
    }

    /**
     * init I18n
     * @return void
     */
    protected function _initI18n() {
        $cm = $this->cacheManager;
        $cache = $cm->getCache('FansubCMS');
        $trans = $cache->load('Translate');
        // there are no translations or cache is invalid - generate cache!
        if(!$trans) {
            $locale = $this->environmentsettings->locale;
            $trans = new Zend_Translate('ini',APPLICATION_PATH.'/locale/'.$locale.'.ini',$locale);
            // add all the translations
            $modules = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . '.ini');
            foreach($modules as $module) {
                $trans->addTranslation($module,$locale);
            }
            $addons = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . '.ini');
            foreach($addons as $addon) {
                $trans->addTranslation($addon,$locale);
            }
            $cache->save($trans);
        }

        // save the translation in the registry
        Zend_Registry::set('Zend_Translate',$trans);
    }

    /**
     * init acl
     * @return void
     */
    protected function _initAcl() {
        $cm = $this->cacheManager;
        $cache = $cm->getCache('FansubCMS');
        $config = $cache->load('Acl_Settings');
        if(!$config) {
            $config = array();
            $modules = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini');
            $modules = array_merge($modules,glob(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'module.ini'));
            foreach($modules as $module) {
                $cleanName = str_replace(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR, '', $module);
                $cleanName = str_replace(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR, '', $cleanName);
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
        foreach($config as $options) {
            $acl->setOptions($options);
        }
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $ident = Zend_Auth::getInstance()->getIdentity();

            $role = new Zend_Acl_Role('fansubcms_user_custom_role_logged_in_user');
            $inherit = $ident->getRoles();

            foreach($inherit as $key => $value) {
                if(!$acl->hasRole($value)) {
                    unset($inherit[$key]);
                }
            }

            $acl->addRole($role, $inherit);
        }
        Zend_Registry::set('Zend_Acl', $acl);
        $this->acl = $acl;
    }

    /**
     * init plugins
     * @return void
     */
    protected function _initPlugins() {
        # only embed the debugging plugin if application status is development or testing
        if($this->applicationStatus == "development") {
            # embed the ZFDebug Plugin/console
            $debug = new ZFDebug_Controller_Plugin_Debug(array(
                            'jquery_path' => '',
                            'plugins' => array(
                                    'Variables',
                                    'File' => array('basePath' => APPLICATION_PATH),
                                    'Memory',
                                    'Time',
                                    'Registry',
                                    'Html',
                                    'Exception'
                            )
            ));
            $this->frontController->registerPlugin($debug);
        }
        # init error handler
        $this->frontController->throwExceptions(false);
        $errorhandler = new Zend_Controller_Plugin_ErrorHandler();
        $errorhandler->setErrorHandler(array('module' => 'default', 'controller' => 'error', 'action' => 'error'));
        $this->frontController->registerPlugin($errorhandler);
        # set the admin layout using plugin
        $layoutPath = $this->settings->backend->layoutpath;
        $layoutAdd = isset($this->environmentsettings->page->layout) ? $this->environmentsettings->page->layout : 'default';
        $layoutPath .= DIRECTORY_SEPARATOR . $layoutAdd;

        if(!empty($layoutPath)) {
            $layoutPlugin = new FansubCMS_Controller_Plugin_Layout();
            $layoutPlugin->registerAdminLayout('admin',$layoutPath,'admin');
            $this->frontController->registerPlugin($layoutPlugin);
        }

        $layoutVersionPlugin = new FansubCMS_Controller_Plugin_LayoutVersion($layoutAdd);
        $this->frontController->registerPlugin($layoutVersionPlugin);

        # not-logged-in-so-go-to-login plugin
        $aclPlugin = new FansubCMS_Controller_Plugin_Acl(Zend_Auth::getInstance(),$this->acl);
        $this->frontController->registerPlugin($aclPlugin);

        # the navigation plugin
        $navPlugin = new FansubCMS_Controller_Plugin_Navigation();
        $this->frontController->registerPlugin($navPlugin);

    }

    /*
     * Helpers
    */

    /**
     * stripslashes() for arrays
     * @param array $var
     */
    protected function _arrayStripslashes(&$var) {
        if(is_string($var)) {
            $var = stripslashes($var);
        } else {
            if(is_array($var)) {
                foreach($var AS $key => $value) {
                    $this->_arrayStripslashes($var[$key]);
                }
            }
        }
    }
}