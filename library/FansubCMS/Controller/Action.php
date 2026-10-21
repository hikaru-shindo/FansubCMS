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
 * This class extends the Zend_Controller_Action to provide some features to
 * the controllers in this application we need.
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @see Zend_Controller_Action
 * @package FansubCMS
 * @subpackage Controller
 * @version 1.0
 */
class FansubCMS_Controller_Action extends Zend_Controller_Action
{
    /**
     *
     * @var Zend_Acl
     */
    public $acl;
    /**
     *
     * @var Zend_Session_Namespace
     */
    public $session;
    /**
     *
     * @var Zend_Controller_Request_Http
     */
    public $request;
    /**
     *
     * @var string
     */
    public $defaultUseRole;
    /**
     *
     * @var FansubCMS_Helper_Delegate
     */
    protected $_delegateHelper;
    /**
     *
     * @var FansubCMS_Cache_Helper
     */
    protected $_cacheHelper;
    /**
     * The class constructor
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->_delegateHelper = new FansubCMS_Helper_Delegate($request->getModuleName());
        $this->_cacheHelper = FansubCMS_Cache_Helper::getInstance();
        parent::__construct($request, $response, $invokeArgs);
        $this->defaultUseRole = 'fansubcms_user_custom_role_logged_in_user';
        $this->request = $request;
        $this->session = Zend_Registry::get('applicationSessionNamespace');
        $this->session->tableActions = array();
        $this->acl = Zend_Registry::get('Zend_Acl');
        $envSettings = Zend_Registry::get('environmentSettings');

        if(!empty($this->session->message)) {
            $this->view->message = $this->session->message;
            $this->view->message_type = $this->session->message_type;
            unset($this->session->message);
            unset($this->session->message_type);
        }

        $this->session->markitup = '';
    }

    /**
     * Checks if user has the right to do privilege on resource
     *
     * @param Zend_Acl_Resource $resource
     * @param string $privilege
     * @return boolean
     */
    public function isAllowed($resource, $privilege)
    {
        if(!$this->acl->has($resource)) {
            return true;
        }

        return $this->acl->isAllowed('fansubcms_user_custom_role_logged_in_user', $resource, $privilege);
    }

    /**
     * Checks if the user is allowd to do something and throws exception if not
     *
     * @param Zend_Acl_Resource $resource
     * @param string $privilege
     * @throws FansubCMS_Exception_Denied
     */
    public function checkAllowed($resource, $privilege)
    {
        if(!$this->isAllowed($resource, $privilege)) {
            throw new FansubCMS_Exception_Denied('The user is not allowed to do this');
        }
    }

    /**
     * translates the given key
     * @param string $key
     * @param array $params
     * @return string
     */
    public function translate($key,$params = array())
    {
        return $this->view->translate($key,$params);
    }

    /**
     *
     * Add a delegate to the controller/action
     * @param string $name The name of the delegate
     */
    protected function _addDelegateType($name)
    {
        $this->_delegateHelper->addDelegateType($name);
    }
    /**
     *
     * Invoke a delegate
     * @param string $name
     * @param string $method
     * @param array $args
     */
    protected function invokeDelegate($name, $method, $args)
    {
        return $this->_delegateHelper->invokeDelegate($name, $method, $args);
    }
}
?>
