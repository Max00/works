<?php

class Application_Model_Travaux extends Zend_Db_Table_Abstract {
    protected $_name = 'works';
    protected $_dependentTables = array('Application_Model_TravauxTypes');
    
    public static $PRIORITIES = array(
        'Urgent' => 1,
        'Normal' => 2,
        'Déjà effectué' => 3
    );                                                                          // @todo Translate
    public static $FREQTYPES = array(
        0 => 'months',
        1 => 'weeks',
        2 => 'days'
    );
    public static $NOTYPE = 'Sans type';

    /*
     * Renvoie un rowset de tous les travaux
     */
    public function getAll() {
        $req = $this->select()->from(
                array('w' => 'works'), array('w.id', 'w.title', 'w.prio', 'w.date_creation'));
        return $this->fetchAll($req);
    }
/*
    public function getAllByTypes() {
        $typesTable = new Application_Model_Types();
        $typesWorksA = array();
        $typesWorksA['types'] = array();
        $typesRS = $typesTable->fetchAll();                                     // rowset
        $i = 0;
        foreach ($typesRS as $typeRow) {
            $typesWorksA['types'][$i] = array();
            $typesWorksA['types'][$i]['type'] = array();
            $typesWorksA['types'][$i]['type']['id'] = $typeRow->id;
            $typesWorksA['types'][$i]['type']['name'] = $typeRow->name;
            $typesWorksA['types'][$i]['works'] = array();
            $currentWorks = $this->getWorksByType((int) $typeRow->id);
            if (empty($currentWorks)) {
                continue;
            }
            foreach ($currentWorks as $currentWork) {
                $currentWorkId = $currentWork['work_id'];
                $typesWorksA['types'][$i]['works'][] = $this->getWorkById((int) $currentWorkId);
            }
            $i++;
        }
        return $typesWorksA;
    }
*/
    
    /*
     * Retourne un row de travail spécifique
     */
    public function getWorkById($workId) {
        $select = $this->_db->select()
                ->from(array('w' => 'works'), array('w.id', 'w.title', 'w.prio', 'w.date_creation', 'w.oeuvre_id', 'w.description', 'w.coords_x', 'w.coords_y', 'w.markup', 'w.desc_emplact', 'w.question', 'w.answer', 'w.frequency_months', 'w.frequency_weeks', 'w.frequency_days', 'date_last_done'))
                ->where('w.id = ?', $workId);
        return $this->_db->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
    }
    
