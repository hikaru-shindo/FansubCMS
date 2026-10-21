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
 *
 */
/**
 *
 * This class is the api to install the cms
 * @author Hikaru-Shindo <hikaru@fansubcode.org>
 *
 */
class Install_Api_DoctrineTool
{
    /*
     * class properties
     */

    /**
     *
     * This is the temporary path to do all actions in
     * @var string
     */
    protected $_tmpPath;

    /**
     *
     * This is the path where the modules reside
     * @var string
     */
    protected $_modulePath;

    /**
     *
     * Doctrine Migration
     * @var Doctrine_Migration
     */
    protected $_migration;

    /**
     *
     * Instance of this api
     * @var Install_Api_DoctrineTool
     */
    protected static $_instance;

    /*
     * magic methods
     */

    /**
     * The class constructor
     *
     * @return void
     */
    protected function __construct ()
    {
        mkdir($this->getTempPath());
        mkdir($this->getFromPath());
        mkdir($this->getToPath());
    }

    /**
     * The classes destructor
     *
     * @return void
     */
    public function __destruct ()
    {
        $this->_rrmdir($this->getTempPath());
    }

    /*
     * main methods
     */

    /**
     *
     * Get an instance of the API
     * @return Install_Api_DoctrineTool
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

        /**
     *
     * Returns the temporary path
     * @return string
     */
    public function getTempPath ()
    {
        if (empty($this->_tmpPath)) {
            $this->_tmpPath = realpath(sys_get_temp_dir()) . DIRECTORY_SEPARATOR .
             'fcms_mig_' . md5(microtime() . 'migration');
        }
        return $this->_tmpPath;
    }
    /**
     *
     * Returns the from-path
     * @return string
     */
    public function getFromPath ()
    {
        return $this->getTempPath() . DIRECTORY_SEPARATOR . 'from';
    }
    /**
     *
     * Returns the to-path
     * @return string
     */
    public function getToPath ()
    {
        return $this->getTempPath() . DIRECTORY_SEPARATOR . 'to';
    }

    /**
     *
     * Set the path where the modules reside
     * @param string $path
     * @return void
     */
    public function setModulePath ($path)
    {
        $this->_modulePath = $path;
    }

    /**
     *
     * Returns the module path
     * @return string
     */
    public function getModulePath ()
    {
        if (empty($this->_modulePath)) {
            $this->_modulePath = APPLICATION_PATH . DIRECTORY_SEPARATOR .
             'modules';
        }
        return $this->_modulePath;
    }

    /**
     *
     * Initialize the database
     * @return void
     */
    public function createTablesFromArray()
    {
        $models = $this->_getModels();
        Doctrine::createTablesFromArray($models);
    }

    /**
     *
     * Get the path where the migrations reside
     * @return string
     */
    public function getMigrationPath()
    {
        return realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'migrations';
    }

    /**
     *
     * Get the migration object
     * @return Doctrine_Migration
     */
    public function getMigration()
    {
        if(empty($this->_migration)) {
            $this->_migration = new Doctrine_Migration($this->getMigrationPath());
        }

        return $this->_migration;
    }

    /**
     * Sets the migration version to $version
     *
     * @param integer $version
     * @return void
     */
    public function setMigrationVersion($version)
    {
        $this->getMigration()->setCurrentVersion($version);
    }

    /**
     * Sets the migration version to latest available. Bassically a wrapper for setMigrationVersion()
     *
     * @return void
     */
    public function setMigrationVersionToCurrent()
    {
        $this->setMigrationVersion($this->getMigration()->getLatestVersion());
    }


    /**
     * Export fixture files to $path
     *
     * @param string $path
     * @return void
     */
    public function exportFixtures($path)
    {
        $this->_loadDoctrineModels();
        Doctrine::dumpData($path, true);
    }

    /**
     * Import fixtures from $path. If $append is true the database will not be purged
     * before importing.
     *
     * @param string $path
     * @param boolean $append
     * @return void
     */
    public function importFixtures($path, $append = false)
    {
        $this->_loadDoctrineModels();
        Doctrine::loadData($path, $append);
    }

