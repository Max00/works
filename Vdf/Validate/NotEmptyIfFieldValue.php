<?php

class Vdf_Validate_NotEmptyIfFieldValue {
    protected $field;
    protected $fieldValues;
    
    protected $messages = array();
    
    public function __construct($options = array()) {
        $this->field = $options['field'];
        $this->fieldValues = $options['fieldValues'];
    }
    
    public function isValid($value, $context = null) {
        // Si on a pas de valeur, vérifier que le champ n'a pas une des valeurs précisées
        if(empty($value)) {
            foreach($this->fieldValues as $value) {
                if($context[$this->field] == $value) {
                    $this->messages[] = 'De quoi s\'agit-il ?';
                    return false;
                }
            }
        }
        return true;
    }
    
    public function getMessages() {
        return $this->messages;
    }
}