<?php
/**
 * @copyright  2008, Giorgio Sironi
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 * @version    $Id$
 * @link       http://sf.net/projects/ossigeno
 */
// note: The original name of this class is Otk_Paginator_Adapter_Doctrine!
// This class was modified to fit our needs!
class FansubCMS_Paginator_Adapter_Doctrine implements Zend_Paginator_Adapter_Interface
{
    /**
     * The actual query to use
     *
     * @var Doctrine_Query
     */
    protected $_query;
    /**
     * The mode for the hydration
     *
     * @var integer
     */
    protected $_hydrationMode;

    /**
     * Construct the instance of the paginator class
     *
     * @param Doctrine_Query $query
     * @param integer $hydrationMode
     */
    public function __construct(Doctrine_Query $query, $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
    {
        $this->_query = $query;
        $this->_hydrationMode = $hydrationMode;
    }

    /**
     * Get the items
     *
     * @param integer $offset
     * @param integer $count
     * @return mixed
     */
    public function getItems($offset, $count)
    {
        $query = clone $this->_query;
        $result = $query->limit($count)
                        ->offset($offset)
                        ->execute(array(), $this->_hydrationMode);
        return $result;
    }

    /**
     * Get the item count
     *
     * @return integer
     */
    public function count()
    {
        return $this->_query->count();
    }
}
