<?php

class Zend_View_Helper_ChangePrioButtons {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function ChangePrioButtons($workId, $currentPrioId) {
        $prios = Application_Model_Travaux::$PRIORITIES;
        
        // On affiche prio UP si la prio courante est de 2 ou 3
        $showUp = $currentPrioId > Application_Model_Travaux::$PRIORITIES['Urgent'] ? '<div class="ui button change_prio" data-prio="' . ( $currentPrioId - 1 ) . '"><i class="caret up icon"></i></div>' : '';
        // On affiche prio DOWN si la prio courante est de 1
        $showDown = $currentPrioId == Application_Model_Travaux::$PRIORITIES['Urgent'] ?'<div class="ui button change_prio" data-prio="' . ( $currentPrioId + 1 ) . '"><i class="caret down icon"></i></div>' : '';
        // $implode = $showUp && $showDown ? ' | ' : '';
        // return $showUp . $implode . $showDown;
        return $showUp . $showDown;
    }
}