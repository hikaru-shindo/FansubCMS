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
class Gadgets_DynamicController extends FansubCMS_Controller_Action
{
    public function menuAction ()
    {
        $links = $this->request->getParam('links', array());

        foreach($links as $key => $link) {
            $url = '';
            if(isset($link['intern']) && $link['intern']) {
                $url = $this->view->baseUrl();
            }

            $url .= $link['url'];

            $links[$key]['url'] = $url;

            $links[$key]['label'] = $this->translate($link['label']);
        }

        $this->view->links = $links;
    }
}
