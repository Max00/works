<?php

class Zend_View_Helper_RemoveWorkButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveWorkButton($workId) {
        return '<a class="remove" href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'supprimer', 'id' => $workId), null, true) . '"><i class="fa fa-remove"></i></a>';
    }
}