<?php

class Application_Form_AddWork extends Zend_Form {

    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formAddWork.ini', 'addWork');
        parent::__construct($config);
        $typesTable = new Application_Model_Types();                            // Ajout des types
        $types = $typesTable->getAllTypes();
        foreach ($types as $currentType) {
            $this->getElement('types')->addMultiOptions(array($currentType['id'] => $currentType['name']));
        }
        $this->addElementPrefixPath('Vdf_Form_Decorator_', ROOT_PATH . 'Vdf/Form/Decorator/', 'decorator');
        
        $this->getElement('prio')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('oeuvre_id')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('worktype')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('term')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('form_title')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('title')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('auth_token')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('description')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('additional_worker_template')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('add_additional_worker')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('tools')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('emplacement')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('emplacement_coords_x')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('emplacement_coords_y')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('desc_emplacement')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('worktype')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('prio')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('types')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('add_type_label')->removeDecorator('Label')->removeDecorator('Errors');
        $this->getElement('frequency')->removeDecorator('Label')->removeDecorator('HtmlTag')->removeDecorator('Errors');
        $this->getElement('frequency_type')->removeDecorator('Label')->removeDecorator('HtmlTag')->removeDecorator('Errors');
        
        $this->setDecorators(array(
            array('ViewScript', array(
                'viewScript' => '_formWork.phtml',
                'mode' => 'add',
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
