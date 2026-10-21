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

class Projects_Form_EditTask extends Zend_Form
{

    public function __construct(Projects_Model_Project $project, array $values = array(), $insert = false, $options = array()) {
        parent::__construct($options);

        $this->setName($insert ? 'add-task' : 'edit-task')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'add-task' : 'edit-task');

        # type
        $type = $this->_getTaskTypeElement($project);
        $type->setValue(!empty($values['task_id']) ? $values['task_id']: null);

        # user
        $user = $this->_getUserElement($project);
        $user->setValue(!empty($values['user_id']) ? $values['user_id']: null);

        # release
        $release = $this->_getReleaseElement($project);

        $val = $project->project_type == Projects_Model_Project::TYPE_SCANLATION ? $values['chapter_id'] : null;
        $val = !$insert && empty($val) ? $values['episode_id'] : $val;

        $release->setValue($val);

        # done
        $done = $this->createElement('text', 'done')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Int', true, array(
                    'meesages' => array(
                        Zend_Validate_Int::NOT_INT => 'default_form_error_int'
                    )
                ))
                ->addValidator('Between', true, array(
                    'min' => 0,
                    'max' => 100,
                    'meesages' => array(
                        Zend_Validate_Between::NOT_BETWEEN => 'default_form_error_between',
                        Zend_Validate_Between::NOT_BETWEEN_STRICT => 'default_form_error_between'
                    )
                ))
                ->setValue(isset($values['done']) ? $values['done'] : null)
                ->setLabel('project_admin_field_task_done');

        # comment
        $comment = $this->createElement('textarea', 'comment');
        $comment->setValue(isset($values['comment']) ? $values['comment'] : null)
                ->addFilter('StringTrim')
                ->setAttrib('cols', 40)
                ->setAttrib('rows', 15)
                ->setLabel('comment');

        $this->addElement($type)
        ->addElement($user)
        ->addElement($release)
        ->addElement($done)
        ->addElement($comment)
        # edit button
        ->addElement('submit', $insert ? 'add' : 'update', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

    protected function _getTaskTypeElement(Projects_Model_Project $project)
    {

        $q = Doctrine_Query::create();
        $q->select('ptt.id, ptt.title')
           ->from('Projects_Model_TaskType ptt')
           ->where('ptt.project_id = ?', $project->id);

        $types = $q->fetchArray();

        $selectArray = array('' => 'pleasechoose');

        foreach($types as $type) {
            $selectArray[$type['id']] = $type['title'];
        }

        $elem = $this->createElement('select', 'type');

        $elem->setLabel('project_admin_field_task_type')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setRequired(true)
                ->setMultiOptions($selectArray);

        return $elem;
    }

    protected function _getReleaseElement(Projects_Model_Project $project)
    {
        if($project->project_type == Projects_Model_Project::TYPE_SCANLATION) {
            $table = 'Projects_Model_Chapter';
            $join = 'Projects_Model_ChapterRelease';
            $label = 'project_admin_field_task_chapter';
        } else {
            $table = 'Projects_Model_Episode';
            $join = 'Projects_Model_EpisodeRelease';
            $label = 'project_admin_field_task_episode';
        }

        $q = Doctrine_Query::create();
        $q->select('r.id, r.number, r.version')
           ->from($table . ' r')
           ->leftJoin('r.' . $join . ' rel')
           ->where('r.project_id = ?', $project->id)
           ->andWhere('rel.released_at IS NULL');

        $releases = $q->fetchArray();

        $selectArray = array('' => 'pleasechoose');

        foreach($releases as $release) {
            $selectArray[$release['id']] = $release['number'];

            if($release['version'] > 1) {
                $selectArray[$release['id']] .= 'v'.$release['version'];
            }
        }

        $elem = $this->createElement('select', 'release');

        $elem->setLabel($label)
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setRequired(true)
                ->setMultiOptions($selectArray);

        return $elem;
    }

    protected function _getUserElement(Projects_Model_Project $project)
    {
        $q = Doctrine_Query::create()
                ->distinct(true)
                ->select('u.id, u.name')
                ->from('User_Model_User u')
                ->leftJoin('u.Projects_Model_User pu')
                ->leftJoin('u.Projects_Model_Leader pl')
                ->where('pu.project_id = ?', $project->id)
                ->orWhere('pl.project_id = ?', $project->id)
                ->orderBy('u.name ASC');
        $users = $q->fetchArray();

        $selectArray = array('' => 'pleasechoose');

        foreach($users as $user) {
            $selectArray[$user['id']] = $user['name'];
        }

        $elem = $this->createElement('select', 'user');

        $elem->setLabel('project_admin_field_task_user')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setRequired(true)
                ->setMultiOptions($selectArray);

        return $elem;
    }
}
