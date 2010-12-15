<?php

/**
 * BaseModelNewsComment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $news_id
 * @property string $author
 * @property string $email
 * @property string $comment
 * @property string $url
 * @property enum $spam
 * @property enum $visible
 * @property string $ip
 * @property News $News
 * 
 * @package    FansubCMS
 * @subpackage Models
 * @author     FansubCMS Developer <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseModelNewsComment extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('news_comments');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('news_id', 'integer', null, array(
             'type' => 'integer',
             'notnull' => true,
             ));
        $this->hasColumn('author', 'string', 32, array(
             'type' => 'string',
             'length' => 32,
             ));
        $this->hasColumn('email', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             ));
        $this->hasColumn('comment', 'string', 60000, array(
             'type' => 'string',
             'length' => 60000,
             'notnull' => true,
             ));
        $this->hasColumn('url', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'notnull' => true,
             ));
        $this->hasColumn('spam', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'yes',
              1 => 'no',
             ),
             'default' => 'no',
             'notnull' => true,
             ));
        $this->hasColumn('visible', 'enum', null, array(
             'type' => 'enum',
             'values' => 
             array(
              0 => 'yes',
              1 => 'no',
             ),
             'default' => 'no',
             'notnull' => true,
             ));
        $this->hasColumn('ip', 'string', 64, array(
             'type' => 'string',
             'length' => 64,
             'notnull' => true,
             ));

        $this->option('type', 'InnoDB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('News', array(
             'local' => 'news_id',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}