<?php

class Zend_View_Helper_SetWorkDoneButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function SetWorkDoneButton() {
        return '<div class="ui button set_work_done_button"><i class="flag icon"></i></div>';
    }
}