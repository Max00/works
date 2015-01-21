<?php

class Vdf_Validate_BothOrNone {
    protected $field1;
    protected $field2;
    
    protected $messages = array();
    
    public function __construct($options = array()) {
        $this->field1 = $options['field1'];
        $this->field2 = $options['field2'];
    }
    
    public function isValid($value, $context = null) {                                                 // Both not empty
        /*
        $notEmptyVal = new Zend_Validate_NotEmpty();
        if($notEmptyVal->isValid($value) ||
                !$notEmptyVal->isValid($context['field2'])) {
            $this->messages[] = 'Merci de remplir les deux champs';
            return false;
        }
        return true;
         * 
         */
        $notEmptyVal1 = new Zend_Validate_NotEmpty();
        $notEmptyVal2 = new Zend_Validate_NotEmpty();
        $notEmpty1 = $notEmptyVal1->isValid($value);
        $notEmpty2 = $notEmptyVal2->isValid($context[$this->field1]);
        if(!$notEmpty1 && !$notEmpty2)
            return true;
        if(($notEmpty1 && !$notEmpty2) || (!$notEmpty1 && $notEmpty2)) {
            $this->messages[]= 'Merci de remplir les deux champs';
            return false;
        }
        return true;
    }
    
    public function getMessages() {
        return $this->messages;
    }
}