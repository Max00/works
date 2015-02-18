<?php

class Application_Model_TravauxTypes extends Zend_Db_Table_Abstract {
    protected $_name = 'works_types';
    protected $_referenceMap = array(
        'BelongsTo' => array(
            'columns' => array('type_id'),
            'refTableClass' => 'Application_Model_Types',
            'refColumns' => array('id')
        ),
        'Defines' => array(
            'columns' => array('work_id'),
            'refTableClass' => 'Application_Model_Travaux',
            'refColumns' => array('id')
        )
    );
    
    public function addWorkType($workId, $typeId) {
        $this->insert(array(
            'work_id' => $workId,
            'type_id' => $typeId
        ));
    }
    
    public function addWorkTypes($typesIds, $workId) {
        foreach($typesIds as $typeId) {
            $this->addWorkType($workId, $typeId);
        }
    }
    
    public function removeWorkTypesByWork($workId) {
        $this->delete('work_id = '.$workId);
    }
}