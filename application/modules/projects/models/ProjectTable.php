<?php

/**
 * Projects_Model_ProjectTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Projects_Model_ProjectTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Projects_Model_ProjectTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Projects_Model_Project');
    }
    
    public function getQueryForListing ($private = null)
    {
        $q = $this->createQuery();
        if ($private === true)
            $pub = 'yes';
        elseif ($private === false)
            $pub = 'no';
        else
            $pub = '%';
        $q->where('private like ?', $pub);
        return $q;
    }
    public function getProjects ()
    {
        $q = $this->createQuery();
        $q->orderBy('name ASC');
        $projects = $q->fetchArray();
        $proRet = array('' => 'pleasechoose');
        foreach ($projects as $key => $project) {
            $proRet[$project['id']] = $project['name'];
        }
        return $proRet;
    }
    public function getFrontListing ()
    {
        return $this->getQueryForListing(false, false)
            ->orderBy('name ASC')
            ->execute();
    }
    public function getArrayListing ()
    {
        return $this->getQueryForListing(false, false)
            ->orderBy('name ASC')
            ->fetchArray();
    }
}