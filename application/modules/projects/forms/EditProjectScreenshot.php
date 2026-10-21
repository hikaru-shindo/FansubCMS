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

class Projects_Form_EditProjectScreenshot extends Zend_Form {

    public function __construct(array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'addprojectscreenshot' : 'editprojectscreenshot')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'addprojectscreenshot' : 'editprojectscreenshot');

        # screen
        if (!is_dir(realpath(UPLOAD_PATH . '/screenshots/'))) {
            if (!mkdir(realpath(UPLOAD_PATH) . DIRECTORY_SEPARATOR . 'screenshots', 0777))
                throw new Zend_Exception('Could not create screenshot directory.');
        }
        $screen = $this->createElement('file', 'screen')
                        ->setRequired($insert ? true : false)
                        ->setDestination(realpath(UPLOAD_PATH . '/screenshots'))
                        //  ->addValidator('Count', false, 1)
                        ->addValidator('IsImage', false, array('jpeg,jpg,png,gif'))
                        ->setLabel('project_admin_field_screenshot_screen');

        # description
        $description = $this->createElement('textarea', 'description');
        $description->addFilter('stringTrim')
                    ->addFilter('stripTags')
                    ->setLabel('project_admin_field_screenshot_description')
                    ->setValue(isset($values['description']) ? $values['description'] : null);

        # projects
        $table = Doctrine::getTable('Projects_Model_Project');
        $project = $this->createElement('select', 'project')
                        ->setRequired(true)
                        ->addValidator('NotEmpty', true, array(
                            'messages' => array(
                                Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                                )))
                        ->setLabel('project_admin_field_screenshot_project')
                        ->setValue(isset($values['project_id']) ? $values['project_id'] : null)
                        ->setMultiOptions($table->getProjects());

        # add elements to the form
        $this->addElement($screen)
                ->addElement($project)
                ->addElement($description)
                # edit button
                ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

    public function getUniqueCode($length = "") {
        $code = md5(uniqid(rand(), true));
        if ($length != "")
            return substr($code, 0, $length);
        else
            return $code;
    }

    public function isValid($data) {
        if ($this->screen instanceof Zend_Form_Element_File) {
            $fileName = $this->screen->getFileName();
            if (!empty($fileName)) {
                $oldname = pathinfo($fileName);
                $newname = $this->getUniqueCode(32) . '.' . $oldname['extension'];
                $this->screen->addFilter('Rename', $newname);
            }
        }
        return parent::isValid($data);
    }

}
