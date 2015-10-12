<?php

class Application_Model_Travaux extends Zend_Db_Table_Abstract {
    protected $_name = 'works';
    protected $_dependentTables = array('Application_Model_TravauxTypes');
    
    public static $PRIORITIES = array(
        'Important' => 1,
        'Normal' => 2,
        'Déjà effectué' => 3
    );                                                                          // @todo Translate
    public static $FREQTYPES = array(
        0 => 'weeks',
        1 => 'days'
    );
    public static $NOTYPE = 'Sans type';
    public static $UNTITLED_WORK = '(Voir les détails)';

    public static $NEARBY_PERIMETER = 400;                                     // Périmètre de proximité, en mètres
    
    public static $DAYS_TO_AUTO_UPDATE = 10;

    /*
     * Renvoie un rowset de tous les travaux
     */
    public function getAll() {
        $req = $this->select()->from(
                array('w' => 'works'), array('w.id', 'w.title', 'w.prio', 'w.date_creation'));
        return $this->fetchAll($req);
    }

    public function SetAutoWorksPrios() {
        $worksToUpdate = $this->getWorksIdsScheduledDaysTo(self::$DAYS_TO_AUTO_UPDATE);
        foreach($worksToUpdate as $curWork) {
            $this->changeWorkPrio($curWork['work_id'], self::$PRIORITIES['Normal']);
        }
    }

    public function removeAllWorksForOeuvre($oeuvreId) {
        $queryStr =<<<EOT
SELECT w.id
FROM works w
WHERE w.oeuvre_id = $oeuvreId;
EOT;
        $workIds = $this->_db->fetchAll($queryStr);
        foreach($workIds as $workId) {
            $this->deleteById($workId->id);
        }
    }

    private function getWorksIdsScheduledDaysTo($daysTo) {
        $queryStr =<<<EOT
SELECT
    `w`.`id` AS `work_id`,
    CASE
        WHEN w.date_last_done IS NULL THEN NULL
        WHEN w.term IS NOT NULL THEN w.term-(DATEDIFF(CURDATE(),w.term_set_on))
        WHEN w.frequency_weeks IS NOT NULL THEN 7*w.frequency_weeks-(DATEDIFF(CURDATE(),w.date_last_done))
        WHEN w.frequency_days IS NOT NULL THEN w.frequency_days-(DATEDIFF(CURDATE(),w.date_last_done))
    END AS `days_to`
FROM `works` AS `w`
HAVING `days_to` <= $daysTo
ORDER BY w.id;
EOT;
        $result = $this->_db->fetchAll($queryStr);
        return $result;
    }

