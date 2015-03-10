<?php

class Application_Model_Users extends Zend_Db_Table_Abstract {
    protected $_name = 'users';
    protected $_dependentTables = array('Application_Model_TravauxTravailleurs');
    
    public function getUserBasics($userId) {
        $select = $this->select()
                ->from($this, array('fname', 'lname', 'mail'))
                ->where('id = ?', $userId);
        return $this->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }
}