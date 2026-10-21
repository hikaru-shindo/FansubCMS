<?php
/*
 * This file is part of FansubCMS.
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
class Gadgets_ProjectController extends FansubCMS_Controller_Action
{
    /**
     * Show a number of random projects
     */
    public function randomAction ()
    {
        $num = $this->getRequest()->getParam('num', 5);
        $pt = Doctrine::getTable('Projects_Model_Project');
        $projects = $pt->createQuery()
            ->where('private = ?', 'no')
            ->orderBy('RANDOM()')
            ->limit($num)
            ->execute();
        $this->view->random = $projects;
    }

    /**
     * Show a number of latest releases
     */
    public function latestAction ()
    {
        $num = $this->getRequest()->getParam('num', 5);
        $pet = Doctrine::getTable('Projects_Model_EpisodeRelease');
        $q = $pet->createQuery('er');
        $q->leftJoin('er.Projects_Model_Episode e')
          ->leftJoin('e.Projects_Model_Project p')
          ->select('er.*, e.title as title, e.number as number, e.version as version, p.name as project, p.name_slug as project_slug, p.mini_poster as poster')
          ->offset(0)
          ->limit($num)
          ->orderBy('er.released_at DESC')
          ->where('er.released_at IS NOT NULL');
        $this->view->latest = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * Show current project status
     */
    public function statusAction()
    {
        $num = $this->getRequest()->getParam('num', 5);
        $q = Doctrine_Query::create();
        $q->from('Projects_Model_Task pt')
          ->select('p.name, p.name_slug, p.name_jp, p.project_type, ptt.title,
          	pt.done, pc.number, pc.version, pc.project_id, pe.number, pe.title,
          	pe.version, pe.project_id')
          ->leftJoin('pt.Projects_Model_TaskType ptt')
          ->leftJoin('ptt.Projects_Model_Project p')
          ->leftJoin('pt.Projects_Model_Episode pe')
          ->leftJoin('pt.Projects_Model_Chapter pc')
          ->where('p.private = ?', 'no')
          ->limit($num)
          ->orderBy('pt.updated_at DESC')
          ->groupBy('pe.number, pe.version, pc.number, pc.version, p.name, p.name_slug,
          	p.name_jp, p.project_type, ptt.title, pt.done, pc.number, pc.version,
          	pc.project_id, pe.number, pe.title, pe.version, pe.project_id, p.id, pe.id,
          	pc.id, ptt.id, pt.id, pt.updated_at');

        $this->view->result = $q->fetchArray();
    }
}
