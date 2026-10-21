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

class News_Form_Comment extends Zend_Form {

    public function __construct($action, $options=null) {
        parent::__construct($options);

        $this->setName('postcomment')
                ->setAction($action)
                ->setMethod('post')
                ->setAttrib('id', 'postcomment');

        # author
        $authorValidatorDB = new FansubCMS_Validator_NoRecordExists('User_Model_User', 'name');
        $authorValidatorDB->setMessages(array(FansubCMS_Validator_NoRecordExists::RECORD_EXISTS => 'news_comment_form_error_author_user_exists'));

        $author = $this->createElement('text', 'author');
        $author->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages'=> array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                /*   ->addValidator('alnum', true, array(
                'messages' => array(
                        Zend_Validate_Alnum::NOT_ALNUM => 'news_comment_form_error_author_alnum'
                )
                )) */
                //               ->addValidator('regex', false, array(
                //                   'pattern' => '/^[a-zA-Z]+/',
                //                   'messages' => array(
                //                       Zend_Validate_Regex::NOT_MATCH => 'news_comment_form_error_regex'
                //                   )))
                ->addValidator('stringLength', true, array(
                'min' => 3,
                'max' => 32,
                'messages' => array(
                        Zend_Validate_StringLength::TOO_LONG => 'news_comment_form_error_author_length',
                        Zend_Validate_StringLength::TOO_SHORT => 'news_comment_form_error_author_length'
                )))
                ->addValidator($authorValidatorDB)
                ->setRequired(true)
                ->setLabel('news_comment_field_author');
        # email
        $email = $this->createElement('text', 'email');
        $email->addValidator('NotEmpty', true, array(
                'messages'=> array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('EmailAddress', false, array(
                'allow' => Zend_Validate_Hostname::ALLOW_DNS,
                'domain' => true,
                'messages' => array(
                        Zend_Validate_EmailAddress::DOT_ATOM => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_FORMAT => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_HOSTNAME => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_LOCAL_PART => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_MX_RECORD => 'default_form_error_email',
                        Zend_Validate_EmailAddress::INVALID_SEGMENT => 'default_form_error_email',
                        Zend_Validate_EmailAddress::LENGTH_EXCEEDED => 'default_form_error_email',
                        Zend_Validate_EmailAddress::QUOTED_STRING => 'default_form_error_email'
                )
                ))
                ->setRequired(true)
                ->setLabel('news_comment_field_email');
        # url
        $url = $this->createElement('text', 'url');
        $url->setRequired(false)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setLabel('news_comment_field_website');

        # comment
        $comment = $this->createElement('Textarea', 'comment');
        $comment->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty', true, array(
                'messages'=> array(
                        Zend_Validate_NotEmpty::IS_EMPTY => 'default_form_error_empty_value'
                )))
                ->setAttrib('rows', 15)
                ->setAttrib('cols', 40)
                ->setLabel('news_comment_field_text');

        #captcha
        if (!User_Model_User::isLoggedIn()) {
            $imgUrl = substr($_SERVER['PHP_SELF'], 0, -9) . '/media/common/images/tmp'; // little hack to have the correct baseurl
            $imgUrl = str_replace('//', '/', $imgUrl);
            $captcha = new Zend_Form_Element_Captcha('captcha', array(
                            'label' => 'captcha',
                            'captcha' => array(
                                'captcha' => 'Image',
                                'wordLen' => 6,
                                'timeout' => 300,
                                'height' => 80,
                                'width' => 150,
                                'startImage' => null,
                                'font' => realpath(APPLICATION_PATH . '/data/ttf') . '/captcha.ttf',
                                'imgurl' => $imgUrl,
                                'imgDir' => HTTP_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tmp'
                            ),
                            'errorMessages'=>array(
                                    'default_form_error_captcha_wrong'
                            )
            ));
            $captcha->setRequired(true);
        }
        # add elements to the form
        if (!User_Model_User::isLoggedIn())
            $this->addElement($author)
                 ->addElement($email);
        $this->addElement($url)
             ->addElement($comment);

        if (!User_Model_User::isLoggedIn())
            $this->addElement($captcha);

        # commit button
        $this->addElement('submit', 'submit', array('label' => 'news_comment_field_submit', 'class' => 'button'));
    }

}
