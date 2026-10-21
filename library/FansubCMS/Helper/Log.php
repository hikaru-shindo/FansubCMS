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

/**
 *
 * Helps with the application logging
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage Helper
 */
class FansubCMS_Helper_Log
{
    /**
     * @var FansubCMS_Helper_Log
     */
    protected static $_instance;

    /**
     * @var Zend_Log
     */
    protected static $_logger;

    /**
     *
     * constructs the helper
     */
    protected function __construct ()
    {
        if(APPLICATION_ENV != 'production') {
            $writer = new Zend_Log_Writer_Firebug();
            $writer->setDefaultPriorityStyle('EXCEPTION');
        } else {
            $mailConfig = Zend_Registry::get('emailSettings');

            if($mtconf->sendmail) {
                $mtconf = array(
                'auth' => 'login',
                'username' => $mailConfig->smtp->user,
                'password' => $mailConfig->smtp->password,
                'port' => $mailConfig->smtp->port,
                );


                if($mailConfig->smtp->ssl) {
                    $mtconf['ssl'] = 'tls';
                }

                $transport = new Zend_Mail_Transport_Smtp($mailConfig->smtp->host, $mtconf);
            } else {
                $transport = new Zend_Mail_Transport_Sendmail();
            }

            Zend_Mail::setDefaultTransport($transport);

            $mail = new Zend_Mail('UTF-8');
            $mail->setFrom($mailConfig->email->noreply, 'FansubCMS Error Report');
            $mail->addTo($mailConfig->email->admin, 'FansubCMS Technical Administrator at' . $_SERVER['HTTP_HOST']);
            $mail->addHeader('X-MailGenerator','Log handler on FansubCMS');
            $mail->addHeader('X-Mailer','FansubCMS');
            $mail->addHeader('X-Priority','3');

            $writer = new Zend_Log_Writer_Mail($mail);
            $writer->setSubjectPrependText('FansubCMS Error: ');
        }

        switch(APPLICATION_ENV) {
            case 'debug': // is handled by dev bar through
                $writer->addFilter(Zend_Log::DEBUG);
                break;
            case 'testing':
                $writer->addFilter(Zend_Log::NOTICE);
                break;
            default:
                $writer->addFilter(Zend_Log::ERR);
                break;
        }

        $logger = new Zend_Log($writer);

        self::$_logger = $logger;

    }

    /**
     * Get an instance of the helper
     * @return FansubCMS_Helper_Log
     */
    public static function getInstance ()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     *
     * Logs the $message
     * @param string $message
     * @param integer $priority
     * @see Zend_Log
     * @return void
     */
    public function log($message, $priority)
    {
        self::$_logger->log($message, $priority);
    }
}
