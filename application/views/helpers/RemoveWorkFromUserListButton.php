<?php

class Zend_View_Helper_RemoveWorkFromUserListButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveWorkFromUserListButton($workId, $userId) {
        echo '<div class="ui button clickable_link" data-href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'retirer-liste-perso', 'travail' => $workId, 'operateur' => $userId, 'redirect' => 'list'), null, true) . '"><i class="minus icon"></i></a>';
    }
}