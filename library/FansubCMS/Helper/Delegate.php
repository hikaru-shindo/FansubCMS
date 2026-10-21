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
 * Helps with delegates
 *
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class FansubCMS_Helper_Delegate
{
    //put your code here
    public $delegateNames,
           $moduleName,
           $delegates,
           $addIn,
           $settings,
           $request;

    public function __construct($moduleName = null)
    {
        $this->settings = Zend_Registry::get('environmentSettings');
        $this->delegates = array('default' => array(), 'layout' => array());
        $this->delegateNames = array();
        $this->moduleName = $moduleName;
    }

    public function init($moduleName, $addIn = '', $settings = null, $request = null)
    {
        $this->moduleName = $moduleName;
        $this->addIn = $addIn;
        $this->request = $request;
        $this->settings = $settings;

    }
    public function addDelegateType($name)
    {
        if(!in_array($name, $this->delegateNames)) {
            $this->delegateNames[] = $name;
        }
    }

    public function invokeDelegate($name, $method, $args = null)
    {
        if(in_array($name, $this->delegateNames) && (empty($this->delegates['version'][$name])
                || empty($this->delegates['default'][$name]))) {
            $className = ucfirst($this->moduleName).'_Delegate_'.ucfirst($this->settings->page->layout) . (!empty($this->addIn) ? '_'.$this->addIn : '').'_'.$name;
            $classBaseName = ucfirst($this->moduleName).'_Delegate_Default'.(!empty($this->addIn) ? '_'.$this->addIn : '').'_'.$name;
            if(class_exists($className)) {
                $this->delegates['layout'][$name] = new $className($this->settings, $this->request);
            }
            if(class_exists($classBaseName)) {
                $this->delegates['default'][$name] = new $classBaseName($this->settings, $this->request);
            }
        }
        if(!empty($this->delegates['layout'][$name]) && $this->delegates['layout'][$name] != null && method_exists($this->delegates['layout'][$name], $method)) {
            return call_user_func_array(array($this->delegates['layout'][$name], $method), $args);

        } else if(!empty($this->delegates['default'][$name]) && $this->delegates['default'][$name] != null && method_exists($this->delegates['default'][$name], $method)) {
            return call_user_func_array(array($this->delegates['default'][$name], $method), $args);

        } else {
            return;
        }
    }
}
?>
