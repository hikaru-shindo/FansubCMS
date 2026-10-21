<?php
/*
 * This file is part of FansubCMS.
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
 * This controller should handle errors in the application.
 *
 * @package FansubCMS
 * @subpackage Controllers
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class Cms_ErrorController extends FansubCMS_Controller_Action
{
    /**
     * This function handles all the general errors.
     * @return void
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $this->view->pageTitle = $this->translate("error_encountered");
        if($errors == null) {
            $errors = new stdClass();
            $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER;
            $errors->exception = new Exception("Invalid controller specified (error)");
            $errors->request = $this->getRequest();
        }
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;

        if($errors->exception instanceof FansubCMS_Exception_Denied) {
            $this->view->error = $this->translate("denied_error");
            $this->getResponse()->setHttpResponseCode(403); // forbidden,
                                                        // alternative 401 unauthorized
                                                        // s. http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
            $this->renderScript('error/denied.phtml');
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->error = $this->translate("notfound_error");
                $this->renderScript('error/40x.phtml');
                break;
            default:
                // application error
                FansubCMS_Helper_Log::getInstance()->log($this->view->exception, Zend_Log::CRIT);
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->error = $this->translate("application_error");
                $this->renderScript('error/50x.phtml');
                break;
        }
    }
}
