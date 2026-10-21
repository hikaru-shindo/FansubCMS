<?php
/**
 * This view helper provides access to the default
 */
class FansubCMS_View_Helper_Markitup extends Zend_View_Helper_Abstract
{
    protected static $_types = array(
        'html' => 'default',
        'textile' => 'textile'
    );

    protected $_identifier;
    protected $_type;

    public function markitup($identifier = '#text', $type = 'textile')
    {
        $this->_identifier = $identifier;
        $this->_type = $type;

        return $this;
    }

    public function __toString()
    {
        $type = empty(self::$_types[$this->_type]) ? 'default' : self::$_types[$this->_type];

        return $this->view->partial('partials/markitup.phtml', array(
            'data' => array (
                'type' => $type,
                'identifier' => $this->_identifier
             )));
    }

}
