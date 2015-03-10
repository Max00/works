<?php

class Zend_View_Helper_ChangePrioButtons {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function ChangePrioButtons($workId, $currentPrioId) {
        $prios = Application_Model_Travaux::$PRIORITIES;
        $lowPrio = max($prios);
        $highPrio = min($prios);
        $showUp = $currentPrioId > $highPrio ? '<a class="move-up" href="' . 
                $this->_view->url(array('controller' => 'travaux', 'action' => 'changer-prio', 'travail' => $workId, 'prio' => $currentPrioId - 1), null, true) .
                '"><i class="fa fa-arrow-up"></i></a>' : '';
        $showDown = $currentPrioId < $lowPrio ?'<a class="move-down" href="' . 
                $this->_view->url(array('controller' => 'travaux', 'action' => 'changer-prio', 'travail' => $workId, 'prio' => $currentPrioId + 1), null, true) .
                '"><i class="fa fa-arrow-down"></i></a>' : '';
        // $implode = $showUp && $showDown ? ' | ' : '';
        // return $showUp . $implode . $showDown;
        return $showUp . $showDown;
    }
}