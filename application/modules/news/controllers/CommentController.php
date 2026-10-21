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

class News_CommentController extends FansubCMS_Controller_Action {

    public $conf;

    public function init() {
        parent::init();
        $this->conf = Zend_Registry::get('environmentSettings');
    }

    public function indexAction() {
        $req = $this->getRequest();
        $id = $req->getParam('id');
        $title = $req->getParam('title');
        $this->view->news = false;
        $this->view->paginator = false;
        $this->view->writeForm = new News_Form_Comment('#');
        if ($id) {
            $this->view->news = News_Model_News::getNewsById($id);
        } else if($title) {
            $this->view->news = News_Model_News::getNewsBySlug(urldecode($title));
        }

        if($this->view->news) {
            $paginator = $this->view->news->getCommentPaginator(false, true);
            $page = $req->getParam('page');
            $paginator->setItemCountPerPage($this->conf->news->comments->numpage);
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;
        }

        if ($req->isPost() && $this->view->news) {
            if ($this->view->writeForm->isValid($_POST)) {
                $values = $this->view->writeForm->getValues();
                $nc = new News_Model_Comment();
                $nc->ip = getenv('REMOTE_ADDR');
                $nc->email = User_Model_User::isLoggedIn() ? Zend_Auth::getInstance()->getIdentity()->email : $values['email'];
                $nc->news_id = $this->view->news->id;
                $nc->visible = 'yes'; // there is no moderation yet
                $nc->author = User_Model_User::isLoggedIn() ? Zend_Auth::getInstance()->getIdentity()->name : $values['author'];
                $nc->url = $values['url'];
                $nc->comment = $values['comment'];
                $nc->checkSpam();
                $nc->save();
                $this->view->writeForm = new News_Form_Comment('#'); // clear form because comment is submitted
            }
        }
    }

}
