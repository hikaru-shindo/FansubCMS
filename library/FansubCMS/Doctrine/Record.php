<?php
abstract class FansubCMS_Doctrine_Record extends Doctrine_Record implements Zend_Acl_Resource_Interface
{
    /**
     * Returns the string identifier for this model in the form module_m_model
     * @see Zend_Acl_Resource_Interface::getResourceId()
     * @return string
     */
    public function getResourceId()
    {
        return str_replace('_model_','_m_',strtolower(get_class($this)));
    }
}
