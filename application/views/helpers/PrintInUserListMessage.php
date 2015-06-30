<?php

class Zend_View_Helper_PrintInUserListMessage {
    private $_view;
    
    private static $INMYLISTMESSAGE = 'Ajouté à votre liste';
    private static $INUSERLISTMESSAGE = 'Ajouté à la liste de %s';
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function PrintInUserListMessage($currentUserId, $userId, $userFName, $userLName) {
        if($currentUserId == $userId) {                                         // Dans la liste de l'utilisateur courant
            echo self::$INMYLISTMESSAGE;
        } else {
            printf(self::$INUSERLISTMESSAGE, $userLName . ' ' . $userLName);
        }
    }
}