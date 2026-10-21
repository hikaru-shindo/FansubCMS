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
 * This class implements the Zend_Auth_Adapter_Interface for Doctrine
 * @see Zend_Auth_Adapter_Interface
 * @package FansubCMS
 * @subpackage Authentication
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 *
 */
class FansubCMS_Auth_Adapter implements Zend_Auth_Adapter_Interface {
    private $_username;
    private $_password;

    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * Performs an authentication attempt using Doctrine User class.
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot
     *                                     be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $result = null;

        try {
            $q = Doctrine_Query::create()
                ->from('User_Model_User u')
                ->where('u.name = ?', $this->_username)
                ->andWhere('u.activated = ?','yes');

            $user = $q->fetchOne();
            if ($user == NULL) {
                $result = new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                        null,
                        array('sorry, login ' . $this->_username . ' was not found'));
            } else {
                if($user->validatePassword($this->_password)) {
                    $result = new Zend_Auth_Result(
                            Zend_Auth_Result::SUCCESS,
                            $user,
                            array());
                } else {
                    $result = new Zend_Auth_Result(
                            Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                            $user,
                            array('sorry, the password you entered was invalid for user ' .
                                                    $this->_username));
                }
            }
            return $result;
        } catch(Exception $e) {
            throw new Zend_Auth_Adapter_Exception($e->getMessage());
        }
    }
}
