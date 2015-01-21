<?php

class Zend_View_Helper_SetWorkDoneButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function SetWorkDoneButton($workId) {
        return '<a class="set-done" href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'marquer-fait', 'id' => $workId)) . '"><i class="fa fa-flag"></i></a>';
    }
}