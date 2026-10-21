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
 * This class extends the Zend_Controller_Dispatcher_Standard.
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @see Zend_Controller_Dispatcher_Standard
 * @package FansubCMS
 * @subpackage Controller
 */
class FansubCMS_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Standard
{

    /**
     * The application configuration
     *
     * @var Zend_Config
     */
    protected $_settings;
    /**
     * Is this request an administrative request?
     *
     * @var boolean
     */
    protected $_isAdminRequest = false;

    public function __construct(array $params = array())
    {
         parent::__construct($params);
    }

    /**
     * Dispatch to a controller/action
     *
     * By default, if a controller is not dispatchable, dispatch() will throw
     * an exception. If you wish to use the default controller instead, set the
     * param 'useDefaultControllerAlways' via {@link setParam()}.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @return void
     * @throws Zend_Controller_Dispatcher_Exception
     */
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->_handleRequest($request);
        parent::dispatch($request, $response);
    }

    /**
     * get version for module (based on module and/or controller version settings)
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return bool
     */
    private function _handleRequest(Zend_Controller_Request_Abstract $request)
    {
        if(!Zend_Registry::isRegistered("settings")) {
            return false;
        }

        $this->_settings = Zend_Registry::get("settings");

        // set to locale
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();

        // is this request administrative?
        if($moduleName == 'admin' || $controllerName == 'admin') {
            $this->_isAdminRequest = true;
        } else {
            $this->_isAdminRequest = false;
        }

        // start handling the request
        $this->_handleView($moduleName, $controllerName);
    }

    /**
     * handle view version paths - based on request
     *
     * @param string $module
     * @param string $controller
     * @return void
     */
    private function _handleView($module, $controller)
    {
        // Determine which layout to use
        if($this->isAdminRequest()) {
            $layout = empty($this->_settings->page->adminLayout) ?
                $this->_settings->page->layout : $this->_settings->page->adminLayout;
        } else {
            $layout = $this->_settings->page->layout;
        }

        // check if layout is available
        $layoutPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout;
        $layoutFile = $this->isAdminRequest() ? 'admin.phtml' : 'frontend.phtml';
        if(!is_readable($layoutPath . DIRECTORY_SEPARATOR . $layoutFile)) {
            throw new Exception('The layout could not be found or is not readable.');
        }
        // start mvc layout
        $layoutInstance = Zend_Layout::startMvc(array('layoutPath' => $layoutPath, 'layout' => $this->isAdminRequest() ? 'admin' : 'frontend'));

        // assign some layout vars
        $layoutInstance->assign('language', $this->_settings->locale);
        $layoutInstance->assign('group', $this->_settings->page->group->name);
        $layoutInstance->assign('group_short', $this->_settings->page->group->short);

        // get view and renderer
        $view = Zend_Layout::getMvcInstance()->getView();
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper("ViewRenderer");

        // set some default view values
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->headMeta()->appendHttpEquiv(
                'Content-Type', 'text/html;charset=utf-8'
        );
        $view->setEncoding('UTF-8');

        // set view paths
        $viewRenderer->setView($view);

        $view->addScriptPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . 'gadgets' .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . 'default' .
                DIRECTORY_SEPARATOR . 'scripts');

        $view->addScriptPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . 'gadgets' .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . $layout .
                DIRECTORY_SEPARATOR . 'scripts');

        $view->addScriptPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . 'cms' .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . 'default' .
                DIRECTORY_SEPARATOR . 'scripts');

        $cmsViewDir = APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . 'cms' .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . $layout .
                DIRECTORY_SEPARATOR . 'scripts';
        if(is_readable($cmsViewDir)) {
            $view->addScriptPath($cmsViewDir);
        }

        $view->addScriptPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . $module .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . 'default' .
                DIRECTORY_SEPARATOR . 'scripts');

        $moduleViewDir = APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . $module .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . $layout .
                DIRECTORY_SEPARATOR . 'scripts';
        if(is_readable($moduleViewDir)) {
            $view->addScriptPath($moduleViewDir);
        }

        $resourceViewDir = APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "resource" .
                DIRECTORY_SEPARATOR . 'templates' .
                DIRECTORY_SEPARATOR . $module .
                DIRECTORY_SEPARATOR . $layout;
        if(is_readable($resourceViewDir)) {
            $view->addScriptPath($resourceViewDir);
        }


        // set path to default view helpers
        $view->addHelperPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "layouts" .
                DIRECTORY_SEPARATOR . 'default' .
                DIRECTORY_SEPARATOR . 'helpers', (!empty($this->_settings->view->helperNamespace) ? $this->_settings->view->helperNamespace : "Zend_View_Helper"));

        // set path to versioned layout view helpers
        $view->addHelperPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "layouts" .
                DIRECTORY_SEPARATOR . $layout .
                DIRECTORY_SEPARATOR . 'helpers', (!empty($this->_settings->view->helperNamespace) ? $this->_settings->view->helperNamespace : "Zend_View_Helper"));

        // set path to module view helpers
        $view->addHelperPath(APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . $module .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . "helpers", (!empty($this->_settings->view->helperNamespace) ? $this->_settings->view->helperNamespace : "Zend_View_Helper"));

        // set path to module view versioned helpers
        $layoutModuleViewHelper = APPLICATION_PATH .
                DIRECTORY_SEPARATOR . "modules" .
                DIRECTORY_SEPARATOR . $module .
                DIRECTORY_SEPARATOR . "views" .
                DIRECTORY_SEPARATOR . $layout .
                DIRECTORY_SEPARATOR . "helpers";
        $view->addHelperPath($layoutModuleViewHelper, (!empty($this->_settings->view->helperNamespace) ? $this->_settings->view->helperNamespace : "Zend_View_Helper"));

    }

    // helper methods

    public function isAdminRequest()
    {
        return $this->_isAdminRequest;
    }
}
