<?php

class Zend_View_Helper_RemoveUserButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveUserButton($userId) {
        return '<div class="ui button delete_user_button" data-href="' . $this->_view->url(array('controller' => 'user', 'action' => 'supprimer', 'id' => $userId), null, true) . '"><i class="remove icon"></i></div>';
    }
}