    /*
     * Renvoie les oeuvres et les travaux à proximité d'un point donné, dans un rayon de $perimeter mètres
     */
    public function getWorksAndOeuvresNearBy($startLat, $startLong, $perimeter = null) {
        if($perimeter == null)
            $perimeter = self::$NEARBY_PERIMETER;
        
        $req = "SELECT id, work_title, oeuvre_title, coords_y, coords_x,
ROUND(SQRT(
POW(111200 * (coords_y - $startLat), 2) +
POW(111200 * ($startLong - coords_x) * COS($startLong / 57.3), 2))) AS distance, oeuvre_title
FROM (SELECT w.id as id, w.work_title, w.coords_x, w.coords_y, w.oeuvre_title FROM works_with_coords as w WHERE w.coords_x IS NOT NULL) as w
HAVING distance < $perimeter ORDER BY distance
";
        return $this->_db->fetchAll($req);
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
    
    public function getTotalWorksCount() {
        $queryStr =<<<EOT
SELECT count(w.id) `works_count`
FROM works w;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $worksCount = (int)$result['works_count'];
        return $worksCount;
    }

    public function getWorksStats() {
        /*
            Travaux urgents
            Travaux à réaliser avant 10 jours
            Travaux en retard
            Taux d'attribution
         */
        
        if(0 == $this->getTotalWorksCount()) {
            return ;
        }

        // Nombre de travaux arrivant à échéance avant 10 jours
        $queryStr =<<<EOT
SELECT count(ws.id) AS `10days_or_less`
FROM works ws
WHERE CASE
    WHEN ws.date_last_done IS NULL THEN 0
    WHEN ws.term IS NOT NULL AND ws.term-(DATEDIFF(CURDATE(),ws.term_set_on)) <= 10 THEN 1
    WHEN ws.frequency_weeks IS NOT NULL AND 7*ws.frequency_weeks-(DATEDIFF(CURDATE(),ws.date_last_done)) <= 10 THEN 1
    WHEN ws.frequency_days IS NOT NULL AND ws.frequency_days-(DATEDIFF(CURDATE(),ws.date_last_done)) <= 10 THEN 1
END;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $tenDaysOrLess = $result['10days_or_less'];

        // Nombre de travaux urgents
        $queryStr =<<<EOT
SELECT count(w.id) AS `urgents_count`
FROM works w
WHERE w.prio = 1;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $urgentsCount = $result['urgents_count'];

        // Nombre de travaux en retard
        $queryStr =<<<EOT
SELECT count(ws.id) AS `late_count`
FROM works ws
WHERE CASE
    WHEN ws.date_last_done IS NULL THEN 0
    WHEN ws.term IS NOT NULL AND ws.term-(DATEDIFF(CURDATE(),ws.term_set_on)) < 0 THEN 1
    WHEN ws.frequency_weeks IS NOT NULL AND 7*ws.frequency_weeks-(DATEDIFF(CURDATE(),ws.date_last_done)) < 0 THEN 1
    WHEN ws.frequency_days IS NOT NULL AND ws.frequency_days-(DATEDIFF(CURDATE(),ws.date_last_done)) < 0 THEN 1
END;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $lateCount = $result['late_count'];

        // Attribués
        $queryStr =<<<EOT
SELECT count(w.id) as `affected`
FROM works w
WHERE w.id IN (
    SELECT DISTINCT work_id FROM works_workers
    );
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $affectedCount = (int)$result['affected'];
        // Total des travaux non effectués
        $queryStr =<<<EOT
SELECT count(w.id) as `total`
FROM works w
WHERE w.prio <> 3;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $totalCount = (int)$result['total'];

        if($totalCount > 0 && $affectedCount != 0)
            $affectationRatio = round((100*$affectedCount) / $totalCount);
        else
            $affectationRatio = 0;

        // Attribués urgents
        $queryStr =<<<EOT
SELECT count(w.id) as `affected_urgent`
FROM works w
WHERE w.id IN (
    SELECT DISTINCT work_id FROM works_workers
    )
AND w.prio = 1;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $affectedCountUrgent = (int)$result['affected_urgent'];
        // Total des travaux non effectués et urgents
        $queryStr =<<<EOT
SELECT count(w.id) as `total_urgent`
FROM works w
WHERE w.prio = 1;
EOT;
        $result = $this->_db->fetchRow($queryStr, array(), Zend_Db::FETCH_ASSOC);
        $totalCountUrgent = (int)$result['total_urgent'];

        if($totalCountUrgent > 0)
            $affectationRatioUrgent = round((100*$affectedCountUrgent) / $totalCountUrgent);
        else
            $affectationRatioUrgent = 100;

        return array(
            'urgent_works'             => $urgentsCount,
            'ten_days_or_less_works'   => $tenDaysOrLess,
            'late_works'               => $lateCount,
            'affectation_ratio'        => $affectationRatio,
            'affectation_ratio_urgent' => $affectationRatioUrgent,
            );
    }

    /*
     * Retourne un row de travail spécifique
     */
    public function getWorkById($workId) {
        $select = $this->_db->select()
                ->from(array('w' => 'works'), array('w.id', 'w.title', 'w.tools', 'w.prio', 'w.date_creation', 'w.oeuvre_id', 'w.description', 'w.coords_x', 'w.coords_y', 'w.markup', 'w.desc_emplact', 'w.frequency_weeks', 'w.frequency_days', 'w.term', 'w.term_set_on', 'date_last_done'))
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
    public function getAllByPrios($userId) {
        $typesTable = new Application_Model_Types();
        $worksTable = new Application_Model_Travaux();
        $priosWorksA = array();
        $priosWorksA['prios'] = array();
        foreach (self::$PRIORITIES as $currentPriorityLabel => $currentPriorityId) {
            // Tous les travaux par priorité, triés par type
            $priosWorksA['prios'][$currentPriorityId] = array();
            $priosWorksA['prios'][$currentPriorityId]['label_prio'] = $currentPriorityLabel;
            // $priosWorksA['prios'][$currentPriorityId]['typesWorks'] = $this->getWorksByPrioOrderTypesArray((int) $currentPriorityId, $userId);
            $priosWorksA['prios'][$currentPriorityId]['works'] = $this->getWorksByPrioOrderDateArray((int) $currentPriorityId, $userId);
        }
        return $priosWorksA;
    }
    
    public function getFromUserAndPrio($userId) {
        $typesTable = new Application_Model_Types();
        $worksTable = new Application_Model_Travaux();
        $priosWorksA = array();
        $priosWorksA['prios'] = array();
        foreach (self::$PRIORITIES as $currentPriorityLabel => $currentPriorityId) {
            // Tous les travaux par priorité, triés par type
            $priosWorksA['prios'][$currentPriorityId] = array();
            $priosWorksA['prios'][$currentPriorityId]['label_prio'] = $currentPriorityLabel;
            $priosWorksA['prios'][$currentPriorityId]['typesWorks'] = $this->getWorksForUserByPrioOrderTypesArray($userId, (int) $currentPriorityId);
        }
        return $priosWorksA;
    }
    
    /*
     * Retourne l'ensemble des travaux ordonnés par type, puis par priorité, sous forme d'un tableau
     */
    public function getAllByTypes($userId) {
        $typesTable = new Application_Model_Types();
        $types = $typesTable->fetchAll(null, 'name');
        $typesWorksA = array();
        $typesWorksA['types'] = array();
        $tIdx = 0;
        foreach($types as $currentType) {
            $typesWorksA['types'][$tIdx] = array();
            $typesWorksA['types'][$tIdx]['label_type'] = $currentType->name;
            $typesWorksA['types'][$tIdx]['priosWorks'] = $this->getWorksByTypeOrderByPriosArray($userId, (int) $currentType->id);
            $tIdx++;
        }
        $typesWorksA['types'][$tIdx] = array();                                 // Oeuvres sans type
        $typesWorksA['types'][$tIdx]['label_type'] = self::$NOTYPE;
        $typesWorksA['types'][$tIdx]['priosWorks'] = $this->getWorksByTypeOrderByPriosArray($userId);
        return $typesWorksA;
    }
    
    /*
     * Retourne une liste de travaux d'un certain type, ordonnés par priorités, sous forme d'un tableau
     */
    private function getWorksByTypeOrderByPriosArray($userId, $typeId = null) {
        if(isset($typeId))
            $worksRows = $this->getWorksByTypeOrderByPrios($typeId);
        else
            $worksRows = $this->getWorksWithoutTypeOrderByPrios ();
        $priosWorksA = array();
        $pIdx = -1;
        $wIdx = -1;
        $currentPrioId = 0;
        foreach($worksRows as $currentWork) {                                   // Pour chaque travail ordonné par type
                                                                                // On a une ligne pour chaque travail-type
            if($currentPrioId != $currentWork['prio']) {                        // Si on est plus dans la meme priorité que le travail précédent
                $pIdx++;                                                        // Indice prio ++
                $wIdx = 0;                                                      // Reset indice Works
                $currentPrioId = $currentWork['prio'];  
                $priosWorksA[$pIdx] = array();                                  // Nouveau tableau de prios
                $prio_label = array_search($currentWork['prio'], self::$PRIORITIES);
                $priosWorksA[$pIdx]['prio_label'] = $prio_label;                // Précisions sur les prios
                $priosWorksA[$pIdx]['prio_id'] = $currentWork['prio'];
                $priosWorksA[$pIdx]['works'] = array();                         // Tableau de travaux
                $priosWorksA[$pIdx]['works'][$wIdx] = array();
            } else {
                $wIdx++;
            }
                                                                                // Remplir le travail
            $priosWorksA[$pIdx]['works'][$wIdx]['id'] = $currentWork['id'];
            $priosWorksA[$pIdx]['works'][$wIdx]['title'] = $currentWork['title'];
            $priosWorksA[$pIdx]['works'][$wIdx]['date_creation'] = $currentWork['date_creation'];
            if(!empty($currentWork['oeuvre_id'])) {                             // On a une oeuvre
                $oeuvresTable = new Application_Model_Oeuvres();
                $oeuvreBasic = $oeuvresTable->getOeuvreBasics($currentWork['oeuvre_id']);
                $priosWorksA[$pIdx]['works'][$wIdx]['oeuvre_title'] = $oeuvreBasic['title'];
                $priosWorksA[$pIdx]['works'][$wIdx]['oeuvre_numero'] = $oeuvreBasic['numero'];
            } else if(!empty($currentWork['coords_x']) && !empty($currentWork['coords_y'])) {
                $priosWorksA[$pIdx]['works'][$wIdx]['coords_x'] = $currentWork['coords_x'];
                $priosWorksA[$pIdx]['works'][$wIdx]['coords_y'] = $currentWork['coords_y'];
            }
                                                                                // Association avec un user
            if(empty($currentWork['date_done']) && !empty($currentWork['date_added'])) { // Travail dans la liste d'un utilisateur: ADDED but not DONE
                $priosWorksA[$pIdx]['works'][$wIdx]['added'] = true;
                if($currentWork['user_id'] == $userId) {                     // Travail associé à l'utilisateur courant
                    $priosWorksA[$pIdx]['works'][$wIdx]['cur_user'] = true;
                }
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
    
    public function getWorksSoonScheduled() {
        // Récupérer les travaux selon leur date d'expiration, groupées par degré d'urgence
        $queryStr =<<<EOT
SELECT
    w.id, w.title as `work_title`, w.oeuvre_id, w.prio,
    o.title as `oeuvre_title`, o.numero as `oeuvre_numero`,
    convert(coalesce(w.coords_x,o.coords_x) using utf8) AS coords_x,
    convert(coalesce(w.coords_y,o.coords_y) using utf8) AS coords_y,
    u.fname,
    u.lname,
    DATEDIFF(CURDATE(),w.date_last_done) as `ddif`
from works w
LEFT JOIN works_workers ww on ww.work_id = w.id
LEFT JOIN users u on u.id = ww.user_id
LEFT JOIN oeuvres o on o.id = w.oeuvre_id
ORDER BY w.date_last_done IS NULL ASC, DATEDIFF(CURDATE(), w.date_last_done)
EOT;
        $result = $this->_db->fetchAll($queryStr, array(), Zend_Db::FETCH_ASSOC);
        return $result;
    }

    /*
     * Retourne un rowset des travaux sans type
     */
    private function getWorksWithoutTypeOrderByPrios() {
        $subSelect = $this->_db->select()
                ->from(array('wt' => 'works_types'), array('wt.work_id'));
        $req = $this->_db->select()
                ->from(array('w' => 'works', 'wt' => 'works_types'), array(
                    'w.id', 'w.title', 'w.date_creation', 'w.prio', 'w.coords_x', 'w.coords_y', 'w.oeuvre_id',
                    'ww.date_added as date_added', 'ww.date_done as date_done', 'ww.user_id as user_id'))
                ->joinLeft(array('ww' => 'works_workers'), 'w.id = ww.work_id', array())
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
                ->from(array('wt' => 'works_types'), array(
                    'w.id', 'w.title', 'w.date_creation', 'w.prio', 'w.coords_x', 'w.coords_y', 'w.oeuvre_id',
                    'ww.date_added as date_added', 'ww.date_done as date_done', 'ww.user_id as user_id'))
                ->join(array('w' => 'works'), 'w.id = wt.work_id', array())
                ->joinLeft(array('ww' => 'works_workers'), 'w.id = ww.work_id', array())
                ->where('type_id = ?', $typeId)
                ->order('w.prio');
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    /*
     * Retourne un tableau de travaux d'une certaine priorité, ordonnés par type
     * Pas de factorisation, pour plus de clarté
     */
    private function getWorksByPrioOrderTypesArray($prioId, $userId) {
        // Toi qui entres ici, abandonnes tout espoir
        $typesWorksA = array();
        $oeuvresTable = new Application_Model_Oeuvres();
        $wwTable = new Application_Model_TravauxTravailleurs();
        $worksRows = $this->getWorksByPrioOrderTypes($prioId);
        $currentTypeId = -1;
        $tIdx = -1;                                                             // Commencera a zero et ne sera jamais réinitialisé
        $wIdx = -1;                                                             // Sera remis a zero a chaque nouveau type
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
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_numero'] = $currentWorkRow['oeuvre_numero'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x'] = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y'] = $currentWorkRow['coords_y'];
                if(empty($currentWorkRow['date_done']) && !empty($currentWorkRow['date_added'])) { // Travail dans la liste d'un utilisateur
                    $typesWorksA[$tIdx]['works'][$wIdx]['added'] = true;
                    if($currentWorkRow['user_id'] == $userId) {                 // Travail associé à l'utilisateur courant
                        $typesWorksA[$tIdx]['works'][$wIdx]['cur_user'] = true;
                    }
                }
                $wIdx++;
            } else {                                                            // On ajoute le travail dans le type existant
                $wIdx++;
                $typesWorksA[$tIdx]['works'][$wIdx]['id'] = $currentWorkRow['work_id'];
                $typesWorksA[$tIdx]['works'][$wIdx]['title'] = $currentWorkRow['work_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['date_creation'] = $currentWorkRow['date_creation'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_title'] = $currentWorkRow['oeuvre_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_numero'] = $currentWorkRow['oeuvre_numero'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x'] = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y'] = $currentWorkRow['coords_y'];
                if(empty($currentWorkRow['date_done']) && !empty($currentWorkRow['date_added'])) { // Travail dans la liste d'un utilisateur
                    $typesWorksA[$tIdx]['works'][$wIdx]['added'] = true;
                    if($currentWorkRow['user_id'] == $userId) {                 // Travail associé à l'utilisateur courant
                        $typesWorksA[$tIdx]['works'][$wIdx]['cur_user'] = true;
                    }
                }
            }
        }
        return $typesWorksA;
    }
    
    /*
     * Retourne un tableau de travaux avec les types associés pour chacun
     * Utilisé par liste-prio.phtml
     * Mécanisme :
     *  1. Requete SQL pour avoir une liste des travaux, dans laquelle il y a autant de doublons d'un travail que de types associés à ce travail
     *  2. Création d'un tableau utilisable par la vue liste-prio.phtml
     * 
     * Tableau 1
     * +---------+------------+---------------+----------+----------+---------+-----------+------------+--------------+---------------+------------+-----------+---------+
     * | work_id | work_title | date_creation | coords_x | coords_y | type_id | type_name | type_color | oeuvre_title | oeuvre_numero | date_added | date_done | user_id |
     * +---------+------------+---------------+----------+----------+---------+-----------+------------+--------------+---------------+------------+-----------+---------+
     * 
     * work_id => '',
     * work_title => '',
     * date_creation => '',
     * coords_x => '',
     * coords_y => '',
     * oeuvre_title => '',
     * oeuvre_numero => '',
     * date_added => '',
     * date_done => '',
     * user_id => '',
     * user_fname => '',
     * user_lname => '',
     * 'types' => {
     *      $typeId => {
     *          'name' => name
     *          'color' => color
     *      }
     * }
     * 
     */
    public function getWorksByPrioOrderDateArray ($prioId, $userId) {
        $worksRows = $this->getWorksByPrioOrderTypes($prioId);
        $works = array();
        foreach($worksRows as $curWork) {                                      // Pour chaque Work
            $workId = $curWork['work_id'];
            if(!array_key_exists($curWork['work_id'], $works)) {                // Si le work n'existe pas dans la liste, ajout
                $works[$workId] = array();                                      // Créer la case du work
                if(!empty($curWork['type_id'])) {
                        $works[$workId]['types'] = array();
                }
                                                                                // Écriture des propriétés

                $works[$workId]['title']         = $curWork['work_title'];
                $works[$workId]['date_creation'] = $curWork['date_creation'];
                $works[$workId]['oeuvre_title']  = $curWork['oeuvre_title'];
                $works[$workId]['oeuvre_numero'] = $curWork['oeuvre_numero'];
                $works[$workId]['oeuvre_id']     = $curWork['oeuvre_id'];
                $works[$workId]['coords_x']      = $curWork['coords_x'];
                $works[$workId]['coords_y']      = $curWork['coords_y'];
                $works[$workId]['days_to']       = $curWork['days_to'];
                if(!empty($curWork['user_id'])) {
                    $works[$workId]['user_id'] = $curWork['user_id'];
                    $works[$workId]['user_fname'] = $curWork['user_fname'];
                    $works[$workId]['user_lname'] = $curWork['user_lname'];
                    if(empty($curWork['date_done']) && !empty($curWork['date_added'])) {
                        $works[$workId]['added'] = true;
                        if($curWork['user_id'] == $userId) {
                            $works[$workId]['cur_user'] = true;
                        }
                    }
                }
            }
            if(!empty($curWork['type_id'])) {
                $works[$workId]['types'][$curWork['type_id']] = array(
                    'name' => $curWork['type_name'],
                    'color' => $curWork['type_color']
                        );
            }
        }
        return $works;
    }
    
    public function getWorksForUserByPrioOrderTypesArray($userId, $prioId) {
        $typesWorksA = array();
        $oeuvresTable = new Application_Model_Oeuvres();
        $wwTable = new Application_Model_TravauxTravailleurs();
        $worksRows = $this->getWorksForUserByPrioOrderTypes($userId, $prioId);
        $currentTypeId = -1;
        $tIdx = -1;                                                             // Commencera a zero et ne sera jamais réinitialisé
        $wIdx = -1;                                                             // Sera remis a zero a chaque nouveau type
        foreach($worksRows as $currentWorkRow) {
            $workId = $currentWorkRow['work_id'];
            if($currentWorkRow['type_id'] != $currentTypeId) {                  // On parcourt un nouveau type
                $tIdx++;
                $wIdx = 0;
                $currentTypeId = $currentWorkRow['type_id'];
                $typesWorksA[$tIdx] = array();
                if($currentWorkRow['type_id'] !== NULL) {                       // Si le travail a un type
                    $typesWorksA[$tIdx]['type']          = array();
                    $typesWorksA[$tIdx]['type']['id']    = $currentTypeId;
                    $typesWorksA[$tIdx]['type']['name']  = $currentWorkRow['type_name'];
                    $typesWorksA[$tIdx]['type']['color'] = $currentWorkRow['type_color'];
                } else {
                    $typesWorksA[$tIdx]['notype'] = true;
                }
                $typesWorksA[$tIdx]['works']                         = array();                         // Il faut ajouter cette ligne au tableau, dans l'element type fraichement créé
                $typesWorksA[$tIdx]['works'][$wIdx]                  = array();
                $typesWorksA[$tIdx]['works'][$wIdx]['id']            = $currentWorkRow['work_id'];
                $typesWorksA[$tIdx]['works'][$wIdx]['title']         = $currentWorkRow['work_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['date_creation'] = $currentWorkRow['date_creation'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_title']  = $currentWorkRow['oeuvre_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_numero'] = $currentWorkRow['oeuvre_numero'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x']      = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y']      = $currentWorkRow['coords_y'];
                $wIdx++;
            } else {                                                            // On ajoute le travail dans le type existant
                $wIdx++;
                $typesWorksA[$tIdx]['works'][$wIdx]['id']            = $currentWorkRow['work_id'];
                $typesWorksA[$tIdx]['works'][$wIdx]['title']         = $currentWorkRow['work_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['date_creation'] = $currentWorkRow['date_creation'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_title']  = $currentWorkRow['oeuvre_title'];
                $typesWorksA[$tIdx]['works'][$wIdx]['oeuvre_numero'] = $currentWorkRow['oeuvre_numero'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_x']      = $currentWorkRow['coords_x'];
                $typesWorksA[$tIdx]['works'][$wIdx]['coords_y']      = $currentWorkRow['coords_y'];
            }
        }
        return $typesWorksA;
    }

    /* 
     * Retourne un rowset de travaux pour une priorité, ordonnés par type
     */
    private function getWorksByPrioOrderTypes($prioId) {
        $prioId = $this->_db->quote($prioId);
       /*
        $req = $this->_db->select()
                ->from(array('w' => 'works'), array(
                    'w.id as work_id', 'w.title as work_title', 'w.date_creation', 'w.coords_x', 'w.coords_y',
                    't.id as type_id', 't.name as type_name', 't.color as type_color',
                    'o.title as oeuvre_title', 'o.numero as oeuvre_numero',
                    'ww.date_added as date_added', 'ww.date_done as date_done',
                    'u.id as user_id', 'u.fname as user_fname', 'u.lname as user_lname'))
                ->joinLeft(array('wt' => 'works_types'), 'w.id = wt.work_id', array())
                ->joinLeft(array('t' => 'types'), 't.id = wt.type_id', array())
                ->joinLeft(array('o' => 'oeuvres'), 'o.id = w.oeuvre_id', array())
                ->joinLeft(array('ww' => 'works_workers'), 'ww.work_id = w.id', array())
                ->joinLeft(array('u' => 'users'), 'u.id = ww.user_id', array())
                ->where('w.prio = ?', $prioId)
                // Get the typed works first
                // ->order(new Zend_Db_Expr('case when type_name is null then 1 else 0 end, type_name, work_title'));
                // - OR -
                // Only alphabetical
                ->order(new Zend_Db_Expr('work_title'));
*/
        $req =<<<EOT
SELECT
    `w`.`id` AS `work_id`,
    `w`.`title` AS `work_title`,
    `w`.`date_creation`,
    `w`.`coords_x`,
    `w`.`coords_y`,
    `w`.`term`,
    `w`.`term_set_on`,
    `t`.`id` AS `type_id`,
    `t`.`name` AS `type_name`,
    `t`.`color` AS `type_color`,
    `o`.`id` AS `oeuvre_id`,
    `o`.`title` AS `oeuvre_title`,
    `o`.`numero` AS `oeuvre_numero`,
    `ww`.`date_added`,
    `ww`.`date_done`,
    `u`.`id` AS `user_id`,
    `u`.`fname` AS `user_fname`,
    `u`.`lname` AS `user_lname`,
    CASE
        WHEN w.date_last_done IS NULL THEN NULL
        WHEN w.term IS NOT NULL THEN w.term-(DATEDIFF(CURDATE(),w.term_set_on))
        WHEN w.frequency_weeks IS NOT NULL THEN 7*w.frequency_weeks-(DATEDIFF(CURDATE(),w.date_last_done))
        WHEN w.frequency_days IS NOT NULL THEN w.frequency_days-(DATEDIFF(CURDATE(),w.date_last_done))
    END AS `days_to`
FROM `works` AS `w`
LEFT JOIN `works_types` AS `wt` ON w.id = wt.work_id
LEFT JOIN `types` AS `t` ON t.id = wt.type_id
LEFT JOIN `oeuvres` AS `o` ON o.id = w.oeuvre_id
LEFT JOIN `works_workers` AS `ww` ON ww.work_id = w.id
LEFT JOIN `users` AS `u` ON u.id = ww.user_id
WHERE (w.prio = $prioId)
ORDER BY w.id;

EOT;
//ORDER BY o.id IS NULL ASC, o.id, days_to IS NOT NULL DESC, days_to, t.name
//
// Nombre de jours restant, si w.term :
// Jours restant jusqu'à w.term + w.term_set_on
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    private function getWorksForUserByPrioOrderTypes($userId, $prioId) {
        $prioId = $this->_db->quote($prioId);
        $userId = $this->_db->quote($userId);
        /*
        $req = $this->_db->select()
                ->from(array('w' => 'works', 'ww => works_workers'), array(
                    'w.id as work_id', 'w.title as work_title', 'w.date_creation', 'w.coords_x', 'w.coords_y',
                    't.id as type_id', 't.name as type_name',
                    'o.title as oeuvre_title',
                    'ww.date_added as date_added'))
                ->joinLeft(array('wt' => 'works_types'), 'w.id = wt.work_id', array())
                ->joinLeft(array('t' => 'types'), 't.id = wt.type_id', array())
                ->joinLeft(array('o' => 'oeuvres'), 'o.id = w.oeuvre_id', array())
                ->where('w.prio = ?', $prioId)
                ->where('ww.work_id = w.id')
                ->where($this->_db->quoteInto('ww.user_id = ?', $userId))
                ->order(new Zend_Db_Expr('case when type_name is null then 1 else 0 end, type_name'));
         * */
        $req=<<<EOT
SELECT
`w`.`id` AS `work_id`, `w`.`title` AS `work_title`, `w`.`date_creation`, `w`.`coords_x`, `w`.`coords_y`,
`t`.`id` AS `type_id`, `t`.`name` AS `type_name`, `t`.`color` AS `type_color`,
`o`.`title` AS `oeuvre_title`, `o`.`numero` AS `oeuvre_numero`,
`ww`.`date_added`
FROM `works_workers` AS `ww`,`works` AS `w`
LEFT JOIN `works_types` AS `wt` ON w.id = wt.work_id
LEFT JOIN `types` AS `t` ON t.id = wt.type_id
LEFT JOIN `oeuvres` AS `o` ON o.id = w.oeuvre_id
WHERE (w.prio = {$prioId})
AND (ww.work_id = w.id)
AND (ww.user_id ={$userId})
AND (ww.date_done IS NULL)
ORDER BY case when type_name is null then 1 else 0 end, type_name
EOT;
        //echo $req;
        //die();
        // LEFT JOIN pour récupérer les travaux qui n'ont pas de type
        return $this->_db->fetchAll($req, array(), Zend_Db::FETCH_ASSOC);
    }
    
    /*
     * Change la priorité d'un travail dont l'identifiant est donné
     */
    public function changeWorkPrio($workId, $prio) {
        if($prio == self::$PRIORITIES['Déjà effectué']) {
            // Si on marque le travail comme effectué, on change la valeur de date_last_done
            $this->update(
                array(
                    'prio' => $prio,
                    'date_last_done' => date('Y-m-d'),
                    'term' => NULL,
                    'term_set_on' => NULL,
                    ),
                    $this->_db->quoteInto('id = ?', $workId));
        } else {
            $this->update(
                array('prio' => $prio),
                $this->_db->quoteInto('id = ?', $workId));
        }
    }
    

    public function getWorkDaysTo($workId) {
        $queryStr=<<<EOT
SELECT
    CASE
        WHEN w.date_last_done IS NULL THEN 0
        WHEN w.term IS NOT NULL THEN w.term-(DATEDIFF(CURDATE(),w.term_set_on))
        WHEN w.frequency_weeks IS NOT NULL THEN 7*w.frequency_weeks-(DATEDIFF(CURDATE(),w.date_last_done))
        WHEN w.frequency_days IS NOT NULL THEN w.frequency_days-(DATEDIFF(CURDATE(),w.date_last_done))
    END AS `days_to`
FROM `works` AS `w`
WHERE `w`.`id` = $workId
EOT;

        $result = $this->_db->fetchAll($queryStr, array(), Zend_Db::FETCH_ASSOC);
        return $result[0]['days_to'];
    }

    /*
     * Définit le travail comme effectué
     */ 
    public function setWorkDone($workId) {
        $this->update(
            array(
                'date_last_done' => date('Y-m-d'),
                'prio' => Application_Model_Travaux::$PRIORITIES['Déjà effectué'],
                'term_set_on' => NULL,
                'term' => NULL,
            ),
            $this->_db->quoteInto('id = ?', $workId));
    }
    
    /*
     * Ajouter un travail depuis des données d'un formulaire
     */
    public function addWork($workData) {
        try {
            $wtype = $workData['worktype'];
            $data = array(
                'date_creation'  => date('Y-m-d'),
                'date_last_done' => date('Y-m-d'),
                'date_update'    => date('Y-m-d'),
                'desc_emplact'   => $workData['desc_emplacement'],
                'prio'           => $workData['prio'],
            );
            if('normal' == $wtype) {
                $data['title'] = $workData['title'];
                $data['description'] = $workData['description'];
            } else if('markup' == $wtype) {
                $data['title'] = $workData['title'];
                $data['description'] = $workData['description'];
                $data['markup'] = true;
            }
            else
                throw new Exception('Work type error');
            if(!empty($workData['tools'])) {
                $data['tools'] = $workData['tools'];
            }
            if(!empty($workData['term'])) {
                $data['term'] = $workData['term'];
                $data['term_set_on'] = date('Y-m-d');
            }
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
            if(isset($workData['types'])) {
                $types = $workData['types'];
                $travauxTypesTable = new Application_Model_TravauxTypes();
                $travauxTypesTable->addWorkTypes($types, $workId);
            }
                                                                                // Ajout des intervenant externes
            if(isset($workData['additional-workers'])) {
                $additionalWorkers = $workData['additional-workers'];
                $travauxIE = new Application_Model_IntervenantsExterieurs();
                $travauxIE->addAdditionalWorkers($additionalWorkers, $workId);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die();
        }
    }
    
    public function deleteById($id) {
        try {
            $intervenantsExterieursTable = new Application_Model_IntervenantsExterieurs();
            $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
            $travauxTypesTable = new Application_Model_TravauxTypes();

            $intervenantsExterieursTable->removeAllByWork($id);
            $travauxTravailleursTable->deleteWorksFromAllCurrentLists($id);
            $travauxTypesTable->removeWorkTypesByWork($id);

            $where = $this->_db->quoteInto('id = ?', $id);
            return $this->delete($where);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            return false;
        }
    }
}
