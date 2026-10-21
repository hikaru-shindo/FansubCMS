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

class Projects_IndexController extends FansubCMS_Controller_Action {

    public function init()
    {
        parent::init();
        $projectsTable = Doctrine::getTable('Projects_Model_Project');
        $this->view->quickSelect = $projectsTable->getArrayListing();
    }

    public function indexAction()
    {
        $this->_addDelegateType('Sorting');
        $projectsTable = Doctrine::getTable('Projects_Model_Project');
        $projects = $projectsTable->getFrontListing();
        $this->view->projects = $this->invokeDelegate('Sorting', 'sortProjects', array($projects));
    }

    public function detailsAction()
    {
        $name = urldecode($this->getRequest()->getParam('project'));
        $project = Doctrine::getTable('Projects_Model_Project');
        $this->view->project = $project->findOneBy('name_slug', $name);
    }

    public function feedAction()
    {
        $num = $this->request->getParam('entries', 25);
        if(!is_numeric($num)) {
            $num = 25;
        }
        if($num > 100) {
            $num = 100; // prevent from full listings which could take like forever to parse
        }
        $num = round($num, 0); // prevent from floting point values

        $q = Doctrine_Query::create();
        $q->from('Projects_Model_EpisodeRelease per')
          ->leftJoin('per.Projects_Model_Episode pe')
          ->leftJoin('pe.Projects_Model_Project p')
          ->where('per.released_at IS NOT NULL')
          ->andWhere('p.private = ?', 'no')
          ->orderBy('per.released_at DESC')
          ->limit($num);

        $this->_generateFeed($q->execute(array(), Doctrine::HYDRATE_RECORD));
    }

    # helpers

    /**
     *
     * Generates a feed of newest released episodes
     * @param Doctrine_Collection $episodes
     */
    protected function _generateFeed($episodes) {
        $this->_helper->layout->disableLayout();
        $settings = Zend_Registry::get('environmentSettings');
        $mailSettings = Zend_Registry::get('emailSettings');

        foreach($episodes as $episode) {
            # build the ep title to be shown
            $epTitle = $episode->Projects_Model_Episode->Projects_Model_Project->name . ' ' . $episode->Projects_Model_Episode->number;
            if($episode->Projects_Model_Episode->version > 1) {
                $epTitle .= 'v' . $episode->Projects_Model_Episode->version;
            }

            # url encode project title
            $projUrl = $episode->Projects_Model_Episode->Projects_Model_Project->name_slug;

            # add entry
            $entries[]=array(
                    'title'=>$epTitle,
                    'link'=>'http://'.$_SERVER['HTTP_HOST'].$this->view->baseUrl().'/projects/index/details/project/' . $projUrl,
                    'description'=>$episode->Projects_Model_Episode->title,
                    'content'=>$episode->Projects_Model_Episode->title,
                    'lastUpdate'=>strtotime($episode->released_at),
            );
        }
        // generate and render RSS feed
        $feed=Zend_Feed::importArray(array(
                'title'   => $settings->page->group->name,
                'link'    => 'http://' . $_SERVER['HTTP_HOST'] . $this->view->baseUrl() . '/projects/feed',
                'charset' => 'UTF-8',
                'entries' => $entries,
                'author'=>$settings->page->group->name,
                'email'=>$mailSettings->email->admin,
                ), 'atom');
        $feed->send();
        die;
    }
}
