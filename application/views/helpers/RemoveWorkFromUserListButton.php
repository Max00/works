<?php

class Zend_View_Helper_RemoveWorkFromUserListButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveWorkFromUserListButton() {
        return '<div class="ui button remove_ulist"><i class="minus icon"></i></div>';}
}