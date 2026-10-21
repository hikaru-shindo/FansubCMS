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

class Projects_AdminController extends FansubCMS_Controller_Action
{
    public function indexAction()
    {
        $this->view->pageTitle = $this->translate('project_list_headline');
        $table = Doctrine_Core::getTable('Projects_Model_Project');

        $this->view->query = $table->createQuery();
    }

    public function addAction()
    {
        $this->view->pageTitle = $this->translate('project_add_headline');
        $this->view->form = new Projects_Form_EditProject(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p = new Projects_Model_Project;
                $p->updateProject($values);
                $this->session->message = $this->translate('project_admin_add_success');
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_add_failed');
            }
        }
    }

    public function deleteAction()
    {
        $this->view->pageTitle = $this->translate('project_delete_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = $this->translate('project_admin_delete_confirmation', array('name' => $p->name));
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_delete_success');
                $this->_helper->redirector->gotoSimple('index', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
    }

    public function editAction()
    {
        $this->view->pageTitle = $this->translate('project_edit_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $pArr = $p->toArray();
        $pArr['Projects_Model_Leader'] = $p->getLeaders();

        $this->view->form = new Projects_Form_EditProject($pArr);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p->updateProject($values);
                $this->session->message = $this->translate('project_admin_edit_success');
                $this->session->message_type = 'message';
                $this->_helper->redirector->gotoSimple('index','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_edit_failed');
                $this->session->message_type = 'type';
            }
        }
    }

    public function episodesAction()
    {
        $id = $this->request->getParam('id');

        if(!empty($id)) {
            $project = Doctrine_Query::create()->from('Projects_Model_Project p')->where('p.id = ?', $id)->fetchOne();

            $this->view->pageTitle = $this->translate('project_admin_episodes_headline',array('name' => $project->name));
            $this->view->all = false;
        } else {
            $this->view->pageTitle = $this->translate('project_admin_episodes_headline_2');
            $this->view->all = true;
        }

        $table = Doctrine_Core::getTable('Projects_Model_Episode');

        $query = $table->createQuery();
        $query->select('*, title as name, p.name as project')
        ->leftJoin('Projects_Model_Episode.Projects_Model_Project p')
        ->orderBy('p.name ASC, Projects_Model_Episode.number ASC');
        if (! empty($id)) {
            $query->where('Projects_Model_Episode.project_id = ?', $id);
        }

        $this->view->query = $query;
    }

    public function addepisodeAction()
    {
        $this->view->pageTitle = $this->translate('project_addepisode_headline');
        $this->view->form = new Projects_Form_EditProjectEpisode(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $p = new Projects_Model_Episode();
                $p->updateEpisode($values);
                $this->session->message = $this->translate('project_admin_addepisode_success');
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_addepisode_failed');
            }
        }
    }

    public function deleteepisodeAction()
    {
        $this->view->pageTitle = $this->translate('project_deleteepisode_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = $this->translate('project_admin_deleteepisode_confirmation', array('name' => $p->title));
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_deleteepisode_success');
                $this->_helper->redirector->gotoSimple('episodes', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_episode_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
    }

    public function editepisodeAction()
    {
        $this->view->pageTitle = $this->translate('project_editepisode_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_episode_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
        $this->view->form = new Projects_Form_EditProjectEpisode($p->toArray());
        $this->view->releaseQuery = Doctrine_Query::create()
                ->select('er.*, er.id as codec, er.updated_by as updater')
                ->from('Projects_Model_EpisodeRelease er')
                ->where('er.episode_id = ?', $p->id);

        $this->view->episodeId = $p->id;

        if($this->request->isPost()) { // there are profile updates
            if($this->view->form->isValid($this->request->getPost())) {
                $values = $this->view->form->getValues();
                $p->updateEpisode($values);
                $this->session->message = $this->translate('project_admin_editepisode_success');
                $this->_helper->redirector->gotoSimple('episodes','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_editepisode_failed');
            }
        }
    }

    public function addEpisodeReleaseAction()
    {
        $this->view->pageTitle = $this->translate('project_add-episode-release_headline');
        $this->view->form = new Projects_Form_EditProjectEpisodeRelease(array(), true);

        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Episode');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_episode_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }

        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $er = new Projects_Model_EpisodeRelease();
                $er->updateRelease($values);
                $p->link('Projects_Model_EpisodeRelease', array($er->id), true);
                $this->session->message = $this->translate('project_admin_add-episode-release_success');
                $this->_helper->redirector->gotoSimple('editepisode','admin','projects', array('id' => $id));
            } else {
                $this->view->message = $this->translate('project_admin_add-episode-release_failed');
            }
        }
    }

    public function deleteEpisodeReleaseAction()
    {
        $this->view->pageTitle = $this->translate('project_delete-episode-release_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_EpisodeRelease');
        if($id) {
            $p = $table->find($id);
            $epId = $p->episode_id;
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = $this->translate('project_admin_delete-episode-release_confirmation',array('crc' => $p->crc));
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_delete-episode-release_success');
                $this->_helper->redirector->gotoSimple('editepisode', 'admin', 'projects', array('id'=> $epId));
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('editepisode','admin','projects', array('id'=> $epId));
            }
        } else {
            $this->session->message = $this->translate('project_episode-release_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
    }

    public function editEpisodeReleaseAction()
    {
        $this->view->pageTitle = $this->translate('project_edit-episode-release_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_EpisodeRelease');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_episode-release_not_existent');
            $this->_helper->redirector->gotoSimple('episodes','admin','projects');
        }
        $epId = $p->episode_id;
        $this->view->form = new Projects_Form_EditProjectEpisodeRelease($p->toArray());
        $this->view->releaseQuery = Doctrine_Query::create()
                ->select('er.*, er.id as codec, er.updated_by as updater')
                ->from('Projects_Model_EpisodeRelease er')
                ->where('er.episode_id = ?', $p->id);

        if($this->request->isPost()) { // there are profile updates
            if($this->view->form->isValid($this->request->getPost())) {
                $values = $this->view->form->getValues();
                $p->updateRelease($values);
                $this->session->message = $this->translate('project_admin_edit-episode-release_success');
                $this->_helper->redirector->gotoSimple('editepisode','admin','projects', array('id'=>$epId));
            } else {
                $this->view->message = $this->translate('project_admin_editepisode_failed');
            }
        }
    }

    public function screenshotsAction()
    {
        $this->view->pageTitle = $this->translate('project_screenshots_headline');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        $id = $this->request->getParam('id', false);

        $query = $table->createQuery()
                ->select('id, description, screenshot as screen, p.name as project')
                ->leftJoin('Projects_Model_Screenshot.Projects_Model_Project p')
                ->orderBy('p.name ASC, id ASC');

        if($id) {
            $query->where('p.id = ?', $id);
        }

        $this->view->query = $query;
    }

    public function addscreenshotAction()
    {
        $this->view->pageTitle = $this->translate('project_addscreenshot_headline');
        $this->view->form = new Projects_Form_EditProjectScreenshot(array(), true);
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($req->getPost())) {
                $values = $this->view->form->getValues();
                $values['file'] = $this->view->form->getElement('screen');
                $p = new Projects_Model_Screenshot();
                $p->updateScreenshot($values);
                $this->session->message = $this->translate('project_admin_addscreenshot_success');
                $this->session->message_type = 'message';
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_addscreenshot_failed');
                $this->view->message_type = 'error';
            }
        }
    }

    public function deletescreenshotAction()
    {
        $this->view->pageTitle = $this->translate('project_deletescreenshot_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        if($id) {
            $p = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            $this->view->confirmation = $this->translate('project_admin_deletescreenshot_confirmation', array('project' => $p->Projects_Model_Project->name, 'screen' => $p->screenshot));
            if($this->request->getParam('yes') && $p) {
                $p->delete();
                $this->session->message = $this->translate('project_admin_deletescreenshot_success');
                $this->_helper->redirector->gotoSimple('screenshots', 'admin', 'projects');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            }
        } else {
            $this->session->message = $this->translate('project_screenshot_not_existent');
            $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
        }
    }

    public function editscreenshotAction()
    {
        $this->view->pageTitle = $this->translate('project_editscreenshot_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Screenshot');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_screenshot_not_existent');
            $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
        }
        $this->view->form = new Projects_Form_EditProjectScreenshot($p->toArray());
        $req = $this->getRequest();
        if($req->isPost()) { // there are profile updates
            if($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                $values['file'] = $this->view->form->getElement('screen');
                $p->updateScreenshot($values);
                $this->session->message = $this->translate('project_admin_editscreenshot_success');
                $this->_helper->redirector->gotoSimple('screenshots','admin','projects');
            } else {
                $this->view->message = $this->translate('project_admin_editscreenshot_failed');
            }
        }
    }

    public function teamAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }

        $this->checkAllowed($p, 'team');

        $this->view->pageTitle = $this->translate('project_team_headline', array('name' => $p->name));

        $leaders = array();
        foreach($p->Projects_Model_Leader as $leader) {
            $leaders[] = $leader->User_Model_User;
        }

        $this->view->leaders = $leaders;
        $this->view->team = $p->Projects_Model_User;

        $this->view->project = $p;
    }

    public function addteammemberAction()
    {
        $id = $this->getRequest()->getParam('pid');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('team','admin','projects');
        }

        $this->checkAllowed($p, 'addteammember');
        $this->view->pageTitle = $this->translate('project_addteammember_headline', array('name' => $p->name));

        $form = new Projects_Form_EditTeamMember($p, array(), true);

        if($this->request->isPost()) {
            if($form->isValid($_POST)) {
                $values = $form->getValues();

                $pu = new Projects_Model_User();
                $pu->project_id = $p->id;
                $pu->user_id = $values['user'];
                $pu->function = $values['function'];
                $pu->save();

                $this->session->message = $this->translate('project_admin_addteammember_success');
                $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $id));
            } else {
                $this->view->message = $this->translate('project_admin_addteammember_failed');
            }
        }

        $this->view->form = $form;
    }

    public function editteammemberAction()
    {
        $pid = $this->getRequest()->getParam('pid', 0);
        $uid = $this->request->getParam('uid', 0);
        $table = Doctrine_Core::getTable('Projects_Model_User');
        $pu = $table->createQuery('pu')
                ->leftJoin('pu.Projects_Model_Project p')
                ->leftJoin('pu.User_Model_User u')
                ->where('pu.project_id = ?', $pid)
                ->andWhere('pu.user_id = ?', $uid)
                ->fetchOne();
        if(!$pu) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('team','admin','projects');
        }
        $this->checkAllowed($pu->Projects_Model_Project, 'editteammember');
        $this->view->pageTitle = $this->translate('project_editteammember_headline', array('name' => $pu->Projects_Model_Project->name));

        $this->view->user = $pu->User_Model_User;

        $form = new Projects_Form_EditTeamMember($pu->Projects_Model_Project, $pu->toArray(), false);

        if($this->request->isPost()) {
            if($form->isValid($_POST)) {
                $values = $form->getValues();

                $pu->function = $values['function'];
                $pu->save();

                $this->session->message = $this->translate('project_admin_editteammember_success');
                $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $pid));
            } else {
                $this->view->message = $this->translate('project_admin_editteammember_failed');
            }
        }

        $this->view->form = $form;
    }

    public function deleteteammemberAction()
    {

        $pid = $this->getRequest()->getParam('pid', 0);
        $uid = $this->request->getParam('uid', 0);
        $table = Doctrine_Core::getTable('Projects_Model_User');
        $pu = $table->createQuery('pu')
                ->leftJoin('pu.Projects_Model_Project p')
                ->leftJoin('pu.User_Model_User u')
                ->where('pu.project_id = ?', $pid)
                ->andWhere('pu.user_id = ?', $uid)
                ->fetchOne();
        if(!$pu) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($pu->Projects_Model_Project, 'deleteteammember');

        $this->view->pageTitle = $this->translate('project_deleteteammember_headline', array('name' => $pu->Projects_Model_Project->name));

        $this->view->form = new FansubCMS_Form_Confirmation();
        $this->view->confirmation = $this->translate('project_admin_deleteteammember_confirmation', array('projectname' => $pu->Projects_Model_Project->name, 'username' => $pu->User_Model_User->name));
        if($this->request->getParam('yes') && $pu) {
            $pu->delete();
            $this->session->message = $this->translate('project_admin_deleteteammember_success');
            $this->_helper->redirector->gotoSimple('team', 'admin', 'projects', array('id' => $pid));
        } else if($this->request->getParam('no')) {
             $this->_helper->redirector->gotoSimple('team','admin','projects', array('id' => $pid));
        }
    }

    public function taskTypeAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }

        $this->checkAllowed($p, 'task-type');

        $this->view->pageTitle = $this->translate('project_task-type_headline', array('name' => $p->name));

        $query = Doctrine_Query::create();
        $query->select('ptt.id, ptt.title, ptt.description')
              ->from('Projects_Model_TaskType ptt')
              ->where('ptt.project_id = ?', $id);

        $this->view->query = $query;
        $this->view->project = $p;
    }

    public function addTaskTypeAction()
    {
        $id = $this->getRequest()->getParam('pid');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }

        $this->checkAllowed($p, 'add-task-type');
        $this->view->pageTitle = $this->translate('project_add-task-type_headline',array('name' => $p->name));

        $form = new Projects_Form_EditTaskType(array(), true);

        if($this->request->isPost()) {
            if($form->isValid($this->request->getPost())) {
                $values = $form->getValues();

                $ptt = new Projects_Model_TaskType();
                $ptt->project_id = $p->id;
                $ptt->title = $values['title'];
                $ptt->description = $values['description'];
                $ptt->save();

                $this->session->message = $this->translate('project_admin_add-task-type_success');
                $this->_helper->redirector->gotoSimple('task-type','admin','projects', array('id' => $id));
            } else {
                $this->view->message = $this->translate('project_admin_add-task-type_failed');
            }
        }

        $this->view->form = $form;
    }

    public function editTaskTypeAction()
    {
        $id = $this->request->getParam('id', 0);
        $table = Doctrine_Core::getTable('Projects_Model_TaskType');
        $ptt = $table->createQuery('ptt')
                ->leftJoin('ptt.Projects_Model_Project p')
                ->where('ptt.id = ?', $id)
                ->fetchOne();
        if(!$ptt) {
            $this->session->message = $this->translate('project_task_type_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($ptt->Projects_Model_Project, 'edit-task-type');
        $this->view->pageTitle = $this->translate('project_edit-task-type_headline', array('name' => $ptt->Projects_Model_Project->name));

        $form = new Projects_Form_EditTaskType($ptt->toArray(), false);

        if($this->request->isPost()) {
            if($form->isValid($this->request->getPost())) {
                $values = $form->getValues();

                $ptt->title = $values['title'];
                $ptt->description = $values['description'];
                $ptt->save();

                $this->session->message = $this->translate('project_admin_edit-task-type_success');
                $this->_helper->redirector->gotoSimple('task-type','admin','projects', array('id' => $ptt->project_id));
            } else {
                $this->view->message = $this->translate('project_admin_edit-task-type_failed');
            }
        }

        $this->view->form = $form;
    }

    public function deleteTaskTypeAction()
    {
        $id = $this->request->getParam('id', 0);
        $table = Doctrine_Core::getTable('Projects_Model_TaskType');
        $ptt = $table->createQuery('ptt')
                ->leftJoin('ptt.Projects_Model_Project p')
                ->where('ptt.id = ?', $id)
                ->fetchOne();
        if(!$ptt) {
            $this->session->message = $this->translate('project_task_type_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($ptt->Projects_Model_Project, 'delete-task-type');

        $this->view->pageTitle = $this->translate('project_delete-task-type_headline', array('name' => $ptt->Projects_Model_Project->name));

        $this->view->form = new FansubCMS_Form_Confirmation();
        $this->view->confirmation = $this->translate('project_admin_delete-task-type_confirmation', array('name' => $ptt->title));
        if($this->request->getParam('yes') && $ptt) {
            $ptt->delete();
            $this->session->message = $this->translate('project_admin_delete-task-type_success');
            $this->_helper->redirector->gotoSimple('task-type', 'admin', 'projects', array('id' => $ptt->project_id));
        } else if($this->request->getParam('no')) {
             $this->_helper->redirector->gotoSimple('task-type','admin','projects', array('id' => $ptt->project_id));
        }
    }

    public function tasksAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }

        $this->checkAllowed($p, 'tasks');

        $this->view->pageTitle = $this->translate('project_tasks_headline', array('name' => $p->name));

        $select = 'pt.id, ptt.title as type_title, pt.done, pt.comment, u.name as username, r.number as number, r.version as version';

        $query = Doctrine_Query::create();
        $query->select($select)
              ->from('Projects_Model_Task pt')
              ->leftJoin('pt.Projects_Model_TaskType ptt')
              ->leftJoin('pt.User_Model_User u');

        if($p->project_type == Projects_Model_Project::TYPE_SCANLATION) {
            $query->leftJoin('pt.Projects_Model_Chapter r');
        } else {
            $query->leftJoin('pt.Projects_Model_Episode r');
        }
        $query->where('ptt.project_id = ?', $id)
              ->orderBy('pt.done ASC');

        $this->view->query = $query;
        $this->view->project = $p;
    }

    public function addTaskAction()
    {
        $id = $this->getRequest()->getParam('pid');
        $table = Doctrine_Core::getTable('Projects_Model_Project');
        $p = $table->findOneBy('id', $id ? $id : 0);
        if(!$p) {
            $this->session->message = $this->translate('project_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }

        $this->checkAllowed($p, 'add-task');
        $this->view->pageTitle = $this->translate('project_add-task_headline', array('name' => $p->name));

        $form = new Projects_Form_EditTask($p, array(), true);

        if($this->request->isPost()) {
            if($form->isValid($this->request->getPost())) {
                $values = $form->getValues();

                $pt = new Projects_Model_Task();

                $pt->user_id = $values['user'];
                $pt->task_id = $values['type'];
                $pt->done = $values['done'];
                $pt->comment = $values['comment'];
                if($p->project_type == Projects_Model_Project::TYPE_SCANLATION) {
                    $pt->chapter_id = $values['release'];
                } else {
                    $pt->episode_id = $values['release'];
                }

                $pt->save();

                $this->session->message = $this->translate('project_admin_add-task_success');
                $this->_helper->redirector->gotoSimple('tasks','admin','projects', array('id' => $id));
            } else {
                $this->view->message = $this->translate('project_admin_add-task_failed');
            }
        }

        $this->view->form = $form;
    }

    public function editTaskAction()
    {
        $id = $this->request->getParam('id', 0);
        $table = Doctrine_Core::getTable('Projects_Model_Task');
        $pt = $table->createQuery('pt')
                ->leftJoin('pt.Projects_Model_TaskType ptt')
                ->leftJoin('ptt.Projects_Model_Project p')
                ->where('pt.id = ?', $id)
                ->fetchOne();
        if(!$pt) {
            $this->session->message = $this->translate('project_task_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($pt->Projects_Model_TaskType->Projects_Model_Project, 'edit-task');
        $this->checkAllowed($pt, 'edit');
        $this->view->pageTitle = $this->translate('project_edit-task_headline', array('name' => $pt->Projects_Model_TaskType->Projects_Model_Project->name));

        $form = new Projects_Form_EditTask($pt->Projects_Model_TaskType->Projects_Model_Project, $pt->toArray(), false);

        if($this->request->isPost()) {
            if($form->isValid($this->request->getPost())) {
                $values = $form->getValues();

                $pt->task_id = $values['type'];
                $pt->user_id = $values['user'];
                $pt->done = $values['done'];
                $pt->comment = $values['comment'];

                switch($pt->Projects_Model_TaskType->Projects_Model_Project) {
                    case Projects_Model_Project::TYPE_SCANLATION:
                        $pt->chapter_id = $values['release'];
                        break;
                    case Projects_Model_Project::TYPE_SCANLATION:
                    default:
                        $pt->episode_id = $values['release'];
                }

                $pt->save();

                $this->session->message = $this->translate('project_admin_edit-task_success');
                $this->_helper->redirector->gotoSimple('tasks','admin','projects', array('id' => $pt->Projects_Model_TaskType->project_id));
            } else {
                $this->view->message = $this->translate('project_admin_edit-task_failed');
            }
        }

        $this->view->form = $form;
    }

    public function deleteTaskAction()
    {
        $id = $this->request->getParam('id', 0);
        $table = Doctrine_Core::getTable('Projects_Model_Task');
        $table = Doctrine_Core::getTable('Projects_Model_Task');
        $pt = $table->createQuery('pt')
                ->leftJoin('pt.Projects_Model_TaskType ptt')
                ->leftJoin('ptt.Projects_Model_Project p')
                ->where('pt.id = ?', $id)
                ->fetchOne();
        if(!$pt) {
            $this->session->message = $this->translate('project_task_type_not_existent');
            $this->_helper->redirector->gotoSimple('index','admin','projects');
        }
        $this->checkAllowed($pt->Projects_Model_TaskType->Projects_Model_Project, 'delete-task');

        $this->view->pageTitle = $this->translate('project_delete-task_headline', array('name' => $pt->Projects_Model_TaskType->Projects_Model_Project->name));

        $this->view->form = new FansubCMS_Form_Confirmation();
        $this->view->confirmation = $this->translate('project_admin_delete-task_confirmation', array('task' => $pt->Projects_Model_TaskType->title, 'username' => $pt->User_Model_User->name));
        if($this->request->getParam('yes') && $pt) {
            $pt->delete();
            $this->session->message = $this->translate('project_admin_delete-task_success');
            $this->_helper->redirector->gotoSimple('tasks', 'admin', 'projects', array('id' => $pt->Projects_Model_TaskType->project_id));
        } else if($this->request->getParam('no')) {
             $this->_helper->redirector->gotoSimple('tasks','admin','projects', array('id' => $pt->Projects_Model_TaskType->project_id));
        }
    }

    public function myTasksAction()
    {
        $this->view->pageTitle = $this->translate('project_my-tasks_headline');
        $identity = Zend_Auth::getInstance()->getIdentity();

        $q = Doctrine_Query::create()
                ->select('id,
                    comment,
                    done,
                    updated_at,
                    created_at,
                    p.name as project,
                    p.name_jp as project_jp,
                    e.number as number,
                    e.version as version,
                    tt.title as task')
                ->from('Projects_Model_Task t')
                ->leftJoin('t.Projects_Model_TaskType tt')
                ->leftJoin('tt.Projects_Model_Project p')
                ->leftJoin('t.Projects_Model_Chapter c')
                ->leftJoin('c.Projects_Model_ChapterRelease cr')
                ->leftJoin('t.Projects_Model_Episode e')
                ->leftJoin('e.Projects_Model_EpisodeRelease er')
                ->where('t.user_id = ?', $identity->id)
                ->andWhere('cr.released_at IS NULL')
                ->andWhere('er.released_at IS NULL')
                ->orderBy('p.name, c.number ASC, e.number ASC');

        $this->view->query = $q;
    }
}
