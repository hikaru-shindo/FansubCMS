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
 * This class should generate the needed cache options and return them
 *
 * @package FansubCMS
 * @subpackage Cache
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 */
class FansubCMS_Cache_Helper {

    /**
     * The cache manager
     *
     * @var Zend_Cache_Manager
     */
    protected $_cacheManager;
    /**
     * The instance of this class
     *
     * @var FansubCMS_Cache_Helper
     */
    protected static $_instance = null;

    /**
     * The class constructor
     */
    protected function __construct()
    {
        $this->_cacheManager = Zend_Registry::get('Zend_Cache_Manager');
    }

    /**
     * Call the cache manager's method if it does not exist
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_cacheManager, $name), $arguments);
    }

    /**
     * Get an instance
     *
     * @return FansubCMS_Cache_Helper
     */
    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Add a new template
     *
     * @param string $name
     * @param array $frontendOptions
     */
    public function setCacheTemplate($name, $frontendOptions = array())
    {
        // for now only file supported
        $backendOptions = array(
            'name' => 'File',
            'options' => array(
                'file_name_prefix' => 'fansubcms',
                'cache_dir' => CACHE_PATH
            ));

        if(APPLICATION_ENV == 'development') {
            // @todo change backend to blackhole - needs refactoring of ACL !
            $frontendOptions['options']['lifetime'] = 10; // cache should only live 10 seconds
        }

        $options = array(
            'frontend' => $frontendOptions,
            'backend' => $backendOptions);

        # add a new cache template for this module
        $this->_cacheManager->setCacheTemplate($name, $options);
    }

}
