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

class Install_IndexController extends FansubCMS_Controller_Action {
    public function init() {
        $envConf = Zend_Registry::get('environmentSettings');
        if(!$envConf->setup && APPLICATION_ENV != 'development') die('Installer locked!');
        $this->_helper->layout()->disableLayout(); // no layout in the installer
    }

    public function indexAction() {
        $migration = new Doctrine_Migration();
        $this->view->databaseCurrent = Install_Api_Migration::getInstance()->getCurrentVersion();
        $this->view->databaseLatest = Install_Api_Migration::getInstance()->getLatestVersion();

        $this->view->databaseUpdateNeeded = false;
        $t = Doctrine::getTable('User_Model_User');
        try {
            $t->count();
            $this->view->databaseInitNeeded = false;
        } catch(Doctrine_Exception $e) {
            $this->view->databaseInitNeeded = true;
        }

        if(!$this->view->databaseInitNeeded && $this->view->databaseCurrent < $this->view->databaseLatest) {
            $this->view->databaseUpdateNeeded = true;
        }

        $status = new stdClass;

        if(is_writable(realpath(APPLICATION_PATH.'/data/cache'))) {
            $status->cache = true;
        }
        if(is_writable(realpath(APPLICATION_PATH.'/data/sessions'))) {
            $status->sessions = true;
        }
        if(is_writable(realpath(realpath(getenv('PHP_SELF')).'/media/common/images/tmp'))) {
            $status->captcha = true;
        }
        if(is_writable(realpath(realpath(getenv('PHP_SELF')).'/upload'))) {
            $status->upload = true;
        }
        if(is_writable(realpath(APPLICATION_PATH.'/resource/static'))) {
            $status->resourceStatic = true;
        }
        $this->view->requirements = $status;
    }
}
