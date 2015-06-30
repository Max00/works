<?php

class Zend_View_Helper_EditWorkButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function EditWorkButton($workId) {
        return '<div class="ui button clickable_link" data-href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'editer', 'id' => $workId), null, true) . '"><i class="edit icon"></i></div>';
    }
}