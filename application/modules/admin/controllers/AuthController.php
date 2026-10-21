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

class Admin_AuthController extends FansubCMS_Controller_Action
{
    public function loginAction()
    {
        $settings = Zend_Registry::get('environmentSettings');
        $layoutVersion = $settings->page->layout;

        # we don't need the admin menu
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('frontend');
        $layout->setLayoutPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layoutVersion);
        # actually do the login stuff
        $form = new Admin_Form_Login();
        $req = $this->getRequest();
        $this->view->form = $form;
        if($req->isPost()) {
            if($form->isValid($_POST)) {
                $values = $form->getValues();
                if(User_Model_User::login($values['username'],$values['password'])->isValid()) {
                    return $this->_helper->redirector('index','index','admin');
                }
           }
        }
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('login');
        die;
    }
}
