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

class FansubCMS_Controller_Plugin_InstallCheck extends Zend_Controller_Plugin_Abstract
{
    /**
     * check if the cms is already installed or needs update and redirect to installer in one of these cases
     * @see Zend_Controller_Plugin_Abstract::preDispatch()
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if($this->getRequest()->getModuleName() == 'install' || $this->getRequest()->getModuleName() == 'devtools') {
            # we don't need to check while we are in the installer itself
            return;
        }
        # check whether the cms is installed or not
        $t = Doctrine::getTable('User_Model_User');
        try {
            $t->count();
            $installed = true;
        } catch(Doctrine_Exception $e) {
            $installed = false;
        }

        $redirect = new Zend_Controller_Action_Helper_Redirector();

        if($installed) {
            # check if update is needed
            $mig = Install_Api_Migration::getInstance();
            if($mig->getCurrentVersion() < $mig->getLatestVersion()) {
                # update needed
                $redirect->gotoSimple('index','index','install');
            }
        } else {
            $redirect->gotoSimple('index','index','install');
        }
    }
}
