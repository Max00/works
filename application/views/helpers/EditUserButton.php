<?php

class Zend_View_Helper_EditUserButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function EditUserButton($userId) {
        return '<div class="ui button edit_user_button" data-href="' . $this->_view->url(array('controller' => 'user', 'action' => 'editer', 'uid' => $userId), null, true) . '"><i class="edit icon"></i></div>';
    }
}