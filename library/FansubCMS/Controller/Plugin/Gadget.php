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

class FansubCMS_Controller_Plugin_Gadget extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Layout::getMvcInstance()->getView();
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/environment.ini', 'gadgets');
        $config = $config->toArray();
        if (!isset($config['gadget']) || !is_array($config['gadget'])) {
            $view->gadgets = array();
            return;
        }
        foreach ($config['gadget'] as $k => $v) {
            if (!isset($config['gadget'][$k]['params']) || !is_array($config['gadget'][$k]['params']))
                $config['gadget'][$k]['params'] = array();
        }
        Zend_Layout::getMvcInstance()->getView()->gadgets = $config['gadget'];
    }
}
