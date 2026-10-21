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

class Projects_Form_EditProject extends Zend_Form {

    public function __construct(array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'addproject' : 'editproject')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'addproject' : 'editproject');

        # name
        $name = $this->createElement('text', 'name')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', true, array(
                'min' => 3,
                'max' => 255,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired(true)
                ->setValue(isset($values['name']) ? $values['name'] : null)
                ->setLabel('project_admin_field_name');

        # name-jp
        $namejp = $this->createElement('text', 'name_jp')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', false, array(
                'min' => 3,
                'max' => 255,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired(true)
                ->setValue(isset($values['name_jp']) ? $values['name_jp'] : null)
                ->setLabel('project_admin_field_name_jp');

        # episode count
        $episodes = $this->createElement('text', 'episode_count')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Int', false, array(
                'meesages' => array(
                        Zend_Validate_Int::NOT_INT => 'default_form_error_int'
                )
                ))
                ->setValue(isset($values['element_count']) ? $values['element_count'] : null)
                ->setLabel('project_admin_field_episode_count');


        # status
        $status = $this->createElement('select', 'status')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setLabel('project_admin_field_status')
                ->setRequired(true)
                ->setValue(isset($values['status']) ? $values['status'] : 'planned')
                ->setMultiOptions(array('planned' => 'project_status_planned',
                'progress' => 'project_status_progress',
                'pending' => 'project_status_pending',
                'completed' => 'project_status_completed',
                'dropped' => 'project_status_dropped',
                'licensed' => 'project_status_licensed'));


        # desctiption
        $description = $this->createElement('textarea', 'description');
        $description->setValue(isset($values['description']) ? $values['description'] : null)
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setAttrib('cols', 40)
                ->setAttrib('rows', 15)
                ->setLabel('description');

        $poster = $this->createElement('text', 'poster')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', false, array(
                'min' => 5,
                'max' => 255,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired(true)
                ->setValue(isset($values['poster']) ? $values['poster'] : null)
                ->setLabel('project_admin_field_poster');

        $miniposter = $this->createElement('text', 'mini_poster')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', false, array(
                'min' => 5,
                'max' => 255,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired(true)
                ->setValue(isset($values['mini_poster']) ? $values['mini_poster'] : null)
                ->setLabel('project_admin_field_mini_poster');

        $uTable = Doctrine::getTable('User_Model_User');
        $users = $uTable->getMultiOptions();

        $leaders = $this->createElement('multiselect', 'leaders');
        $leaders->setMultiOptions($users)
                ->setLabel('project_admin_field_leader')
                ->setValue(isset($values['Projects_Model_Leader']) ? $values['Projects_Model_Leader'] : array());

        $private = $this->createElement('radio', 'private')
                ->setMultiOptions(array(
                'yes' => 'yes_term',
                'no' => 'no_term'
                ))
                ->setLabel('project_admin_field_private')
                ->setValue(isset($values['private']) ? $values['private'] : 'yes');

        # add elements to the form
        $this->addElement($name)
                ->addElement($namejp)
                ->addElement($episodes)
                ->addElement($status)
                ->addElement($description)
                ->addElement($poster)
                ->addElement($miniposter)
                ->addElement($leaders)
                ->addElement($private)
                # edit button
                ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

}
