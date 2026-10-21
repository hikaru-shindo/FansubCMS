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

class Group_ContactController extends FansubCMS_Controller_Action {
    protected $mailsettings;

    public function indexAction() {
        $req = $this->getRequest();
        $this->view->form = new Group_Form_Contact('contact');
        if($req->isPost()) {
            if($this->view->form->isValid($_POST)) {
                // we need the email settings from the registry
                $this->mailsettings = Zend_Registry::get('emailSettings');
                $values = $this->view->form->getValues();
                if(!$this->mailsettings->sendmail) {
                    $mtconf = array('auth' => 'login',
                            'username' => $this->mailsettings->smtp->user,
                            'password' => $this->mailsettings->smtp->password,
                            'port' => $this->mailsettings->smtp->port);
                    if($this->mailsettings->smtp->ssl) $mtconf['ssl'] = 'tls';
                    $mtr = new Zend_Mail_Transport_Smtp($this->mailsettings->smtp->host,$mtconf);
                } else {
                    $mtr = new Zend_Mail_Sendmail();
                }
                $mailer = new Zend_Mail('UTF-8');
                $mailer->setFrom($values['email'],$values['author']);
                $mailer->addTo($this->mailsettings->email->admin,'FansubCMS Administration');
                $mailer->setMessageId();
                $mailer->setSubject('FansubCMS Contact');
                $mailer->addHeader('X-MailGenerator','ContactForm on FansubCMS');
                $mailer->addHeader('X-Mailer','FansubCMS');
                $mailer->addHeader('X-Priority','3');
                $message = $this->translate('contact_mail_text',array('name' => $values['author'], 'email' => $values['email'], 'text' => $values['content']));
                $mailer->setBodyText($message,'UTF-8');
                if($mailer->send($mtr)) {
                    $this->view->message = $this->translate('group_contact_mail_sent_successful');
                    $this->view->form = new Group_Form_Contact('contact');
                }
            }
        }
        $this->view->title = $this->translate('group_contact_title');
    }
}