    /**
     *
     * Generate the models to the module folder
     * @return void
     */
    public function generateModels ()
    {
        $this->_getSchema($this->getFromPath());
        $this->_generateModels($this->getFromPath(), $this->getToPath());
        $models = glob($this->getToPath() . DIRECTORY_SEPARATOR . '*.php');

        foreach ($models as $model) {
            $filename = str_replace($this->getToPath() . DIRECTORY_SEPARATOR,
            '', $model);
            $filenameParts = explode('_', $filename);

            $module = strtolower($filenameParts[0]);
            $modelDir = $this->getModulePath() . DIRECTORY_SEPARATOR . $module .
             DIRECTORY_SEPARATOR . 'models';

            if (! is_dir($modelDir)) {
                mkdir($modelDir);
            }

            if (! is_dir($modelDir . DIRECTORY_SEPARATOR . 'Base')) {
                mkdir($modelDir . DIRECTORY_SEPARATOR . 'Base');
            }

            if (! file_exists(
            $modelDir . DIRECTORY_SEPARATOR . $filenameParts[2])) {
                copy($model,
                $modelDir . DIRECTORY_SEPARATOR . $filenameParts[2]);
            }

            unlink($model);
        }

        $baseModels = glob(
        $this->getToPath() . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR .
         '*.php');
        foreach ($baseModels as $model) {
            $filename = str_replace($this->getToPath() . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR,
            '', $model);
            $filenameParts = explode('_', $filename);
            $module = strtolower($filenameParts[0]);
            $modelDir = $this->getModulePath() . DIRECTORY_SEPARATOR . $module .
             DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Base';
            if (file_exists($modelDir . DIRECTORY_SEPARATOR . $filenameParts[3])) {
                unlink($modelDir . DIRECTORY_SEPARATOR . $filenameParts[3]);
            }
            copy($model, $modelDir . DIRECTORY_SEPARATOR . $filenameParts[3]);
            unlink($model);
        }
        rmdir($this->getToPath() . DIRECTORY_SEPARATOR . 'Base');
        $garbage = glob($this->getFromPath() . DIRECTORY_SEPARATOR . '*');
        foreach ($garbage as $del) {
            if (is_file($del)) {
                unlink($del);
            } else {
                $this->_rrmdir($dirname);
            }
        }
    }

    /**
     * Generates the migration classes
     *
     * @param boolean $changesOnly
     * @return array
     */
    public function generateMigration($changesOnly = false)
    {
        $dm = Doctrine_Manager::getInstance();

        if(!$changesOnly && $dm->getAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT) != '%s') {
            throw new Exception("You can not create migrations from diff with a custom Doctrine attribute 'ATTR_TBLNAME_FORMAT'");
        }
        if(!$changesOnly && $dm->getAttribute(Doctrine_Core::ATTR_IDXNAME_FORMAT) != '%s_idx') {
            throw new Exception("You can not create migrations from diff with a custom Doctrine attribute 'ATTR_IDXNAME_FORMAT'");
        }
        if(!$changesOnly && $dm->getAttribute(Doctrine_Core::ATTR_SEQNAME_FORMAT) != '%s_seq') {
            throw new Exception("You can not create migrations from diff with a custom Doctrine attribute 'ATTR_SEQNAME_FORMAT'");
        }

        $this->_getModels($this->getFromPath());
        $this->_getSchema($this->getToPath());

        $changes = new Doctrine_Migration_Diff($this->getFromPath(), $this->getToPath(), $this->getMigrationPath());

