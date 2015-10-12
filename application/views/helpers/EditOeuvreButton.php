<?php

class Zend_View_Helper_EditOeuvreButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function EditOeuvreButton($oeuvreId) {
        return '<div class="ui button edit_oeuvre_button" data-href="' . $this->_view->url(array('controller' => 'oeuvre', 'action' => 'editer', 'oid' => $oeuvreId), null, true) . '"><i class="edit icon"></i></div>';
    }
}