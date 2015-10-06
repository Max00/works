<?php

class AjaxController extends Zend_Controller_Action {

    public function init() {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }

    // Dropdown
    public function getOeuvresAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        }
        if ($hash == $pageToken) {
            $q = $this->getParam('q');
            if (empty($q))
                return;
            $oeuvresTable = new Application_Model_Oeuvres();
            $oeuvres = $oeuvresTable->searchOeuvres($q);
            $oeuvresArray = array();
            foreach ($oeuvres as $currentOeuvre) {
                $oeuvresArray[] = array(
                    'Value' => $currentOeuvre['id'],
                    'Text' => $currentOeuvre['numero'] . ' - ' . $currentOeuvre['title']
                );
            }
            echo json_encode($oeuvresArray);
            return;
        }
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }

    // Creation d'un type a la volée
    public function createOnFlyTypeAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        }
        if ($hash == $pageToken) {
            $color = $this->getRequest()->getParam('color');
            $name = $this->getRequest()->getParam('name');
            $colorValidator = new Zend_Validate_Regex(array('pattern' => '/^[a-fA-F0-9]*$/'));
            $nameValidator = new Zend_Validate_Regex(array('pattern' => '/^[a-zA-ZÀ-ÿ0-9 ]*$/'));
            $result = '';
            if (!empty($name) && !empty($color) && $colorValidator->isValid($color) && $nameValidator->isValid($name)) {
                $typesTable = new Application_Model_Types();
                $colorFilter = new Zend_Filter_StringTrim('#');
                $color = $colorFilter->filter($color);
                $nameFilter = new Vdf_Filter_UCFirst();
                $name = $nameFilter->filter($name);
                // On verifie si le type n'existe pas déjà
                $sameNameId = $typesTable->getTypeId($name);
                if ($sameNameId) {
                    $notice = $this->view->render('types/notices/create-info-exists.phtml');
                    $result = array('error' => true, 'typeId' => $sameNameId, 'notice' => $notice);
                } else {
                    $typeId = $typesTable->createType($name, $color);
                    $notice = $this->view->render('types/notices/create-success.phtml');
                    $result = array('success' => true, 'typeId' => $typeId, 'typeName' => $name, 'typeColor' => $color, 'notice' => $notice);
                }
            } else {
                $notice = $this->view->render('types/notices/create-error-form.phtml');
                $result = array('error' => true, 'notice' => $notice);
            }
            echo json_encode($result);
            return;
        }                                                                       // No return: JQuery comprend l'erreur
        echo json_encode(array('error' => 'token'));
        return;
    }
