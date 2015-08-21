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

ini_set("short_open_tag", true);

// Require composer autoload
require_once('vendor/autoload.php');

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

define('UPLOAD_PATH', realpath(dirname(__FILE__).'/upload'));

defined('HTTP_PATH')
        || define('HTTP_PATH', realpath(dirname(__FILE__)));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
)));
/** Zend_Application */
require_once 'Zend/Application.php';  

// Create application, bootstrap, and run
$application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
);
try {
    $application->bootstrap()
            ->run();
} catch(Zend_Config_Exception $e) {
	if(!headers_sent()) {
        header('Content-Type: text/plain');
    }

    if(APPLICATION_ENV == 'production') {
        echo "The page is misconfigured or configuration files are missing. Please contact an administratior.";
    } else {
        echo "The page is misconfigured or configuration files are missing. Please contact an administratior.\n\n";
        echo "Error information:\n";
        echo $e->getMessage() . "(Code " . $e->getCode() . ")\n";
        echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n\n";
        echo "Trace:\n";
        echo $e->getTraceAsString();
        echo "\n\n";
        echo "Extended Trace:\n";
        print_r($e->getTrace());
    }
} catch (Doctrine_Exception $e) {
	if(!headers_sent()) {
        header('Content-Type: text/plain');
    }

    if(APPLICATION_ENV == 'production') {
        echo "This page is encountering database issues. If you are the administrator please check your settings.";
    } else {
        echo "This page is encountering database issues. If you are the administrator please check your settings.\n\n";
        echo "Error information:\n";
        echo $e->getMessage() . "(Code " . $e->getCode() . ")\n";
        echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n\n";
        echo "Trace:\n";
        echo $e->getTraceAsString();
        echo "\n\n";
        echo "Extended Trace:\n";
        print_r($e->getTrace());
    }
} catch(Exception $e) {
    if(!headers_sent()) {
        header('Content-Type: text/plain');
    }

    if(APPLICATION_ENV == 'production') {
        echo "A fatal system error has occured on application startup. Please contact an admistrator.";
    } else {
        echo "A fatal system error has occured. Please contact an administrator.\n\n";
        echo "Error information:\n";
        echo $e->getMessage() . "(Code " . $e->getCode() . ")\n";
        echo "File: " . $e->getFile() . ", Line: " . $e->getLine() . "\n\n";
        echo "Trace:\n";
        echo $e->getTraceAsString();
        echo "\n\n";
        echo "Extended Trace:\n";
        print_r($e->getTrace());
    }
}
// This fixes a bug with some PHP5 versions and Bytecode-Caches like APC that
// would cause a fatal error
function shutdownCMS() {
    Zend_Session::writeClose(true);
}
register_shutdown_function('shutdownCMS');
