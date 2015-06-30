<?php

class Zend_View_Helper_SetWorkDoneButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function SetWorkDoneButton($workId) {
        $setDoneLink = $this->_view->url(array('controller' => 'travaux', 'action' => 'marquer-fait', 'id' => $workId), null, true);
        return '<div class="ui button set_work_done_button" data-href="'.$setDoneLink.'"><i class="flag icon"></i></div>';
    }
}