//    
//    public function getWorksAndOeuvresNearBy() {
//        $authNS = new Zend_Session_Namespace('authToken');
//        $hash = $authNS->authToken;
//        if ($this->getRequest() && $hash == $this->getRequest()->getParam('auth_token')) {
//            $workId = $this->getRequest()->getParam('workId');
//            if (!empty($workId)) {
//                
//                echo json_encode($items);
//                return;
//            }
//        }
//    }
    
    public function getOeuvreCoordsAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } else if(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        if ($hash == $pageToken) {
            $oeuvreId = $this->getRequest()->getParam('oeuvreId');
            if (!empty($oeuvreId)) {
                $coordsAr = array();
                $oeuvresTable = new Application_Model_Oeuvres();
                $coords = $oeuvresTable->getOeuvreCoords($oeuvreId);
                $coordsAr['coords_x'] = $coords['coords_x'];
                $coordsAr['coords_y'] = $coords['coords_y'];
//                if(!empty($coords['coords_x']) && !empty($coords['coords_y'])) {
//                    $xExplode = explode(' ', $coords['coords_x']);
//                    $yExplode = explode(' ', $coords['coords_y']);
//                    $coordsAr['coords_x'] = substr($xExplode[0], 0, 1) . ' ' . substr($xExplode[0], 1, 2) . '° ' . $xExplode[1] . "' " . $xExplode[2] . "''";
//                    $coordsAr['coords_y'] = substr($yExplode[0], 0, 1) . ' ' . substr($yExplode[0], 1, 2) . '° ' . $yExplode[1] . "' " . $yExplode[2] . "''";
//                }
                echo json_encode($coordsAr);
                return;
            }
        }
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
//    
//    public function getCoordsForWorkAction() {
//        $authNS = new Zend_Session_Namespace('authToken');
//        $hash = $authNS->authToken;
//        if($this->getRequest() && $hash == $this->getRequest()->getParam('auth_token') ) {
//            $workId = $this->getRequest()->getParam('workId');
//            if(!empty($workId)) {
//                $coordsAr = array();
//                $coordsAr['coords_x'] = '';
//                $coordsAr['coords_y'] = '';
//                $oeuvresTable = new Application_Model_Oeuvres();
//                $worksTable = new Application_Model_Travaux();
//                $work = $worksTable->getWorkById($workId);
//                if(empty($work['oeuvre_id'])) {                                // On a une oeuvre, on renvoie ses coordonnées
//                    $coordsAr['coords_x'] = $work['coords_x'];
//                    $coordsAr['coords_y'] = $work['coords_y'];
//                } else {                                                        // Pas d'oeuvre, on renvoie les coordonnées du travail
//                    $coords = $oeuvresTable->getOeuvreCoords($work['oeuvre_id']);
//                    if(!empty($coords['coords_x']) && !empty($coords['coords_y'])) {
//                        $xExplode = explode(' ', $coords['coords_x']);
//                        $yExplode = explode(' ', $coords['coords_y']);
//                        $coordsAr['coords_x'] = substr($xExplode[0], 0, 1) . ' ' . substr($xExplode[0], 1, 2) . '° ' . $xExplode[1] . "' " . $xExplode[2] . "''";
//                        $coordsAr['coords_y'] = substr($yExplode[0], 0, 1) . ' ' . substr($yExplode[0], 1, 2) . '° ' . $yExplode[1] . "' " . $yExplode[2] . "''";
//                    }
//                }
//                echo json_encode($coordsAr);
//                return;
//            }
//            // Invalid token
//        }
//    }
//    
    public function getWorkDetailsAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } else if(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        if ($hash == $pageToken) {
            $workId = $this->getRequest()->getParam('workId');
            if(!empty($workId)) {
                $worksTable = new Application_Model_Travaux();
                $workRowset = $worksTable->find($workId);
                $work = $workRowset->current();
                $workAr = array(
                    'title' => $work['title'],
                    'description' => $work['description'],
                    'tools' => $work['tools'],
                    'desc_emplact' => $work['desc_emplact'],
                    'coords_x' => $work['coords_x'],
                    'coords_y' => $work['coords_y'],
                    'prio' => $work['prio'],
                    'markup' => $work['markup'],
                    'question' => $work['question'],
                    'answer' => $work['answer'],
                    'frequency_weeks' => $work['frequency_weeks'],
                    'frequency_days' => $work['frequency_days'],
                    'date_last_done' => $work['date_last_done'],
                );
                if(!empty($work['oeuvre_id'])) {
                    $workAr['oeuvre_id'] = $work['oeuvre_id'];
                    $oeuvresTable = new Application_Model_Oeuvres();
                    $oeuvre = $oeuvresTable->getOeuvreBasics($work['oeuvre_id']);
                    $workAr['oeuvre_title'] = $oeuvre['title'];
                    $workAr['oeuvre_numero'] = $oeuvre['numero'];

//                    
//                    if(!empty($oeuvre['coords_x']) && !empty($oeuvre['coords_y'])) {
//                        $xExplode = explode(' ', $oeuvre['coords_x']);
//                        $yExplode = explode(' ', $oeuvre['coords_y']);
//                        $workAr['coords_x'] = substr($xExplode[0], 0, 1) . ' ' . substr($xExplode[0], 1, 2) . '° ' . $xExplode[1] . "' " . $xExplode[2] . "''";
//                        $workAr['coords_y'] = substr($yExplode[0], 0, 1) . ' ' . substr($yExplode[0], 1, 2) . '° ' . $yExplode[1] . "' " . $yExplode[2] . "''";
//                    }
                    
                    if(!empty($oeuvre['coords_x']) && !empty($oeuvre['coords_y'])) {
                        $workAr['coords_x'] = $oeuvre['coords_x'];
                        $workAr['coords_y'] = $oeuvre['coords_y'];
                    }
                }
                                                                                // Récupération des types pour le travail en cours
                $types = $work->findManyToManyRowset('Application_Model_Types', 'Application_Model_TravauxTypes');
                $workAr['types'] = $types->toArray();
                                                                                // Si un utilisateur a ajouté le travail dans sa liste, on renvoie son nom + prenom
                
                
                $wwTable = new Application_Model_TravauxTravailleurs();
                $userId = $wwTable->getUserIdForWorkNotDone($workId);
                if($userId) {
                    $usersTable = new Application_Model_Users();
                    $userRow = $usersTable->getUserBasics($userId);
                    $workAr['user_add'] = $userRow['fname'] . ' ' . $userRow['lname'];
                }
                
                                                                                // Récupération des intervenants extérieurs
                $additionalWorkersTable = new Application_Model_IntervenantsExterieurs();
                $workers = $additionalWorkersTable->getAllByWork($workId);
                $workersToWorkAr = array();
                foreach($workers as $curAW) {
                    $workersToWorkAr []= $curAW['label'];
                }
                $workAr['additional_workers'] = $workersToWorkAr;
                if(isset($workAr['coords_x']) && isset($workAr['coords_y'])) {                    
                    $lat = $workAr['coords_y'];
                    $lon = $workAr['coords_x'];
                    $nearbyItems = $worksTable->getWorksAndOeuvresNearBy($lat,$lon, Application_Model_Travaux::$NEARBY_PERIMETER);
                    // Zend_Registry::get('logger')->log(Zend_Debug::Dump($nearbyItems, null, false), 6);
                    $workAr['nearby'] = array();
                    foreach($nearbyItems as $item) {
                        if($item['id'] != $workId)
                            $workAr['nearby'] []= array('id' => $item['id'], 'title' => $item['work_title'], 'oeuvre_title' => $item['oeuvre_title'], 'distance' => $item['distance']);
                    }
                }
                
                /* Définir si le travail a été ajouté à une liste, et si oui l'id de l'intervenant */
                $worksWorkersTable = new Application_Model_TravauxTravailleurs();
                $userId = $worksWorkersTable->getUserIdForWork($workId);
                if($userId) {
                    $workAr['added'] = true;
                    $workAr['user_id'] = $userId;
                }
                echo json_encode($workAr);
                return;
            }
        }
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }

    public function getWorksStatsAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        // Zend_Registry::get('logger')->log('0', 6);
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        }
        if ($hash == $pageToken) {
            $travauxTable = new Application_Model_Travaux();
            $worksStats = $travauxTable->getWorksStats();
            echo json_encode($worksStats);
            return;
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }

    public function getWorkDaystoAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        }
        if ($hash == $pageToken) {
            $workId = (int)$this->getRequest()->getParam('wid');
            if(!empty($workId)) {
                $travauxTable = new Application_Model_Travaux();
                $workDaysto = $travauxTable->getWorkDaysTo($workId);
                $workDaystoStr = '';
                if($workDaysto > 0) {
                    $workDaystoStr = 'J - ' . abs($workDaysto);
                } elseif($workDaysto == 0) {
                    $workDaystoStr = 'Aujourd\'hui !';
                } else {
                    $workDaystoStr = abs($workDaysto) . ' jours en retard';
                }
                echo json_encode(
                    array(
                        'days_to' => $workDaysto,
                        'str' => $workDaystoStr
                        ));
            }
            return;
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
    
    public function changeWorkPrioAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        }
        if ($hash == $pageToken) {
            $workId = $this->getRequest()->getParam('id');
            $prioId = $this->getRequest()->getParam('p');
            if(!empty($workId) && in_array((int)$prioId, Application_Model_Travaux::$PRIORITIES)) {
                $travauxTable = new Application_Model_Travaux();
                $travauxTable->changeWorkPrio($workId, $prioId);
                echo true;
                return;
            }
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
    
    public function setWorkDoneAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } elseif(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        if ($hash == $pageToken) {
            $workId = $this->getRequest()->getParam('id');
            if(!empty($workId)) {
                $travauxTable = new Application_Model_Travaux();
                $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
                $travauxTravailleursTable->deleteWorksFromAllCurrentLists($workId);
                $travauxTable->setWorkDone($workId);
                echo true;
                return;
            }
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
    
    public function getUlistCountAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } elseif(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        if ($hash == $pageToken) {
            $uid = $this->getRequest()->getParam('uid');
            if(!empty($uid)) {
                $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
                $count = $travauxTravailleursTable->getCountForUser($uid);
                echo json_encode(array('works_count' => $count));
                return;
            }
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
    
    public function addToUlistAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } elseif(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        Zend_Registry::get('logger')->log($pageToken, 6);
        Zend_Registry::get('logger')->log($hash, 6);
        if ($hash == $pageToken) {
            $uid = $this->getRequest()->getParam('uid');
            $wid = $this->getRequest()->getParam('wid');
            if(!empty($uid) && !empty($wid)) {
                
                $worksWorkersTable = new Application_Model_TravauxTravailleurs();
                // Vérifier si l'enregistrement n'existe pas déjà, avec une date antérieure
                if ($worksWorkersTable->alreadyExists($uid, $wid))
                    return;

                $dateAdded = date('Y-m-d H:i:s');                               // Date d'ajout                
                $worksWorkersTable->insert(array('user_id' => $uid, 'work_id' => $wid, 'date_added' => $dateAdded));
                // Insertion d'une ligne: ajout a la liste perso
                echo json_encode(true);
                return;
            }
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
    
    public function removeFromUlistAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash;
        $pageToken = $this->getRequest()->getParam('auth_token');
        if(isset($authNS->authTokenSupervisor)) {
            $hash = $authNS->authTokenSupervisor;
        } elseif(isset($authNS->authTokenWorker)) {
            $hash = $authNS->authTokenWorker;
        }
        if ($hash == $pageToken) {
            $uid = $this->getRequest()->getParam('uid');
            $wid = $this->getRequest()->getParam('wid');
            if(!empty($uid) && !empty($wid)) {
                $worksWorkersTable = new Application_Model_TravauxTravailleurs();
                // Vérifier si l'enregistrement n'existe pas déjà, avec une date antérieure
                if ($worksWorkersTable->alreadyExists($uid, $wid)) {
                    $worksWorkersTable->deleteFromUserList($uid, $wid);
                    echo json_encode(true);
                    return;
                }
                return;
            }
        }
        
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
}
