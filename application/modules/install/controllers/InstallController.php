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

class Install_InstallController extends FansubCMS_Controller_Action {
    public function init()
    {
        $envConf = Zend_Registry::get('environmentSettings');
        if(!$envConf->setup && APPLICATION_ENV != 'development') die('locked!');
        $this->_helper->layout()->disableLayout(); // no layout in the installer
    }

    public function indexAction()
    {
        $t = Doctrine::getTable('User_Model_User');
        try {
            $t->count();
            $this->_helper->redirector->gotoSimple('createuser','install','install');
        } catch(Doctrine_Exception $e) {
            $this->view->form = new FansubCMS_Form_Confirmation;
            if($this->request->isPost()) {
                $submit = $this->request->getParam('yes');
                if(!empty($submit)) {
                    Install_Api_DoctrineTool::getInstance()->createTablesFromArray();
                    Install_Api_Migration::getInstance()->setCurrentVersion(Install_Api_Migration::getInstance()->getLatestVersion()); // we are on the top atm
                    $this->_helper->redirector->gotoSimple('createuser','install','install');
                } else {
                    $this->_helper->redirector->gotoSimple('index','index','install');
                }
            }
        }
    }

    public function createuserAction()
    {
        $t = Doctrine::getTable('User_Model_User');
        try {
            $c = $t->count();
            if($c > 0) {
                $this->_helper->redirector->gotoSimple('success','install','install');
            } else {
                $this->view->form = new Install_Form_InstallUser;
                if($this->request->isPost()) {
                    if($this->view->form->isValid($_POST)) {
                        $values = $this->view->form->getValues();
                        $user = new User_Model_User;
                        $user->name = $values['username'];
                        $user->setPassword($values['password1']);
                        $user->email = $values['email'];
                        $user->save();
                        $ur = new User_Model_Role();
                        $ur->User_Model_User = $user;
                        $ur->role_name = 'admin_admin';
                        $ur->save();
                        $this->_helper->redirector->gotoSimple('success','install','install');
                    }
                }
            }
        } catch(Doctrine_Exception $e) {
            $this->_helper->redirector->gotoSimple('index','install','install');
        }
    }

    public function successAction()
    {
        $t = Doctrine::getTable('User_Model_User');
        try {
            $c = $t->count();
            if($c < 1) {
                $this->_helper->redirector->gotoSimple('createuser','install','install');
            }
        } catch(Doctrine_Exception $e) {
            $this->_helper->redirector->gotoSimple('index','install','install');
        }
    }
}
