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

class News_Form_EditNews extends Zend_Form {

    public function __construct($values=array(), $insert=false, $options=null) {
        parent::__construct($options);

        $this->setName($insert ? 'addnews' : 'editnews')
                ->setAction('#')
                ->setMethod('post')
                ->setAttrib('id', $insert ? 'addnews' : 'editnews');

        $title = $this->createElement('text', 'title')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setLabel('news_admin_add_field_title')
                ->setValue(isset($values['title']) ? $values['title'] : null);

        $text = $this->createElement('textarea', 'text')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setAttrib('rows', 15)
                ->setAttrib('cols', 40)
                ->setLabel('news_admin_add_field_text')
                ->setValue(isset($values['text']) ? $values['text'] : null);

        $public = $this->createElement('radio', 'public')
                ->setMultiOptions(array(
                'yes' => 'yes_term',
                'no' => 'no_term'
                ))
                ->setRequired(true)
                ->addValidator('NotEmpty', true, array(
                'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setLabel('news_admin_add_field_public')
                ->setValue(isset($values['public']) ? $values['public'] : 'no');

        # publish date
        $envConf = Zend_Registry::get('environmentSettings');
        $time = empty($values['publish_date']) ? time() : strtotime($values['publish_date']);
        $date = new Zend_Date($time,null,$envConf->locale);
        $publishDate = $this->createElement('text', 'publishdate')
                ->setValue($date->toString(Zend_Date::DATE_SHORT))
                ->setLabel('news_admin_field_news_publish_date');
        $iso = $this->createElement('hidden', 'isoDate')
                ->setDecorators(array('viewhelper'))
                ->setValue(isset($values['publish_date']) ? $values['publish_date'] : null);

        # add elements to the form
        $this->addElement($title)
                ->addElement($text)
                ->addElement($public)
                ->addElement($publishDate)
                ->addElement($iso);

        # commit button
        $this->addElement('submit', 'submit', array('label' => $insert ? 'field_add' : 'field_edit', 'class' => 'button'));
    }

}
