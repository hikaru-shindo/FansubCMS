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

class User_Form_EditUser extends Zend_Form {

    public function __construct(array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'adduser' : 'edituser')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'adduser' : 'edituser');

        # username
        $usernameInDbValidator = new FansubCMS_Validator_NoRecordExists('User_Model_User', 'name');
        $usernameInDbValidator->setMessages(array(
            FansubCMS_Validator_NoRecordExists::RECORD_EXISTS => 'user_form_error_user_exists'
        ));;

        $username = $this->createElement('text', 'username')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', true, array(3, 255))
                ->setRequired(true)
                ->setValue(isset($values['name']) ? $values['name'] : null)
                ->setLabel('user_admin_field_username');
        if($insert === true) {
            $username->addValidator($usernameInDbValidator);
        }
        # password
        $password = $this->createElement('password', 'password1');
        $password->setRequired($insert ? true : false)
                ->setLabel('user_admin_field_password');
        if ($insert) {
            $password->addValidator('NotEmpty', true, array(
                    'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
            )));
        }

        $password->addValidator('StringLength', false, array(
                'min' => 8,
                'max' => 64,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )));

        # retype password
        $repassword = $this->createElement('password', 'password2');
        $repassword->addValidator('StringLength', true, array(
                'min' => 8,
                'max' => 64,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired($insert ? true : false)
                ->setLabel('user_admin_field_retype_password');
        if ($insert) {
            $repassword->addValidator('NotEmpty', true, array(
                    'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
            )));
        }

        $repassword->addValidator('Identical', false, array(
                'messages' => array(
                        Zend_Validate_Identical::NOT_SAME => 'user_form_error_passwords_not_match'
                )
                ));

        # Email
        $email = $this->createElement('text', 'email');
        $email->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('EmailAddress', false, array(
                'allow' => Zend_Validate_Hostname::ALLOW_DNS,
                'domain' => true,
                'messages' => array(
                        Zend_Validate_EmailAddress::DOT_ATOM => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_FORMAT => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_HOSTNAME => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_LOCAL_PART => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_MX_RECORD => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_SEGMENT => 'default_form_error_email',
                        Zend_Validate_EmailAddress::LENGTH_EXCEEDED => 'default_form_error_email',
                        Zend_Validate_EmailAddress::QUOTED_STRING => 'default_form_error_email'
                )))
                ->setRequired(true)
                ->setValue(isset($values['email']) ? $values['email'] : null)
                ->setLabel('email');

        # Roles
        $roles = $this->createElement('multiCheckbox', 'roles')
                ->setLabel('user_admin_field_roles')
                ->setValue(isset($values['User_Model_Role']) ? $values['User_Model_Role'] : null)
                ->setMultiOptions(User_Model_Role::getRoles());
        # Tasks
        $table = Doctrine_Core::getTable('User_Model_Task');
        if (count($table->getTasks())) {
            $tasks = $this->createElement('multiCheckbox', 'tasks')
                    ->setLabel('user_admin_field_tasks')
                    ->setValue(isset($values['User_Model_Task']) ? $values['User_Model_Task'] : null)
                    ->setMultiOptions($table->getTasks());
        }

        # Profiltext
        $description = $this->createElement('textarea', 'description');
        $description->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setValue(isset($values['description']) ? $values['description'] : null)
                ->setAttrib('cols', 40)
                ->setAttrib('rows', 15)
                ->setLabel('description');

        $team = $this->createElement('radio', 'show_team')
                ->setMultiOptions(array(
                'yes' => 'yes_term',
                'no' => 'no_term'
                ))
                ->setLabel('user_admin_field_show_team')
                ->setValue(isset($values['show_team']) ? $values['show_team'] : 'yes');

        $active = $this->createElement('radio', 'active')
                ->setMultiOptions(array(
                'yes' => 'yes_term',
                'no' => 'no_term'
                ))
                ->setLabel('user_admin_field_active')
                ->setValue(isset($values['active']) ? $values['active'] : 'yes');

        $activated = $this->createElement('radio', 'activated')
                ->setMultiOptions(array(
                'yes' => 'yes_term',
                'no' => 'no_term'
                ))
                ->setLabel('user_admin_field_activated')
                ->setValue(isset($values['activated']) ? $values['activated'] : 'yes');

        # add elements to the form
        $this->addElement($username)
                ->addElement($password)
                ->addElement($repassword)
                ->addElement($email)
                ->addElement($roles);
        if (isset($tasks)) {
            $this->addElement($tasks);
        }
        $this->addElement($description)
                ->addElement($team)
                ->addElement($active)
                ->addElement($activated)
                # edit button
                ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

    public function isValid($data) {
        $retypePw = $this->getElement('password2');
        $retypePw->getValidator('Identical')->setToken($data['password1']);
        return parent::isValid($data);
    }

}
