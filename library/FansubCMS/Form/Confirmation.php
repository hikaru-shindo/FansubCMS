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

class FansubCMS_Form_Confirmation extends Zend_Form {
    public function  __construct($options=null) {
        parent::__construct($options);
        $this->setName('confirmation')
                ->setAction('#')
                ->setMethod('post');

        # add elements to the form
        $this->addElement('submit', 'yes', array('label' => 'yes_term', 'class'=>'button'))
             ->addElement('submit', 'no', array('label' => 'no_term', 'class'=>'button'));
    }
}
