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

class Cms_Form_EditStatic extends Zend_Form {

    public function __construct($values=array(), $insert = false, $options=null) {
        parent::__construct($options);

        $this->setName('postcomment')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', 'postcomment');

        # title
        $title = $this->createElement('text','title');
        $title->addFilter('StringToLower')
                ->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->addValidator('NotEmpty', true, array(
                'messages'=> array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addValidator('Alnum',true,array(
                'messages' => array(
                        Zend_Validate_Alnum::NOT_ALNUM => 'default_form_error_alnum'
                )
                ))
                ->setRequired(true)
                ->setValue(empty($values['title']) ? null : $values['title'])
                ->setLabel('cms_static_field_title');

        $content = $this->createElement('textarea', 'text');
        $content->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages'=> array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setValue(empty($values['text']) ? null : $values['text'])
                ->setRequired(true)
                ->setLabel('cms_static_field_content');

        # add elements to the form
        if($insert)
            $this->addElement($title);

        $this->addElement($content);

        # commit button
        $this->addElement('submit', 'submit', array('label' => $insert ? 'add' : 'update', 'class' => 'button'));
    }

    public function isValid($data) {
        $ret = parent::isValid($data);

        if ($this->getElement('title') && !$this->getElement('title')->hasErrors()) {
            $fileExistsValidator = new Zend_Validate_File_NotExists();
            $fileExistsValidator->setDirectory(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'static')
                    ->setMessage('cms_form_error_title_exists');
            if(!$fileExistsValidator->isValid($data['title'].'.html')) {
                $this->getElement('title')->addErrors($fileExistsValidator->getMessages());
                $ret = false;
            }
        }

        return $ret;
    }

}
