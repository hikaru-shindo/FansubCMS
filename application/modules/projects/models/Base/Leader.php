<?php

/**
 * Projects_Model_Base_Leader
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $user_id
 * @property integer $project_id
 * @property User_Model_User $User_Model_User
 * @property Projects_Model_Project $Projects_Model_Project
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Dev Team <hikaru@fansubcode.org>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Projects_Model_Base_Leader extends FansubCMS_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('project_leaders');
        $this->hasColumn('user_id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('project_id', 'integer', null, array(
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
        $this->hasOne('User_Model_User', array(
             'local' => 'user_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Projects_Model_Project', array(
             'local' => 'project_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}