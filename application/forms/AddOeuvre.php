<?php

class Application_Form_AddOeuvre extends Zend_Form {

    protected $_color;

    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formAddOeuvre.ini', 'addOeuvre');
        parent::__construct($config);

        $this->getElement('form_title')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('title')->removeDecorator('Label');
        $this->getElement('numero')->removeDecorator('numero');
        $this->getElement('artist')->removeDecorator('artist');
        $this->getElement('coords_x')->removeDecorator('coords_x');
        $this->getElement('coords_y')->removeDecorator('coords_y');
        
        $this->_color = null;

        $this->setDecorators(array(
            array('ViewScript', array(
                'viewScript' => '_formAddOeuvre.phtml',
                'mode' => 'edit',
            ))
            )
        );
    }

    public function initToken() {
        $authNS = new Zend_Session_Namespace('authToken');
        $authNS->setExpirationSeconds(TOKEN_EXPIRATION_SECS);
        $authNS->authTokenForm = $hash = md5(uniqid(rand(), 1));                   // Token, pour AJAX notamment
        $this->auth_token->setValue($hash)
                ->removeDecorator('HtmlTag')
                ->removeDecorator('Label');
    }

}

