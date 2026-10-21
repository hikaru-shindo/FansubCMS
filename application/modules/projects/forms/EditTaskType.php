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

class Projects_Form_EditTaskType extends Zend_Form
{

    public function __construct(array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'add-task-type' : 'edit-task-type')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'add-task-type' : 'edit-task-type');

        # title
        $title = $this->createElement('text', 'title')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('StringLength', true, array(
                'min' => 2,
                'max' => 255,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'default_form_error_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'default_form_error_length'
                )))
                ->setRequired(true)
                ->setValue(isset($values['title']) ? $values['title'] : null)
                ->setLabel('project_admin_field_task_title');

        # desctiption
        $description = $this->createElement('textarea', 'description');
        $description->setValue(isset($values['description']) ? $values['description'] : null)
                ->addFilter('StringTrim')
                ->setAttrib('cols', 40)
                ->setAttrib('rows', 15)
                ->setLabel('description');

        $this->addElement($title)
        ->addElement($description)
        # edit button
        ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }
}
