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

class News_AdminController extends FansubCMS_Controller_Action
{
    public function indexAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_index_headline');
        $table = Doctrine_Core::getTable('News_Model_News');

        $query = $table->createQuery('n');
        $query->select('n.*, n.id as comments, n.id as id, u.name as author')
            ->leftJoin('n.User_Model_User u')
            ->orderBy('n.publish_date DESC');

        $this->view->query = $query;
    }

    public function editAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_edit_headline');
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_News');
        $n = $table->findOneBy('id', $id ? $id : 0);
        if(!$n) {
            $this->session->message = $this->translate('news_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }
        $this->view->form = new News_Form_EditNews($n->toArray());

        if($this->_request->isPost()) { // there are profile updates
            if($this->view->form->isValid($this->_request->getPost())) {
                $values = $this->view->form->getValues();
                $n->title = $values['title'];
                $n->text = $values['text'];
                $public = $values['public'] == 'yes' ? 'yes' : 'no';
                $n->public = $public;

                if(!empty($values['public']) && $values['public'] == 'yes') {
                    if (empty($values['isoDate'])) {
                        $n->publish_date = date('Y-m-d H:i:s');
                    } else {
                        $n->publish_date = $values['isoDate'];
                    }
                } else if (!empty($values['public']) && $values['public'] == 'no') {
                    $n->publish_date = null;
                }

                $n->save();
                $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
            } else {
                $this->view->message = $this->translate('news_admin_edit_failed');
            }
        }
    }

    public function spamAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_spam_headline');
        $table = Doctrine::getTable('News_Model_Comment');
        $this->view->spam = $table->getSpamPaginator();
    }

    public function deleteAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_delete_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_News');
        if($id) {
            $news = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            if($this->request->getParam('yes') && $news) {
                $news->delete();
                $this->session->message = $this->translate('news_admin_delete_success');
                $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
            }
        } else {
            $this->session->message = $this->translate('news_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }
    }

    public function addAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_add_headline');
        $this->view->form = new News_Form_EditNews(null, true);

        if($this->_request->isPost()) { // there are profile updates
            if($this->view->form->isValid($this->_request->getPost())) {
                $values = $this->view->form->getValues();

                $n = new News_Model_News;
                $n->title = $values['title'];
                $n->text = $values['text'];
                $public = $values['public'] == 'yes' ? 'yes' : 'no';
                $n->public = $public;

                if(!empty($values['public']) && $values['public'] == 'yes') {
                    if (empty($values['isoDate'])) {
                        $n->publish_date = date('Y-m-d H:i:s');
                    } else {
                        $n->publish_date = $values['isoDate'];
                    }
                } else if (!empty($values['public']) && $values['public'] == 'no') {
                        $n->publish_date = null;
                }

                $n->user_id = Zend_Auth::getInstance()->getIdentity()->id;
                $n->save();

                $this->session->message = $this->translate('news_admin_add_success');
                $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
                $this->view->form = new News_Form_EditNews(null,true);
            } else {
                $this->view->message = $this->translate('news_admin_add_failed');
            }
        }
    }

    public function commentsAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_comments_headline');
        $this->view->role = $this->defaultUseRole;
        $id = $this->getRequest()->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_News');
        $n = $table->findOneBy('id', $id ? $id : 0);
        if(!$n) {
            $this->session->message = $this->translate('news_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }
        $this->view->comments = $n->getCommentPaginator(false);
        $page = $this->getRequest()->getParam('page');
        $this->view->comments->setItemCountPerPage(25);
        $this->view->comments->setCurrentPageNumber($page);
    }

    public function markhamAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_markham_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_Comment');
        if($id) {
            $comment = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            if($this->request->getParam('yes') && $comment) {
                $a = new FansubCMS_Validator_Akismet();
                $a = $a->getAkismet();
                $a->submitHam([
                  'user_ip'              => $comment->ip,
                  'comment_type'         => 'comment',
                  'comment_author'       => $comment->author,
                  'comment_author_email' => $comment->email,
                  'comment_author_url'   => $comment->url,
                  'comment_content'      => $comment->content,
                ]);

                $comment->spam = 'no';
                $comment->visible = 'yes';
                $comment->save();
                $this->session->message = $this->translate('news_admin_mark_ham_success');
                $this->_helper->redirector->gotoRoute(array('action'=>'spam','controller'=>'admin','module'=>'news'),'news');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoRoute(array('action'=>'spam','controller'=>'admin','module'=>'news'),'news');
            }
        } else {
            $this->session->message = $this->translate('news_comment_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'spam','controller'=>'admin','module'=>'news'),'news');
        }
    }

    public function markspamAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_markspam_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_Comment');
        if($id) {
            $comment = $table->find($id);
            $this->view->form = new FansubCMS_Form_Confirmation();
            if($this->request->getParam('yes') && $comment) {
                $a = new FansubCMS_Validator_Akismet();
                $a = $a->getAkismet();
                $a->submitSpam([
                  'user_ip'              => $comment->ip,
                  'comment_type'         => 'comment',
                  'comment_author'       => $comment->author,
                  'comment_author_email' => $comment->email,
                  'comment_author_url'   => $comment->url,
                  'comment_content'      => $comment->content,
                ]);
                $comment->spam = 'yes';
                $comment->visible = 'no';
                $comment->save();
                $this->session->message = $this->translate('news_admin_mark_spam_success');
                $this->_helper->redirector->gotoRoute(array('action'=>'comments','controller'=>'admin','module'=>'news','id'=>$comment->news_id),'news');
            } else if($this->request->getParam('no')) {
                $this->_helper->redirector->gotoRoute(array('action'=>'comments','controller'=>'admin','module'=>'news','id'=>$comment->news_id),'news');
            }
        } else {
            $this->session->message = $this->translate('news_comment_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }
    }

    public function deletecommentAction()
    {
        $this->view->pageTitle = $this->translate('news_admin_deletecomment_headline');
        $id = $this->request->getParam('id');
        $table = Doctrine_Core::getTable('News_Model_Comment');
        if($id) {
            $comment = $table->find($id);
            $spam = $comment->spam == 'yes' ? true : false;
            $this->view->form = new FansubCMS_Form_Confirmation();
            if($this->request->getParam('yes') && $comment) {
                $comment->delete();
                $this->session->message = $this->translate('news_admin_deletecomment_success');
                $spam ? $this->_helper->redirector->gotoRoute(array('action'=>'spam','controller'=>'admin','module'=>'news'),'news') : $this->_helper->redirector->gotoRoute(array('action'=>'comments','controller'=>'admin','module'=>'news', 'id' => $id),'news');
            } else if($this->request->getParam('no')) {
                $spam ? $this->_helper->redirector->gotoRoute(array('action'=>'spam','controller'=>'admin','module'=>'news'),'news') : $this->_helper->redirector->gotoRoute(array('action'=>'comments','controller'=>'admin','module'=>'news', 'id' => $id),'news');
            }
        } else {
            $this->session->message = $this->translate('news_comment_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }
    }

    public function previewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $n = News_Model_News::getNewsById($id);
        if(!$n) {
            $this->session->message = $this->translate('news_not_existent');
            $this->_helper->redirector->gotoRoute(array('action'=>'index','controller'=>'admin','module'=>'news'),'news');
        }

        $this->_helper->redirector->gotoRoute(array(
            'action' => 'index',
            'controller' => 'comment',
            'module' => 'news',
            'title' => $n->title_slug
        ), 'news_new_perma');
    }
}
