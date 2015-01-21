<?php

class Application_Model_Roles extends Zend_Db_Table_Abstract {
    
    protected $_name            = 'roles';
    
    static $ROLE_WORKER         = 1;
    static $ROLE_SUPERVISOR     = 2;
    
    public function getRoleName($roleId) {
        $this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
        $req = $this->select()->where('id = ?', $roleId);
        $row = $this->fetchRow($req);
        return $row;
    }
}