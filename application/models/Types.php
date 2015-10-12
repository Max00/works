<?php

class Application_Model_Types extends Zend_Db_Table_Abstract {
    protected $_name = 'types';
    protected $_dependentTables = array('Application_Model_TravauxTypes');
    
    public function getAllTypes() {
        $select = $this->_db->select()
                ->from(array('t'=>'types'), array('t.id', 't.name', 't.color'))
                ->order('name ASC');
        return $this->_db->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);
    }
    
    public function createType($name, $color) {
        return $this->insert(array('name' => $name,
            'color' => $color));
    }
    
    public function getTypeBasics($typeId) {
        $select = $this->select()
                ->from($this, array('name', 'color'))
                ->where('id = ?', $typeId);
        return $this->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }

    public function getTypeId($name) {
        $select = $this->_db->select()
                ->from(array('t' => 'types'), array('t.id'))
                ->where($this->_db->quoteInto('name=?', $name));
        $sameName = $this->_db->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);
        if(count($sameName) > 0) {
            $sameNameRow = $sameName[0];
            return $sameNameRow['id'];
        }
        return false;
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