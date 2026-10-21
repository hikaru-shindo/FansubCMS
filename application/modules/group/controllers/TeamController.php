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

class Group_TeamController extends FansubCMS_Controller_Action {
    public function memberAction() {
        $this->_addDelegateType('Sorting');
        $this->view->users = $this->invokeDelegate('Sorting', 'sortTeam', array(User_Model_User::getTeam(false)));
        $this->view->title = $this->translate('group_member_title');
    }

    public function detailAction() {
        $username = $this->request->getParam('username',false);
        if($username == false) {
            return $this->_helper->redirector('member','team','group');
        }
        $table = Doctrine::getTable('User_Model_User');
        $this->view->user = $table->getTeamMemberByName($username);
        if(!$this->view->user) {
            return $this->_helper->redirector('member','team','group');
        }
    }
}
