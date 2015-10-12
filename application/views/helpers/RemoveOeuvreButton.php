<?php

class Zend_View_Helper_RemoveOeuvreButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveOeuvreButton($oeuvreId) {
        return '<div class="ui button delete_oeuvre_button" data-href="' . $this->_view->url(array('controller' => 'oeuvre', 'action' => 'supprimer', 'id' => $oeuvreId), null, true) . '"><i class="remove icon"></i></div>';
    }
}