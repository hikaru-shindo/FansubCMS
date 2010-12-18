<?php
/*
 * This file is part of FansubCMS.
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
 */
class NewsCommentTable extends Doctrine_Table {
    public function getSpamPaginator() {
        $q = $this->createQuery();
        $q->where('spam = ?', 'yes');
        $adapter = new FansubCMS_Paginator_Adapter_Doctrine($q);
        return new Zend_Paginator($adapter);
    }
}