<?php

class Zend_View_Helper_AddWorkToUserListButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function AddWorkToUserListButton($workId, $userId) {
        echo '<a class="add-ulist" href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'ajouter-liste-perso', 'travail' => $workId, 'operateur' => $userId), null, true) . '"><i class="fa fa-plus"></i></a>';
    }
}