        if($changesOnly) {
            return $changes->generateChanges();
        } else {
            return $changes->generateMigrationClasses();
        }
    }

    /*
     * Helpers
     */

    /**
     * Let doctrine load its models
     *
     * @return void
     */
    protected function _loadDoctrineModels()
    {
        $models = $this->_getModels();

        foreach($models as $model) {
            $cleanModel = str_replace($this->getModulePath() . DIRECTORY_SEPARATOR, '', $model);
            $cleanModel = str_replace('.php', '', $cleanModel);
            /*
             * 0 => modulename
             * 1 => models
             * 2 => model base name
             */
            $classnameParts = explode(DIRECTORY_SEPARATOR, $cleanModel);
            $classname = ucfirst($classnameParts[0]) . '_Model_' . $classnameParts[2];
            if(class_exists($classname) && is_subclass_of($classname, 'Doctrine_Record')) {
                Doctrine::loadModel($classname, $model);
            }
        }
    }

    /**
     * Get the schema files and save them to $path
     *
     * @param string $path
     * @return void
     */
    protected function _getSchema ($path)
    {
        $schema = glob(
        $this->getModulePath() . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR .
         'models' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR .
         '*.yml');

        foreach ($schema as $yml) {
            $filename =  explode(
            DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'schema' .
             DIRECTORY_SEPARATOR,
            str_replace($this->getModulePath() . DIRECTORY_SEPARATOR, '', $yml));
            copy($yml, $path . DIRECTORY_SEPARATOR . implode('_',$filename));
        }
    }

    /**
     * Copies all models to $path, returns a list of models if path is empty
     *
     * @param string $path
     * @return void|array
     */
    protected function _getModels ($path)
    {
        if(empty($path)) {
            $models = glob($this->getModulePath() . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR .
            'models' . DIRECTORY_SEPARATOR . '*.php');

            $classNames = array();

            foreach($models as $model) {
                $filename = str_replace($this->getModulePath() . DIRECTORY_SEPARATOR, '', $model);
                $filenameParts = explode(DIRECTORY_SEPARATOR, $filename);
                $className = ucfirst($filenameParts[0]).'_Model_'.str_replace('.php', '', $filenameParts[2]);

                if(!class_exists($className)) {
                    // can't be an doctrine model
                    continue;
                }

                if(is_subclass_of($className, 'FansubCMS_Doctrine_Record')) {
                    // ignore models which are not doctrine models
                    $classNames[] = $className;
                }
            }

            return $classNames;
        }

        $models = glob($this->getModulePath() . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR .
        'models' . DIRECTORY_SEPARATOR . '*.php');
        $baseModels = glob($this->getModulePath() . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR .
        'models' . DIRECTORY_SEPARATOR . 'Base' . DIRECTORY_SEPARATOR . '*.php');

        $models = array_merge($models, $baseModels);

        foreach($models as $model) {
            $filename = str_replace($this->getModulePath() . DIRECTORY_SEPARATOR, '', $model);
            $filenameParts = explode(DIRECTORY_SEPARATOR, $filename);
            $module = $filenameParts[0];
            array_shift($filenameParts); // module name
            array_shift($filenameParts); // 'models'

            $filename = '';

            $filename .= ucfirst($module) . '_Model_';

            $filename .= implode('_', $filenameParts);
            copy($model, $path . DIRECTORY_SEPARATOR . $filename);
        }
    }

    /**
     * Generate models from $yaml to $path
     *
     * @param string $yaml
     * @param string $path
     * @param array $options
     * @return void
     */
    protected function _generateModels ($yaml, $path, $classPrefix = null, array $options = array())
    {
        if (! count($options)) {
            $options = array(
            'baseClassName' => 'FansubCMS_Doctrine_Record',
            'suffix' => '.php', 'baseClassesDirectory' => 'Base',
            'baseClassPrefix' => '', 'generateTableClasses' => true,
            'generateBaseClasses' => true, 'phpDocPackage' => 'FansubCMS',
            'phpDocSubpackage' => 'Models', 'phpDocName' => 'FansubCMS Dev Team',
            'phpDocEmail' => 'hikaru@fansubcode.org');
        }

        if(!empty($classPrefix)) {
            $options['classPrefix'] = $classPrefix;
        }

       // Doctrine::generateModelsFromYaml($yaml, $path, $options);
        $this->_importSchema($yaml, 'yml', $path, $options);
    }

    /**
     * importSchema
     *
     * A method to import a Schema and translate it into a Doctrine_Record object
     *
     * @param  string $schema       The file containing the XML schema
     * @param  string $format       Format of the schema file
     * @param  string $directory    The directory where the Doctrine_Record class will be written
     * @param  array  $models       Optional array of models to import
     *
     * @return void
     */
    protected function _importSchema($schema, $format = 'yml', $directory = null, $options = array())
    {
        $schema = (array) $schema;
        $builder = new Install_Api_DoctrineBuilder();
        $builder->setTargetPath($directory);
        $builder->setOptions($options);

        $importer = new Doctrine_Import_Schema();
        $array = $importer->buildSchema($schema, $format);

        if (count($array) == 0) {
            throw new Doctrine_Import_Exception(
                sprintf('No ' . $format . ' schema found in ' . implode(", ", $schema))
            );
        }
        foreach ($array as $name => $definition) {
            $builder->buildRecord($definition);
        }
    }

    /**
     * Removes a directory recursivly
     *
     * @param string $dir
     * @return void
     */
    protected function _rrmdir ($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->_rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }

            reset($objects);
            rmdir($dir);
        }
    }
}
