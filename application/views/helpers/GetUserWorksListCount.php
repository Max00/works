<?php

class Zend_View_Helper_GetUserWorksListCount {
    public function GetUserWorksListCount($userId) {
        $worksCount = TravauxController::getWorksListCount($userId);
        if($worksCount > 0) {
            echo $worksCount;
        } else echo '0';
    }
}