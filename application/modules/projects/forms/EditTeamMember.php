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

class Projects_Form_EditTeamMember extends Zend_Form
{

    public function __construct(Projects_Model_Project $project, array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'addprojectteammember' : 'editprojectteammember')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'addprojectteammember' : 'editprojectteammember');

        $user = $this->_getUserElement($project);
        $function = $this->createElement('text', 'function')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setValue(!empty($values['function']) ? $values['function'] : null)
                ->setLabel('project_admin_field_team_function');

        # add elements to the form
        if($insert) {
            $this->addElement($user);
        }
        $this->addElement($function)
                # edit button
                ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

    protected function _getUserElement(Projects_Model_Project $project)
    {
        $q = Doctrine_Query::create()
                ->select('u.id, u.name')
                ->from('User_Model_User u')
                ->leftJoin('u.Projects_Model_User pu')
                ->where('pu.project_id <> ?', $project->id)
                ->orWhere('pu.project_id IS NULL')
                ->orderBy('u.name ASC');
        $users = $q->fetchArray();

        $selectArray = array('' => 'pleasechoose');

        foreach($users as $user) {
            $selectArray[$user['id']] = $user['name'];
        }

        $elem = $this->createElement('select', 'user');

        $elem->setLabel('project_admin_field_team_user')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setRequired(true)
                ->setMultiOptions($selectArray);

        return $elem;
    }
}
