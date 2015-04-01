<?php

class Vdf_Validate_NotEmptyIfFieldEmpty {
    protected $fields;
    
    protected $messages = array();
    
    public function __construct($options = array()) {
        $this->fields = $options['fields'];
    }
    
    public function isValid($value, $context = null) {
        // Si on a pas de valeur, vérifier que les champs $fields sont tous attribués
        if(empty($value)) {                                                     // Valeur verifiee vide
            foreach($this->fields as $curField) {                               // Pour chaque champ, qui ne doit pas etre vide
                                                                                // $curField ne contient pas la valeur, mais l'ID du champ
                if(empty($context[$curField])) {                                // Si le champ est vide
                    $this->messages[] = 'Renseignez ce champ ou précisez un emplacement géographique';
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