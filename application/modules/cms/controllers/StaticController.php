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
class Cms_StaticController extends FansubCMS_Controller_Action {

    public function showAction() {
        $title = urldecode($this->request->getParam('title'));
        if(file_exists(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . $title . '.html')) {
            $this->view->html = file_get_contents(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . $title . '.html');
        } else {
            $this->_forward('error','error','cms');
        }
    }
}
