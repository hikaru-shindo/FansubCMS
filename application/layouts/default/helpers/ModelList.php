<?php
/**
 * ZFDoctrine
 *
 * Copyright (c) 2010, Benjamin Eberlei
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the ZFDoctrine nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Benjamin Eberlei BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Original class name: ZFDoctrine_View_Helper_ModelList
 * This class was modified by Hikaru-Shindo to function in FansubCMS environment.
 */

class FansubCMS_View_Helper_ModelList extends Zend_View_Helper_Abstract
{
    /**
     * @var array
     */
    static private $_defaultOptions = array(
        'pageParamName' => 'page',
        'paginationStyle' => 'Sliding',
        'paginationScript' => null,
        'actions' => array(),
        'footerActions' => array(),
        'prefix' => 'default_table_',
        'fieldFilter' => array(),
        'rowScript' => null,
        'itemsPerPage' => 30,
        'listScript' => null,
    );

    /**
     * @param string $modelName
     * @param array $options
     * @param Doctrine_Query_Abstract $query
     * @return string
     */
    public function modelList($modelName, array $options = array(), ?Doctrine_Query_Abstract $query = null)
    {
        $options = array_merge(self::$_defaultOptions, $options);

        if (!$options['paginationScript']) {
            $options['paginationScript'] = 'partials/pagination.phtml';
        }

        $table = Doctrine_Core::getTable($modelName);

        if (!$query) {
          $query = $table->createQuery();
        }

        $adapter = new FansubCMS_Paginator_Adapter_Doctrine($query);
        $paginator = new Zend_Paginator($adapter);

        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();
        $currentPage = $request->getParam('page', 1);
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage($options['itemsPerPage']);

        if (!isset($options['listScript'])) {
            $options['listScript'] = 'partials/table.phtml';
        }

        if (!isset($options['showFieldNames'])) {
            $fieldNames = $this->getAutoFieldNames($table);
        } else {
            $fieldNames = $options['showFieldNames'];
        }

        return $this->view->partial($options['listScript'], array(
            'modelName' => $modelName,
            'paginator' => $paginator,
            'currentPage' => $currentPage,
            'options' => $options,
            'fieldNames' => $fieldNames,
        ));
    }

    /**
     * @return array
     */
    private function getAutoFieldNames($table) {
        $data = $table->getColumns();
        $cols = array();
        foreach($data as $name => $def) {
            $columnName = $table->getColumnName($name);
            $fieldName = $table->getFieldName($columnName);

            $cols[] = $fieldName;
        }

        return $cols;
    }
}
