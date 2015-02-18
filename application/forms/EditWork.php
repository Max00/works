<?php

class Application_Form_EditWork extends Zend_Form {

    public function __construct($options = null) {
        $config = new Zend_Config_Ini(CONFIGS_PATH . '/formEditWork.ini', 'editWork');
        parent::__construct($config);
        $typesTable = new Application_Model_Types();                            // Ajout des types
        $types = $typesTable->getAllTypes();
        foreach ($types as $currentType) {
            $this->getElement('types')->addMultiOptions(array($currentType['id'] => $currentType['name']));
        }
        $this->addElementPrefixPath('Vdf_Form_Decorator_', ROOT_PATH . 'Vdf/Form/Decorator/', 'decorator');

        $this->removeDecorator('DtDdWrapper');
        foreach($this->getDisplayGroups() as $group) {
            $group -> removeDecorator('DtDdWrapper');
        }
//        $this->getDisplayGroup('titleDesc')->removeDecorator('DtDdWrapper');    // Remove some decorators
//        $this->getDisplayGroup('titleDescQuestion')->removeDecorator('DtDdWrapper');
//        $this->getDisplayGroup('prioG')->removeDecorator('DtDdWrapper');
//        $this->getDisplayGroup('coords')->removeDecorator('DtDdWrapper');
//        $this->getElement('worktype')->removeDecorator('DtDdWrapper');
//        $this->getElement('prio')->removeDecorator('DtDdWrapper');
//        $this->getElement('oeuvre_id')->removeDecorator('DtDdWrapper');

        $coordWrapperX = new Vdf_Form_Decorator_CoordWrapper(array('id' => 'cell_coord_x'));
        $coordWrapperY = new Vdf_Form_Decorator_CoordWrapper(array('id' => 'cell_coord_y'));
        $this->getElement('emplacement_coords_x')->addDecorator($coordWrapperX);
        $this->getElement('emplacement_coords_y')->addDecorator($coordWrapperY);
        
        $this->getElement('maponload')->removeDecorator('DtDdWrapper');
        $this->getElement('maponload')->removeDecorator('Label');
        $this->getElement('oeuvre_id')->removeDecorator('DtDdWrapper');
        $this->getElement('oeuvre_id')->removeDecorator('Label');
        $this->getElement('frequency_type')->removeDecorator('DtDdWrapper');
        $this->getElement('frequency_type')->removeDecorator('Label');
        $this->getElement('add_type_color_btn')->removeDecorator('DtDdWrapper');
        $this->getElement('add_type_color_btn')->removeDecorator('Label');
        $this->getElement('add_type_btn')->removeDecorator('DtDdWrapper');
        $this->getElement('add_type_btn')->removeDecorator('Label');
        $this->getElement('manage_types_btn')->removeDecorator('DtDdWrapper');
        $this->getElement('manage_types_btn')->removeDecorator('Label');
    }

    public function initToken() {
        $authNS = new Zend_Session_Namespace('authToken');
        $authNS->setExpirationSeconds(TOKEN_EXPIRATION_SECS);
        $authNS->authToken = $hash = md5(uniqid(rand(), 1));                    // Token, pour AJAX notamment
        $this->auth_token->setValue($hash)
                ->removeDecorator('HtmlTag')
                ->removeDecorator('Label');
    }

}
