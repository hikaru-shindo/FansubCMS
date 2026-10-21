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
class Projects_Api_Assert_IsLeader implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl, Zend_Acl_Role_Interface $role = null, Zend_Acl_Resource_Interface $resource = null, $privilege = null)
    {
        $auth = Zend_Auth::getInstance();

        if(!$auth->hasIdentity()) {
            return false;
        }
        $ident = $auth->getIdentity();

        if($resource->getResourceId() == 'projects_m_project') {
            foreach($resource->Projects_Model_Leader as $leader) {
                if($leader->user_id == $ident->id) {
                    return true;
                }
            }

            return false;
        } else {
            $q = Doctrine_Query::create()
                    ->from('Projects_Model_Leader pl')
                    ->where('user_id = ?', $ident->id);

            if($q->count()) {
                return true;
            }

            return false;
        }
    }
}
