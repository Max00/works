<?php

class Zend_View_Helper_GetUserWorksListCount {
    public function GetUserWorksListCount($userId) {
        require_once APPLICATION_PATH . '/controllers/TravauxController.php';
        $worksCount = TravauxController::getWorksListCount($userId);
        if($worksCount > 0) {
            echo $worksCount;
        } else echo '0';
    }
}