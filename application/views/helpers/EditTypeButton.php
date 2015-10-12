<?php

class Zend_View_Helper_EditTypeButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function EditTypeButton($typeId) {
        return '<div class="ui button edit_type_button" data-href="' . $this->_view->url(array('controller' => 'type', 'action' => 'editer', 'tid' => $typeId), null, true) . '"><i class="edit icon"></i></div>';
    }
}