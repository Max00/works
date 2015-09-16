<?php

class Zend_View_Helper_AddWorkToUserListButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function AddWorkToUserListButton() {
        return '<div class="ui button add_ulist"><i class="plus icon"></i></div>';
    }
}