<?php

class Application_Model_TravauxTravailleurs extends Zend_Db_Table_Abstract {
    
    /*
     * SCENARIO
     * 
     *      1. SUPERVISOR creates WORK                                          -> INSERT WORK
     *         WORK 'status' = 'open'
     *      2. WORKER adds WORK in 'custom list'                                -> INSERT WORKS_WORKERS (user_id, work_id, date_added)
     *         WORK 'status' = 'waiting'
     *      3. WORKER marks WORK as done                                        -> UPDATE WORK (prio)
     *                                                                          -> UPDATE WORKS_WORKERS (date_done)
     *         WORK 'status' = 'open'
     *      4. SUPERVISOR sees WORK as done in 'done list'                      -> SELECT WORKS_WORKERS (date_done) and compare date_connexion
     *         WORK added to SUPERVISOR 'done list'
     */
    
    protected $_name = 'works_workers';
    protected $_referenceMap = array(
        'DoneBy' => array(
            'columns' => array('user_id'),
            'refTableClass' => 'Application_Model_Users',
            'refColumns' => array('id')
        ),
        'Executes' => array(
            'columns' => array('work_id'),
            'refTableClass' => 'Application_Model_Travaux',
            'refColumns' => array('id')
        )
    );

    public function setWorkDoneBy($workId, $userId) {
        
    }
    
    public function alreadyExists($userId, $workId) {
        $where = array();
        $where[]= $this->getAdapter()->quoteInto('user_id = ?', $userId);        
        $where[]= $this->getAdapter()->quoteInto('work_id = ?', $workId);
        $where[]= $this->getAdapter()->quoteInto('date_done IS NULL');          // On ne cible que les travaux pas encore effectués !
        $rowset = $this->fetchAll($where);
        return boolval(count($rowset));
    }
    
    public function getUserIdForWorkNotDone($workId) {
        $req = $this
                ->select()
                ->from($this, array('user_id'))
                ->where($this->getAdapter()->quoteInto('work_id = ?', $workId))
                ->where('date_done IS NULL');
        $row = $this->fetchRow($req);
        if($row) {
            return $row['user_id'];
        }
        else return null;
    }
    
    public function getUserIdForWork($workId) {
        $req = $this
                ->select()
                ->from($this, array('user_id'))
                ->where($this->getAdapter()->quoteInto('work_id = ?', $workId));
        $row = $this->fetchRow($req);
        if($row) {
            return $row['user_id'];
        }
        else return null;
    }
    
    public function deleteById($userId, $workId, $dateAdded) {
        $where = array();
        $where[]= $this->getAdapter()->quoteInto('user_id = ?', $userId);
        $where[]= $this->getAdapter()->quoteInto('work_id = ?', $workId);
        $where[]= $this->getAdapter()->quoteInto('date_added = ?', $dateAdded);
        $this->delete($where);
    }
    
    public function deleteFromUserList($userId, $workId) {
        $where = array();
        $where[]= $this->getAdapter()->quoteInto('user_id = ?', $userId);
        $where[]= $this->getAdapter()->quoteInto('work_id = ?', $workId);
        $where[]= 'date_done IS NULL';
        $where[]= 'date_added IS NOT NULL';
        $this->delete($where);
    }
    
    public function getCountForUser($userId) {
        $count = count($this->fetchAll('user_id = ' . $userId . ' AND date_done IS NULL'));
        return $count;
    }
    
    public function deleteWorksFromAllCurrentLists($workId) {
        $where = array();
        $where[]= $this->getAdapter()->quoteInto('work_id = ?', $workId);
        // Supprimer les travaux n'ayant pas été effectués de la liste
        $where[]= 'date_done IS NULL';
        $where[]= 'date_added IS NOT NULL';
        $this->delete($where);
    }
}
