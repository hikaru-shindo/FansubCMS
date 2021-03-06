<?php

/**
 * User_Model_Base_User
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $password
 * @property string $email
 * @property string $description
 * @property enum $show_team
 * @property enum $active
 * @property enum $activated
 * @property timestamp $last_login
 * @property string $sourcetype
 * @property string $sourcekey
 * @property Doctrine_Collection $Projects_Model_User
 * @property Doctrine_Collection $Projects_Model_Leader
 * @property Doctrine_Collection $Projects_Model_Task
 * @property Doctrine_Collection $User_Model_Role
 * @property Doctrine_Collection $User_Model_UserTask
 * @property Doctrine_Collection $News_Model_News
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Dev Team <hikaru@fansubcode.org>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class User_Model_Base_User extends FansubCMS_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('users');
        $this->hasColumn('id', 'integer', null, array(
             'primary' => true,
             'autoincrement' => true,
             'type' => 'integer',
             ));
        $this->hasColumn('name', 'string', 32, array(
             'type' => 'string',
             'unique' => true,
             'length' => 32,
             'notnull' => true,
             ));
        $this->hasColumn('password', 'string', 64, array(
             'type' => 'string',
             'length' => 64,
             'notnull' => true,
             ));
        $this->hasColumn('email', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('description', 'string', 60000, array(
             'type' => 'string',
             'length' => 60000,
             ));
        $this->hasColumn('show_team', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'yes',
              1 => 'no',
             ),
             'default' => 'yes',
             'notnull' => true,
             ));
        $this->hasColumn('active', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'yes',
              1 => 'no',
             ),
             'default' => 'yes',
             'notnull' => true,
             ));
        $this->hasColumn('activated', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'yes',
              1 => 'no',
             ),
             'default' => 'yes',
             'notnull' => true,
             ));
        $this->hasColumn('last_login', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('sourcetype', 'string', 64, array(
             'type' => 'string',
             'length' => 64,
             ));
        $this->hasColumn('sourcekey', 'string', 64, array(
             'type' => 'string',
             'length' => 64,
             ));


        $this->index('user_src_idx', array(
             'fields' => 
             array(
              0 => 'sourcetype',
              1 => 'sourcekey',
             ),
             ));
        $this->option('type', 'InnoDB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Projects_Model_User', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $this->hasMany('Projects_Model_Leader', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $this->hasMany('Projects_Model_Task', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $this->hasMany('User_Model_Role', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $this->hasMany('User_Model_UserTask', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $this->hasMany('News_Model_News', array(
             'local' => 'id',
             'foreign' => 'user_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}