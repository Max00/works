<?php

class Application_Model_Users extends Zend_Db_Table_Abstract {
    protected $_name = 'users';
    protected $_dependentTables = array('Application_Model_TravauxTravailleurs');
    
    public function getUserBasics($userId) {
        $select = $this->select()
                ->from($this, array('fname', 'lname', 'mail', 'role_id'))
                ->where('id = ?', $userId);
        return $this->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }

    public function getAllUsers() {
    	try {
    		$select = $this->select()
            ->from($this, array('id', 'fname', 'lname', 'mail', 'role_id'))
            ->order(array(
            	'role_id DESC',
            	'lname ASC',
            	'fname ASC'));
    		return $this->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);  
        } catch(Exception $ex) {
        	return false;
        }  
    }

    public function deleteById($id) {
        try {
            $where = $this->_db->quoteInto('id = ?', $id);
            return $this->delete($where);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            return false;
        }
    }
}