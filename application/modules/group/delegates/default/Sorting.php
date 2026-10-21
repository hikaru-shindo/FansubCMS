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
class Group_Delegate_Default_Sorting
{
    /**
     * The HTTP request
     *
     * @var Zend_Controller_Request_Http
     */
    public $request;
    public $settings;

    public function __construct($settings, $request)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    public function sortTeam($team)
    {
        $active = array();
        $inactive = array();
        if(!empty($team)) {
            foreach($team as $member) {
                if($member['active'] == 'yes') {
                    $active[] = $member;
                } else {
                    $inactive[] = $member;
                }
            }
        }
        return array('active'=>$active,'inactive'=>$inactive);
    }
}
