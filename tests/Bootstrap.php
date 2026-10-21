<?php
/**
 *
 * This is a helper class that does the bootstrap process for unit testing
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 *
 */
class UnitBootstrapHelper
{
    public static function bootstrap()
    {
        // Define path to application directory
        defined('APPLICATION_PATH')
                || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

        // Define application environment
        defined('APPLICATION_ENV')
                || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

        !defined('UPLOAD_PATH') ? define('UPLOAD_PATH', realpath(dirname(__FILE__).'/../public/upload')) : null;

        defined('HTTP_PATH')
                || define('HTTP_PATH', realpath(dirname(__FILE__)).'/../public');

        require_once 'Zend/Application.php';
        $application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        $application->bootstrap();
    }
}
