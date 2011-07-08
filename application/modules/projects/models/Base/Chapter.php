<?php

/**
 * Projects_Model_Base_Chapter
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $project_id
 * @property integer $number
 * @property integer $version
 * @property Projects_Model_Project $Projects_Model_Project
 * @property Doctrine_Collection $Projects_Model_Task
 * @property Doctrine_Collection $Projects_Model_ChapterRelease
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Dev Team <hikaru@fansubcode.org>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Projects_Model_Base_Chapter extends FansubCMS_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_chapters');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('project_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('number', 'integer', 5, array(
             'type' => 'integer',
             'length' => 5,
             ));
        $this->hasColumn('version', 'integer', 2, array(
             'type' => 'integer',
             'length' => 2,
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

        $this->hasMany('Projects_Model_Task', array(
             'local' => 'id',
             'foreign' => 'chapter_id'));

        $this->hasMany('Projects_Model_ChapterRelease', array(
             'local' => 'id',
             'foreign' => 'chapter_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}