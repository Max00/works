<?php

class Application_Form_AddType extends Zend_Form {

    protected $_color;

    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formAddType.ini', 'addType');
        parent::__construct($config);

        $this->getElement('form_title')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('label')->removeDecorator('Label');
        
        $this->_color = null;

        $this->setDecorators(array(
            array('ViewScript', array(
                'viewScript' => '_formAddType.phtml',
                'mode' => 'edit',
            ))
            )
        );
    }

    public function getColor() {
        return $this->_color;
    }

    public function setColor($color) {
        $this->_color = $color;
    }

    public function __toString() {
        return str_replace('__COLOR__', $this->_color, parent::__toString());
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

