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
/**
 * This controller should handle errors in the application.
 *
 * @package FansubCMS
 * @subpackage Controllers
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class Cms_AdminController extends FansubCMS_Controller_Action
{
    protected $_staticPath;

    public function init ()
    {
        $this->_staticPath = realpath(APPLICATION_PATH . '/resource/static');
    }

    public function exportLayoutAction ()
    {
        $layout = $this->request->getParam('layout');
        $layoutMediaPath = HTTP_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $layout;
        $layoutPath = $layoutMedia = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout;
        $delegatePaths = glob(
        APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR .
         'delegates' . DIRECTORY_SEPARATOR . $layout);
        $viewPaths = glob(
        APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'views' .
         DIRECTORY_SEPARATOR . $layout);

        if (empty($layout)) {
            die('no layout given');
        }

        if (! is_dir($layoutPath)) {
            die('layout path does not exist');
        }

        if (! is_dir($layoutMediaPath)) {
            die('layout media path does not exist');
        }

        $tempPath = $this->_getTempPath();
        $tempDir = $tempPath . DIRECTORY_SEPARATOR . md5(time()) . '_style';
        $tempLayoutDir = $tempDir . DIRECTORY_SEPARATOR . 'layout';

        if (mkdir($tempDir)) {
            // create base dirs
            mkdir($tempLayoutDir);
            mkdir($tempLayoutDir . DIRECTORY_SEPARATOR . 'application');
            mkdir($tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'layouts');
            mkdir(
            $tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .
             $layout);
            mkdir($tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'modules');
            mkdir($tempLayoutDir . DIRECTORY_SEPARATOR . 'public');
            mkdir($tempLayoutDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'media');
            mkdir(
            $tempLayoutDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR .
             $layout);

            // copy base
            $this->_recurseCopy($layoutPath,
            $tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .
             $layout);
            $this->_recurseCopy($layoutMediaPath,
            $tempLayoutDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR .
             $layout);

            foreach ($viewPaths as $vP) {
                $cleanVP = str_replace(APPLICATION_PATH, '', $vP);
                $this->_recurseCopy($vP,
                $tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $cleanVP);
            }

            foreach ($delegatePaths as $dP) {
                $cleanDP = str_replace(APPLICATION_PATH, '', $dP);
                $this->_recurseCopy($dP,
                $tempLayoutDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $cleanDP);
            }

            $this->_zip($tempLayoutDir, $tempDir . DIRECTORY_SEPARATOR . $layout . '.zip');

            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . $layout . '.zip"');

            readfile($tempDir . DIRECTORY_SEPARATOR . $layout . '.zip');
        } else {
            die('could not create temporary dir');
        }

        die();
    }

    public function liststaticAction ()
    {
        $this->view->pageTitle = $this->translate('cms_list_static_headline');
        if ($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'editatic'))
            $this->session->tableActions['cms_edit_static'] = array('module' => 'cms', 'controller' => 'admin',
            'action' => 'editstatic');
        if ($this->acl->isAllowed($this->defaultUseRole, 'user_admin', 'deletestatic'))
            $this->session->tableActions['cms_delete_static'] = array('module' => 'cms', 'controller' => 'admin',
            'action' => 'deletestatic');

        $pages = glob($this->_staticPath . DIRECTORY_SEPARATOR . '*.html');
        $pages = str_replace($this->_staticPath . DIRECTORY_SEPARATOR, '', $pages);
        $pages = str_replace('.html', '', $pages);

        $this->view->pages = $pages;
    }

    public function editstaticAction ()
    {
        $this->view->pageTitle = $this->translate('cms_edit_static_headline');
        $this->session->markitup = 'html';
        $title = $this->request->getParam('title');
        $file = $this->_staticPath . DIRECTORY_SEPARATOR . $title . '.html';
        if (file_exists($file)) {
            $this->view->form = new Cms_Form_EditStatic(array('text' => @file_get_contents($file)));
            $req = $this->getRequest();
            if ($req->isPost()) { // there are profile updates
                if ($this->view->form->isValid($_POST)) {
                    $values = $this->view->form->getValues();
                    @unlink($file);
                    @file_put_contents($file, $values['text']);
                    $this->session->message = $this->translate('cms_admin_editstatic_success');
                    $this->_helper->redirector->gotoRoute(
                    array('action' => 'liststatic', 'controller' => 'admin', 'module' => 'cms'));
                } else {
                    $this->view->message = $this->translate('cms_admin_editstatic_failed');
                }
            }
        } else {
            $this->session->message = $this->translate('cms_static_not_existent');
            $this->_helper->redirector->gotoRoute(
            array('action' => 'liststatic', 'controller' => 'admin', 'module' => 'cms'));
        }
    }

    public function addstaticAction ()
    {
        $this->view->pageTitle = $this->translate('cms_add_static_headline');
        $this->session->markitup = 'html';
        $this->view->form = new Cms_Form_EditStatic(array(), true);
        $req = $this->getRequest();
        if ($req->isPost()) { // there are profile updates
            if ($this->view->form->isValid($_POST)) {
                $values = $this->view->form->getValues();
                @file_put_contents($this->_staticPath . DIRECTORY_SEPARATOR . $values['title'] . '.html',
                $values['text']);
                $this->session->message = $this->translate('cms_admin_addstatic_success');
                $this->_helper->redirector->gotoRoute(
                array('action' => 'liststatic', 'controller' => 'admin', 'module' => 'cms'));
            } else {
                $this->view->message = $this->translate('cms_admin_addstatic_failed');
            }
        }
    }

    public function deletestaticAction ()
    {
        $this->view->pageTitle = $this->translate('cms_delete_static_headline');
        $title = $this->request->getParam('title');
        $file = $this->_staticPath . DIRECTORY_SEPARATOR . $title . '.html';
        if (file_exists($file)) {
            $this->view->confirmation = $this->translate('cms_admin_static_delete_confirmation', array('name' => $title));
            $this->view->form = new FansubCMS_Form_Confirmation();
            if ($this->request->getParam('yes')) {
                if (unlink($file)) {
                    $this->session->message = $this->translate('cms_admin_deletestatic_success');
                } else {
                    $this->session->message = $this->translate('cms_admin_deletestatic_failed');
                }
                $this->_helper->redirector->gotoRoute(
                array('action' => 'liststatic', 'controller' => 'admin', 'module' => 'cms'));
            }
        } else {
            $this->session->message = $this->translate('cms_static_not_existent');
            $this->_helper->redirector->gotoRoute(
            array('action' => 'liststatic', 'controller' => 'admin', 'module' => 'cms'));
        }
    }

    /**
     * Copy dir recursive
     * @param $source
     * @param $dest
     * @return void
     */
    protected function _recurseCopy ($source, $destination)
    {
        if (is_file($source)) {
            $perm = fileperms($source);
            copy($source, $destination);
            chmod($destination, $perm);
        }
        if (is_dir($source)) {
            $oldmask = umask(0);
            @mkdir($destination, 0777, true);
            umask($oldmask);
            $dir_handle = opendir($source);
            while ($files = readdir($dir_handle))
                if ($files != "." && $files != "..")
                    $file_array[] = $files;
            closedir($dir_handle);
        }
        for ($i = 0; $i < count($file_array); $i ++) {
            $file = $file_array[$i];
            if ($destination != $source  . DIRECTORY_SEPARATOR . $file)
                $this->_recurseCopy($source  . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
        }
    }

    /**
     * Create a zip archive recursive
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    protected function _zip ($source, $destination)
    {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();
                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);

                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
                        RecursiveIteratorIterator::SELF_FIRST);

                        foreach ($files as $file) {
                            $file = realpath($file);
                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source . DIRECTORY_SEPARATOR, '', $file . '/'));
                            }

                            else
                                if (is_file($file) === true) {
                                    $zip->addFromString(str_replace($source . DIRECTORY_SEPARATOR, '', $file),
                                    file_get_contents($file));
                                }
                        }
                    }

                    else
                        if (is_file($source) === true) {
                            $zip->addFromString(basename($source), file_get_contents($source));
                        }
                }

                return $zip->close();
            }
        }

        return false;
    }

    /**
     *
     * Retuns the temp path
     * @return string
     */
    protected function _getTempPath ()
    {
        if ($temp = ini_get('upload_tmp_dir'))
            return $temp;
        if ($temp = getenv('TMP'))
            return $temp;
        if ($temp = getenv('TEMP'))
            return $temp;
        if ($temp = getenv('TMPDIR'))
            return $temp;
        $temp = tempnam(__FILE__, '');
        if (file_exists($temp)) {
            unlink($temp);
            return dirname($temp);
        }
        return null;
    }

}
