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
 * This class provides access to version management
 * @author Hikaru-Shindo <hikaru@fansubcode.org>
 *
 */
class FansubCMS_Version
{
    /**
     *
     * The current application version
     * @var string
     */
    const VERSION = '0.10.0-git';
    /**
     *
     * Zend Framework
     * @var string
     */
    const ZEND = 'zend';
    /**
     *
     * Doctrine ORM
     * @var string
     */
    const DOCTRINE = 'doctrine';
    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $_lastestVersion;
    /**
     *
     * The url to get current versions from
     * @var string
     */
    protected static $_updateUrl = 'http://fansubcode.org/fancmsVersion';

    /**
     *
     * Get the current version
     * @return string
     */
    public static function getCurrentVersion()
    {
        return self::VERSION;
    }

    /**
     *
     * Get the version of framework given
     * @param string $framework
     * @return string
     */
    public static function getFrameworkVersion($framework)
    {
        if($framework == self::ZEND) {
            return Zend_Version::VERSION;
        } elseif($framework == self::DOCTRINE) {
            return Doctrine_Core::VERSION;
        }

        return '';
    }

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend_Version::VERSION of Zend Framework.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int           -1 if the $version is older,
     *                           0 if they are the same,
     *                           and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);

        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release
     *
     * @return string
     */
    public static function getLatest()
    {
        if (null === self::$_lastestVersion) {
            self::$_lastestVersion = 'not available';

            $handle = fopen(self::$_updateUrl, 'r');
            if (false !== $handle) {
                $versions = stream_get_contents($handle);
                $versions = explode("\n", $versions);

                foreach($versions as $version) {
                    if(substr($version, 0, 7) == 'latest=') {
                        self::$_lastestVersion = trim(str_replace('latest=', '', $version));
                        break;
                    }
                }

                fclose($handle);
            }
        }

        return self::$_lastestVersion;
    }
}
