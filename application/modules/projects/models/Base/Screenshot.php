<?php

/**
 * Base_Projects_Model_Screenshot
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $project_id
 * @property string $screenshot
 * @property Projects_Model_Project $Projects_Model_Project
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Dev Team <hikaru@fansubcode.org>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Base_Projects_Model_Screenshot extends FansubCMS_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_screenshots');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('project_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('screenshot', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'unique' => true,
             'notnull' => true,
             ));

        $this->option('type', 'InnoDB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Projects_Model_Project', array(
             'local' => 'project_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}