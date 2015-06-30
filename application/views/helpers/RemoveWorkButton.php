<?php

class Zend_View_Helper_RemoveWorkButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveWorkButton($workId) {
        return '<div class="ui button delete_work_button" data-href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'supprimer', 'id' => $workId), null, true) . '"><i class="remove icon"></i></div>';
    }
}