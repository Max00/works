<?php

class Zend_View_Helper_RemoveWorkFromListButton {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function RemoveWorkFromListButton($workId, $userId) {
        echo '<a class="remove-ulist" href="' . $this->_view->url(array('controller' => 'travaux', 'action' => 'retirer-liste-perso', 'travail' => $workId, 'operateur' => $userId), null, true) . '"><i class="fa fa-minus"></i></a>';
    }
}