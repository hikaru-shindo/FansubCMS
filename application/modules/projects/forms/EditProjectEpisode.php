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

class Projects_Form_EditProjectEpisode extends Zend_Form {

    public function __construct(array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'addprojectepisode' : 'editprojectepisode')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'addprojectepisode' : 'editprojectepisode');

        # title
        $title = $this->createElement('text', 'title')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setLabel('project_admin_field_episode_title')
                ->setValue(isset($values['title']) ? $values['title'] : null);

        # number
        $number = $this->createElement('text', 'number')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('Int', false, array(
                'meesages' => array(
                        Zend_Validate_Int::NOT_INT => 'default_form_error_int'
                )
                ))
                ->setRequired(true)
                ->setValue(isset($values['number']) ? $values['number'] : null)
                ->setLabel('project_admin_field_episode_number');

        # version
        $version = $this->createElement('text', 'version')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('Int', false, array(
                'messages' => array(
                        Zend_Validate_Int::NOT_INT => 'default_form_error_int'
                )
                ))
                ->setRequired(true)
                ->setValue(isset($values['version']) ? $values['version'] : '1')
                ->setLabel('project_admin_field_episode_version');

        # projects
        $table = Doctrine::getTable('Projects_Model_Project');
        $project = $this->createElement('select', 'project')
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setLabel('project_admin_field_episode_project')
                ->setValue(isset($values['project_id']) ? $values['project_id'] : null)
                ->setMultiOptions($table->getProjects());

        # add elements to the form
        $this->addElement($title)
                ->addElement($project)
                ->addElement($number)
                ->addElement($version)
                # edit button
                ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

}
