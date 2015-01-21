<?php

class Application_Form_Login extends Zend_Form {
    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formLogin.ini', 'formLogin');
        parent::__construct($config);
        $this->login->addErrorMessage("Entrez une adresse mail valide");
        $this->pass->addErrorMessage("Entrez un mot de passe");
    }
}