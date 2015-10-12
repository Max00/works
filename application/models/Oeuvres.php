<?php

class Application_Model_Oeuvres extends Zend_Db_Table_Abstract {
    protected $_name = 'oeuvres';
    
    public function searchOeuvres($needle) {
        $select = $this->_db->select()
                ->from(array('o' => 'oeuvres'), array('o.id', 'o.numero', 'o.title'))
                ->where('o.title like "%'.$needle.'%"', $needle)
                ->orWhere('o.artist like "%'.$needle.'%"', $needle);
        return $this->_db->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);
    }
    
    public function getOeuvreCoords($oeuvreId) {
        $select = $this->_db->select()
                ->from(array('o'=>'oeuvres'), array('o.coords_x', 'o.coords_y'))
                ->where('o.id=?', $oeuvreId);
        return $this->_db->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }
    
    public function getOeuvreBasics($oeuvreId) {
        $select = $this->_db->select()
                ->from(array('o'=>'oeuvres'), array('o.title', 'o.artist', 'o.numero', 'o.coords_x', 'o.coords_y'))
                ->where('o.id=?', $oeuvreId);
        return $this->_db->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }    
    /*
    public function getOeuvreAttrs($oeuvreId, $fields) {
        foreach($fields as $field) {
            if(!in_array($field, $this->_fields)) {
                throw new Exception('Erreur: le champ ' . $field . 'n\'est pas dans la liste des champs autorisés');
                return;
            }
        }
        $select = $this->_db->select()
                ->from(array('o'=>'oeuvres'), $fields)
                ->where('o.id=?', $oeuvreId);
        return $this->_db->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }
    */
   
   public function getAllOeuvres() {
        $select = $this->_db->select()
            ->from(array('o'=>'oeuvres'), array('o.id', 'o.title', 'o.artist', 'o.numero', 'o.coords_x', 'o.coords_y'))
            ->order('numero ASC');
        return $this->_db->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);
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