    public function getWorkPrio($workId) {
        $select = $this->_db->select()
                ->from(array('w' => 'works'), array('w.prio'))
                ->where('w.id = ?', $workId);
        $row = $this->_db->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);
        return $row['prio'];
    }

    /*
     * Retourne l'ensemble des travaux ordonnés par priorité, puis par type, sous forme d'un tableau
     */
    public function getAllByPrios() {
        $typesTable = new Application_Model_Types();
        $worksTable = new Application_Model_Travaux();
        $priosWorksA = array();
        $priosWorksA['prios'] = array();
        foreach (self::$PRIORITIES as $currentPriorityLabel => $currentPriorityId) {
            // Tous les travaux par priorité, triés par type
            $priosWorksA['prios'][$currentPriorityId] = array();
            $priosWorksA['prios'][$currentPriorityId]['label_prio'] = $currentPriorityLabel;
            $priosWorksA['prios'][$currentPriorityId]['typesWorks'] = $this->getWorksByPrioOrderTypesArray((int) $currentPriorityId);
        }
        return $priosWorksA;
    }

    /*
     * Retourne l'ensemble des travaux ordonnés par type, puis par priorité, sous forme d'un tableau
     */
    public function getAllByTypes() {
        $typesTable = new Application_Model_Types();
        $types = $typesTable->fetchAll();
        $typesWorksA = array();
        $typesWorksA['types'] = array();
        $tIdx = 0;
        foreach($types as $currentType) {
            $typesWorksA['types'][$tIdx] = array();
            $typesWorksA['types'][$tIdx]['label_type'] = $currentType->name;
            $typesWorksA['types'][$tIdx]['priosWorks'] = $this->getWorksByTypeOrderByPriosArray((int) $currentType->id);
            $tIdx++;
        }
        $typesWorksA['types'][$tIdx] = array();                                 // Oeuvres sans type
        $typesWorksA['types'][$tIdx]['label_type'] = self::$NOTYPE;
        $typesWorksA['types'][$tIdx]['priosWorks'] = $this->getWorksByTypeOrderByPriosArray();
        return $typesWorksA;
    }
    
    /*
     * Retourne une liste de travaux d'un certain type, ordonnés par priorités, sous forme d'un tableau
     */
    private function getWorksByTypeOrderByPriosArray($typeId = null) {
        if(isset($typeId))
            $worksRows = $this->getWorksByTypeOrderByPrios($typeId);
        else
            $worksRows = $this->getWorksWithoutTypeOrderByPrios ();
        $priosWorksA = array();
        $pIdx = -1;
        $wIdx = -1;
        $currentPrioId = 0;
        foreach($worksRows as $currentWork) {                                   // Pour chaque travail ordonné par type
            if($currentPrioId != $currentWork['prio']) {                        // Si on est plus dans la meme priorité que le travail précédent
                $pIdx++;
                $wIdx = 0;
                $currentPrioId = $currentWork['prio'];
                $priosWorksA[$pIdx] = array();
                $prio_label = array_search($currentWork['prio'], self::$PRIORITIES);
                $priosWorksA[$pIdx]['prio_label'] = $prio_label;
                $priosWorksA[$pIdx]['prio_id'] = $currentWork['prio'];
                $priosWorksA[$pIdx]['works'] = array();
                $priosWorksA[$pIdx]['works'][$wIdx] = array();
            } else {
                $wIdx++;
            }
            $priosWorksA[$pIdx]['works'][$wIdx]['id'] = $currentWork['id'];
            $priosWorksA[$pIdx]['works'][$wIdx]['title'] = $currentWork['title'];
            $priosWorksA[$pIdx]['works'][$wIdx]['date_creation'] = $currentWork['date_creation'];
            if(!empty($currentWork['oeuvre_id'])) {                         // On a une oeuvre
                $oeuvresTable = new Application_Model_Oeuvres();
                $oeuvreBasic = $oeuvresTable->getOeuvreBasics($currentWork['oeuvre_id']);
                $priosWorksA[$pIdx]['works'][$wIdx]['oeuvre_title'] = $oeuvreBasic['title'];
            } else if(!empty($currentWork['coords_x']) && !empty($currentWork['coords_y'])) {
                $priosWorksA[$pIdx]['works'][$wIdx]['coords_x'] = $currentWork['coords_x'];
                $priosWorksA[$pIdx]['works'][$wIdx]['coords_y'] = $currentWork['coords_y'];
            }
        }
        /*
         * OUTPUT
         * 
         *                              $priosWorksA                            Array of Prios
         *                                  [$prioId][]                         Array of mixed - Idx = Priority ID
         *                                      ['prio_label']                  String - Priority Label
         *                                      ['prio_id']                     Int - Priority ID
         *                                      ['works']{}                     Array of Works
         *                                          [$wIdx]{}                   Array of properties
         *                                              ['id']                  String - Work ID
         *                                              ['title']               String - Work Title
         *                                              ['date_creation']       String - Work Creation date
         *                                              ['coords_x']?           String - Work Coords X
         *                                              ['coords_y']?           String - Work Coords Y
         *                                              ['oeuvre_title']?       String - Work related Oeuvre
         *                                  
         */
        return $priosWorksA;
    }
    
    /*
     * Retourne un rowset des travaux sans type
     */
    private function getWorksWithoutTypeOrderByPrios() {
        $subSelect = $this->_db->select()
                ->from(array('wt' => 'works_types'), array('wt.work_id'));
        $req = $this->_db->select()
                ->from(array('w' => 'works', 'wt' => 'works_types'), array('w.id', 'w.title', 'w.date_creation', 'w.prio', 'w.coords_x', 'w.coords_y', 'w.oeuvre_id'))
                ->where('w.id NOT IN ?', $subSelect)
                ->order('w.prio');
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    /*
     * Retourne un rowset de travaux d'un certain type, ordonnés par priorités
     */
    private function getWorksByTypeOrderByPrios($typeId) {
        $typeId = $this->_db->quote($typeId);
        $req = $this->_db->select()
                ->from(array('wt' => 'works_types'), array('w.id', 'w.title', 'w.date_creation', 'w.prio', 'w.coords_x', 'w.coords_y', 'w.oeuvre_id'))
                ->join(array('w' => 'works'), 'w.id = wt.work_id', array())
                ->where('type_id = ?', $typeId)
                ->order('w.prio');
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    /*
     * Retourne un tableau de travaux d'une certaine priorité, ordonnés par type
     */
    private function getWorksByPrioOrderTypesArray($prioId) {
        // Toi qui entres ici, abandonnes tout espoir
        $typesWorksA = array();
        $oeuvresTable = new Application_Model_Oeuvres();
        $worksRows = $this->getWorksByPrioOrderTypes($prioId);
        $currentTypeId = -1;
        $tIdx = -1;                                                             // Commencera a zero et ne sera jamais réinitialisé
        $wIdx = -1;                                                              // Sera remis a zero a chaque nouveau type
        foreach($worksRows as $currentWorkRow) {
            $workId = $currentWorkRow['work_id'];
            if($currentWorkRow['type_id'] != $currentTypeId) {                  // On parcourt un nouveau type
                $tIdx++;
                $wIdx = 0;
                $currentTypeId = $currentWorkRow['type_id'];
                $typesWorksA[$tIdx] = array();
                if($currentWorkRow['type_id'] !== NULL) {                       // Si le travail a un type
                    $typesWorksA[$tIdx]['type'] = array();
                    $typesWorksA[$tIdx]['type']['id'] = $currentTypeId;
                    $typesWorksA[$tIdx]['type']['name'] = $currentWorkRow['type_name'];
                } else {
                    $typesWorksA[$tIdx]['notype'] = true;
                }
                $typesWorksA[$tIdx]['works'] = array();                         // Il faut ajouter cette ligne au tableau, dans l'element type fraichement créé
                $typesWorksA[$tIdx]['works'][$wIdx] = array();
                $typesWorksA[$tIdx]['works'][$wIdx]['id'] = $currentWorkRow['work_id'];
                $typesWorksA[$tIdx]['works'][$wIdx]['title'] = $currentWorkRow['work_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['date_creation'] = $currentWorkRow['date_creation'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_title'] = $currentWorkRow['oeuvre_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x'] = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y'] = $currentWorkRow['coords_y'];
                $wIdx++;
            } else {                                                            // On ajoute le travail dans le type existant
                $wIdx++;
                $typesWorksA[$tIdx]['works'][$wIdx]['id'] = $currentWorkRow['work_id'];
                $typesWorksA[$tIdx]['works'][$wIdx]['title'] = $currentWorkRow['work_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['date_creation'] = $currentWorkRow['date_creation'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_title'] = $currentWorkRow['oeuvre_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x'] = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y'] = $currentWorkRow['coords_y'];
            }
        }
        return $typesWorksA;
    }

    /* 
     * Retourne un rowset de travaux pour une priorité, ordonnés par type
     */
    private function getWorksByPrioOrderTypes($prioId) {
        $prioId = $this->_db->quote($prioId);
        $req = $this->_db->select()
                ->from(array('w' => 'works'), array('w.id as work_id', 'w.title as work_title', 'w.date_creation', 'w.coords_x', 'w.coords_y', 't.id as type_id', 't.name as type_name', 'o.title as oeuvre_title'))
                ->joinLeft(array('wt' => 'works_types'), 'w.id = wt.work_id', array())
                ->joinLeft(array('t' => 'types'), 't.id = wt.type_id', array())
                ->joinLeft(array('o' => 'oeuvres'), 'o.id = w.oeuvre_id', array())
                ->where('w.prio = ?', $prioId)
                ->order(new Zend_Db_Expr('case when type_name is null then 1 else 0 end, type_name'));
        // LEFT JOIN pour récupérer les travaux qui n'ont pas de type
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    /*
     * Change la priorité d'un travail dont l'identifiant est donné
     */
    public function changeWorkPrio($workId, $prio) {
        $this->update(array('prio' => $prio), $this->_db->quoteInto('id = ?', $workId));
    }
    
    /*
     * Définit le travail comme effectué
     */
    public function setWorkDone($workId) {
        $this->update(array('date_last_done' => date('Y-m-d'),'prio' => Application_Model_Travaux::$PRIORITIES['Déjà effectué']), $this->_db->quoteInto('id = ?', $workId));
    }
    
    /*
     * Ajouter un travail depuis des données d'un formulaire
     */
    public function addWork($workData) {
        try {
            $wtype = $workData['worktype'];
            $data = array(
                'date_creation' => date('Y-m-d'),
                'date_update' => date('Y-m-d'),
                'desc_emplact' => $workData['desc_emplacement'],
                'prio' => $workData['prio'],
            );
            if('normal' == $wtype) {
                $data['title'] = $workData['title'];
                $data['description'] = $workData['description'];
            } else if('markup' == $wtype) {
                $data['title'] = $workData['title'];
                $data['description'] = $workData['description'];
                $data['markup'] = true;
            } else if('question' == $wtype) {
                $data['title'] = $workData['title_question'];
                $data['description'] = $workData['description_question'];
                $data['question'] = true;
            }
            else
                throw new Exception('Work type error');
            if(!empty($workData['frequency'])) {
                $freqTypeAr = explode('frequency_', $workData['frequency_type']);
                $freqType = $freqTypeAr[0];
                if(in_array($freqType, self::$FREQTYPES)) {
                    $data['frequency_' . $freqType] = $workData['frequency'];
                }
            }
            if(!empty($workData['oeuvre_id'])) {
                $data['oeuvre_id'] = $workData['oeuvre_id'];
            } else if(!empty($workData['emplacement_coords_x'])) {
                $data['coords_x'] = $workData['emplacement_coords_x'];
                $data['coords_y'] = $workData['emplacement_coords_y'];
            }
            $workId = $this->insert($data);
                                                                                // Ajout des types
            $types = $workData['types'];
            $travauxTypesTable = new Application_Model_TravauxTypes();
            $travauxTypesTable->addWorkTypes($types, $workId);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die();
        }
    }
    
    public function deleteById($id) {
        try {
            $where = $this->_db->quoteInto('id = ?', $id);
            return $this->delete($where);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            echo $ex->getTraceAsString();die();
            return false;
        }
    }

}
