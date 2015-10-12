<?php

class Zend_View_Helper_RemoveTypeButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveTypeButton($typeId) {
        return '<div class="ui button delete_type_button" data-href="' . $this->_view->url(array('controller' => 'type', 'action' => 'supprimer', 'id' => $typeId), null, true) . '"><i class="remove icon"></i></div>';
    }
}