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

class News_IndexController extends FansubCMS_Controller_Action {
    public $conf;

    public function init() {
        parent::init();
        $this->conf = Zend_Registry::get('environmentSettings');
    }

    public function indexAction() {
        $newsTable = Doctrine::getTable('News_Model_News');
        $this->view->news = $newsTable->getPaginator(true);
        $page = $this->getRequest()->getParam('page');
        $this->view->news->setItemCountPerPage($this->conf->news->front);
        $this->view->news->setCurrentPageNumber($page);
        if($this->getRequest()->getParam('rss')) {
            $this->view->news->setItemCountPerPage(50);
            $this->view->news->setCurrentPageNumber(1);
            $this->_generateFeed($this->view->news);
        }
    }

    # helpers

    protected function _generateFeed($news) {
        $this->_helper->layout->disableLayout();
        $settings = Zend_Registry::get('environmentSettings');
        $mailSettings = Zend_Registry::get('emailSettings');

        foreach($news as $post) {
            $entries[]=array(
                    'title'=>$post->title,
                    'link'=>'http://'.$_SERVER['HTTP_HOST'].$this->view->baseUrl().'/news/'.$post->getUrlParams(),
                    'description'=>$post->text,
                    'content'=>$this->view->textile($post->text),
                    'lastUpdate'=>strtotime($post->updated_at),
            );
        }
        // generate and render RSS feed
        $feed=Zend_Feed::importArray(array(
                'title'   => $settings->page->group->name,
                'link'    => 'http://'.$_SERVER['HTTP_HOST'].$this->view->baseUrl().'/news/feed',
                'charset' => 'UTF-8',
                'entries' => $entries,
                'author'=>$settings->page->group->name,
                'email'=>$mailSettings->email->admin,
                ), 'atom');
        $feed->send();
        die;
    }
}

