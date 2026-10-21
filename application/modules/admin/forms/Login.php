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

class Admin_Form_Login extends Zend_Form {

    public function __construct($options = null) {
        parent::__construct($options);

        $this->setName('login')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', 'login');

        # username
        $username = $this->createElement('text', 'username');
        $username->addValidator('NotEmpty', true, array(
                    'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                        )))
                ->setRequired(true)
                ->setLabel('admin_field_username');

        # password
        $password = $this->createElement('password', 'password');
        $password->addValidator('NotEmpty', true, array(
                    'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                        )))
                ->setRequired(true)
                ->setLabel('admin_field_password');

        # add elements to the form
        $this->addElement($username)
                ->addElement($password)
                # login button
                ->addElement('submit', 'submit', array('label' => 'admin_field_login', 'class' => 'button'));
    }

}
