<?php

/**
 * BaseModelProjectUserTask
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $project_episode_id
 * @property integer $user_id
 * @property integer $project_task_id
 * @property ProjectEpisode $ProjectEpisode
 * @property User $User
 * @property ProjectTask $ProjectTask
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Developer <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseModelProjectUserTask extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_tasks');
        $this->hasColumn('project_episode_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('project_task_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));

        $this->option('type', 'InnoDB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('ProjectEpisode', array(
             'local' => 'project_episode_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('User', array(
             'local' => 'user_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('ProjectTask', array(
             'local' => 'project_task_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}