<?php

class Application_Model_IntervenantsExterieurs extends Zend_Db_Table_Abstract {

    protected $_name = 'additional_workers';
    protected $_dependentTables = array('Application_Model_IntervenantsExterieurs');

    public function addAdditionalWorker($workId, $additionalWorkerLabel) {
        $this->insert(array(
            'work_id' => $workId,
            'label' => $additionalWorkerLabel
        ));
    }

    public function addAdditionalWorkers($additionalWorkersLabels, $workId) {
        foreach ($additionalWorkersLabels as $additionalWorkerLabel) {
            $this->addAdditionalWorker($workId, $additionalWorkerLabel);
        }
    }
    
    public function getAllByWork($workId) {
        $select = $this->_db->select()
                ->from(array('aw'=>'additional_workers'), array('aw.id', 'aw.label'))
                ->where($this->_db->quoteInto('aw.work_id=?', $workId))
                ->order('label ASC');
        return $this->_db->fetchAll($select, array(), Zend_Db::FETCH_ASSOC);
    }
    
    public function removeAllByWork($workId) {
        $this->delete('work_id = '.$workId);
    }
}
