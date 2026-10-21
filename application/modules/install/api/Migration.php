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

class Install_Api_Migration
{
    /**
     * Instance of this class
     *
     * @var Install_Api_Migration
     */
    protected static $_instance;
    /**
     * The migration object
     *
     * @var Doctrine_Migration
     */
    private $_migration;

    /**
     * returns an instance of Install_Api_Migration
     *
     * @return Install_Api_Migration
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->_migration = new Doctrine_Migration(realpath(APPLICATION_PATH.'/../migrations'));
    }

    /**
     * Get the current version of the database
     *
     * @return integer $version
     */
    public function getCurrentVersion() {
        return $this->_migration->getCurrentVersion();
    }

    /**
     * Gets the latest possible version from the loaded migration classes
     *
     * @return integer $latestVersion
     */
    public function getLatestVersion() {
        return $this->_migration->getLatestVersion();
    }

    /**
     * Run the migration process but rollback at the very end. Returns true or
     * false for whether or not the migration can be ran
     *
     * @param  string  $to
     * @return boolean $success
     */
    public function migrateDryRun($to = null)
    {
        return $this->_migration->migrateDryRun($to);
    }

    /**
     * Perform a migration process by specifying the migration number/version to
     * migrate to. It will automatically know whether you are migrating up or down
     * based on the current version of the database.
     *
     * @param  integer $to       Version to migrate to
     * @param  boolean $dryRun   Whether or not to run the migrate process as a dry run
     * @return integer $to       Version number migrated to
     * @throws Doctrine_Exception
     */
    public function migrate($to = null, $dryRun = false)
    {
        if(empty($to)) {
            $to = $this->_migration->getLatestVersion();
        }

        $result = $this->_migration->migrate($to, $dryRun);

        // TODO: This is a dirty fix for problems... if we get here migration was successful
        if(!$dryRun && $result) {
            $this->_migration->setCurrentVersion($to);
        }

        return $result;
    }

    /**
     * Set the current version of the database
     *
     * @param integer $number
     * @return void
     */
    public function setCurrentVersion($number)
    {
        $this->_migration->setCurrentVersion($number);
    }

    /**
     * returns the migration object
     *
     * @return Doctrine_Migration
     */
    public function getMigrationObject()
    {
        return $this->_migration;
    }
}
