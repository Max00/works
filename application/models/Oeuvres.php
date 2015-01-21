<?php

class Application_Model_Oeuvres extends Zend_Db_Table_Abstract {
    protected $_name = 'oeuvres';
    protected $_fields = array(
        'title',
        'description',
        'date_creation',
        'date_update',
        'desc_emplact',
        'coords_x',
        'coords_y',
        'coords_z',
        'prio',
        'markup',
        'question',
        'answer',
        'frequency_months',
        'frequency_weeks',
        'frequency_days',
        'date_last_done',
        'oeuvre_id',
    );
    
    public function searchOeuvres($needle) {
        $select = $this->_db->select()
                ->from(array('o' => 'oeuvres'), array('o.id', 'o.title'))
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
}