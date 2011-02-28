<?php

/**
 * Projects_Model_EpisodeTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class Projects_Model_EpisodeTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object Projects_Model_EpisodeTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Projects_Model_Episode');
    }
    
    /**
     * builds the Query for listings
     * @param string $order
     * @return Doctrine_Query
     */
    public function buildQueryForListing ($order = null)
    {
        $q = $this->createQuery();
        if (! is_null($order)) {
            $q->orderBy($order);
        }
        return $q;
    }
    /**
     * returns a Zend_Paginator_Object
     * @see Zend_Paginator
     * @param $pid project id
     * @return Zend_Paginator
     */
    public function getPaginator ($pid = null)
    {
        $q = $this->createQuery();
        $q->leftJoin('Projects_Model_Episode.Projects_Model_Project p')
        ->orderBy('p.name ASC, Projects_Model_Episode.number ASC, Projects_Model_Episode.container ASC');
        if (! empty($pid)) {
            $q->where('Projects_Model_Episode.project_id = ?', $pid);
        }
        $adapter = new FansubCMS_Paginator_Adapter_Doctrine($q);
        return new Zend_Paginator($adapter);
    }
}