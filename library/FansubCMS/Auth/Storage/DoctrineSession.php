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
 * This class stores the user object as an array in the session and retrieves it as an object
 * @package FansubCMS
 * @subpackage Authentication
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class FansubCMS_Auth_Storage_DoctrineSession extends Zend_Auth_Storage_Session
{
    protected static $_data;

    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        parent::__construct($namespace, $member);
    }

    public function write($contents)
    {
        parent::write($contents->id);
    }

    public function read()
    {
        if(empty(self::$_data)) {
            self::$_data = Doctrine_Core::getTable('User_Model_User')->find(parent::read());
        }

        return self::$_data;
    }
}
