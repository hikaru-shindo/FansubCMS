<?php
/*
 *  This file is part of FansubCMS.
 *
 *  FansubCMS is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  FansubCMS is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with FansubCMS.  If not, see <http://www.gnu.org/licenses/>
 */

/**
 * This class is for checking if a value already exists in db
 * @author Hikaru-Shindo <hikaru@animeownage.de>
 * @package FansubCMS
 * @subpackage Validator
 * @version SVN: $Id
 */
class FansubCMS_Validator_NoRecordExists extends Zend_Validate_Abstract
{
    private $_table;
    private $_field;

    const RECORD_EXISTS = '';

    protected $_messageTemplates = array(
        self::RECORD_EXISTS => "'%value%' already exists in database"
    );

    public function __construct($table, $field) {
        if(is_null(Doctrine::getTable($table)))
            throw new Exception('Model does not exist');

        if(!Doctrine::getTable($table)->hasColumn($field))
            throw new Exception('Model does not have column specefied as field');

        $this->_table = Doctrine::getTable($table);
        $this->_field = $field;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if(count($this->_table->findBy($this->_field,$value))>0) {
            $this->_error();
            return false;
        }

        return true;
    }
}
