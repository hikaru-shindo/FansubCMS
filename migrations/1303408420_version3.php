<?php
/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Version3 extends Doctrine_Migration_Base
{
    public function up()
    {
        $this->addColumn('projects', 'name_slug', 'string', '255', array(
             ));

    }

    public function postUp()
    {   
        $records = Projects_Model_ProjectTable::getInstance()->findAll();

        foreach($records as $record) {
            if(empty($record->name_slug)) {
                $record->name_slug = Doctrine_Inflector::urlize($record->name);
                $record->save();
            }
        }
    }
    
    public function down()
    {
        $this->removeColumn('projects', 'name_slug');
    }
}