<?php

class Application_Form_AddUser extends Zend_Form {

    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formAddUser.ini', 'addUser');
        parent::__construct($config);
        $typesTable = new Application_Model_Types();                            // Ajout des types
        
        $this->getElement('form_title')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('fname')->removeDecorator('Label');
        $this->getElement('lname')->removeDecorator('Label');
        $this->getElement('pass')->removeDecorator('Label');
        $this->getElement('mail')->removeDecorator('Label');
        
        $this->setDecorators(array(
            array('ViewScript', array(
                'viewScript' => '_formAddUser.phtml',
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

