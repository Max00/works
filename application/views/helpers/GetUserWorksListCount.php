<?php

class Zend_View_Helper_getUserWorksListCount {
    public function getUserWorksListCount($userId) {
        $worksCount = TravauxController::getWorksListCount($userId);
        if($worksCount > 0) {
            echo ' (' . $worksCount . ')';
        }
    }
}