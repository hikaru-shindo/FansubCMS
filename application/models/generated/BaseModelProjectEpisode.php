<?php

/**
 * BaseModelProjectEpisode
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $project_id
 * @property integer $number
 * @property integer $version
 * @property string $vcodec
 * @property string $acodec
 * @property string $container
 * @property string $crc
 * @property string $title
 * @property string $updated_by
 * @property timestamp $released_at
 * @property Project $Project
 * @property Doctrine_Collection $ProjectUserTask
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Developer <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseModelProjectEpisode extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_episodes');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('project_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('number', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             ));
        $this->hasColumn('version', 'integer', 2, array(
             'type' => 'integer',
             'length' => 2,
             ));
        $this->hasColumn('vcodec', 'string', 10, array(
             'type' => 'string',
             'length' => 10,
             ));
        $this->hasColumn('acodec', 'string', 10, array(
             'type' => 'string',
             'length' => 10,
             ));
        $this->hasColumn('container', 'string', 10, array(
             'type' => 'string',
             'length' => 10,
             ));
        $this->hasColumn('crc', 'string', 8, array(
             'type' => 'string',
             'length' => 8,
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('updated_by', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             ));
        $this->hasColumn('released_at', 'timestamp', null, array(
             'type' => 'timestamp',
             ));

        $this->option('type', 'InnoDB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Project', array(
             'local' => 'project_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasMany('ProjectUserTask', array(
             'local' => 'id',
             'foreign' => 'project_episode_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}