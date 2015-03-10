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
        $hash = $authNS->authToken;
        if ($this->getRequest() && $hash == $this->getRequest()->getParam('auth_token')) {
            $q = $this->getParam('q');
            if (empty($q))
                return;
            $oeuvresTable = new Application_Model_Oeuvres();
            $oeuvres = $oeuvresTable->searchOeuvres($q);
            $oeuvresArray = array();
            foreach ($oeuvres as $currentOeuvre) {
                $oeuvresArray[] = array(
                    'Value' => $currentOeuvre['id'],
                    'Text' => $currentOeuvre['title']
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
        $hash = $authNS->authToken;
        if (true || $this->getRequest() && $hash == $this->getRequest()->getParam('auth_token')) {
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

    public function getOeuvreCoordsAction() {
        $authNS = new Zend_Session_Namespace('authToken');
        $hash = $authNS->authToken;
        if ($this->getRequest() && $hash == $this->getRequest()->getParam('auth_token')) {
            $oeuvreId = $this->getRequest()->getParam('oeuvreId');
            if (!empty($oeuvreId)) {
                $coordsAr = array();
                $coordsAr['coords_x'] = '';
                $coordsAr['coords_y'] = '';
                $oeuvresTable = new Application_Model_Oeuvres();
                $coords = $oeuvresTable->getOeuvreCoords($oeuvreId);
                if(!empty($coords['coords_x']) && !empty($coords['coords_y'])) {
                    $xExplode = explode(' ', $coords['coords_x']);
                    $yExplode = explode(' ', $coords['coords_y']);
                    $coordsAr['coords_x'] = substr($xExplode[0], 0, 1) . ' ' . substr($xExplode[0], 1, 2) . '° ' . $xExplode[1] . "' " . $xExplode[2] . "''";
                    $coordsAr['coords_y'] = substr($yExplode[0], 0, 1) . ' ' . substr($yExplode[0], 1, 2) . '° ' . $yExplode[1] . "' " . $yExplode[2] . "''";
                }
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
        $hash = $authNS->authToken;
        if ($this->getRequest() && $hash == $this->getRequest()->getParam('auth_token')) {
            $workId = $this->getRequest()->getParam('workId');
            if(!empty($workId)) {
                $worksTable = new Application_Model_Travaux();
                $workRowset = $worksTable->find($workId);
                $work = $workRowset->current();
                $workAr = array(
                    'title' => $work['title'],
                    'description' => $work['description'],
                    'desc_emplact' => $work['desc_emplact'],
                    'coords_x' => $work['coords_x'],
                    'coords_y' => $work['coords_y'],
                    'prio' => $work['prio'],
                    'markup' => $work['markup'],
                    'question' => $work['question'],
                    'answer' => $work['answer'],
                    'frequency_months' => $work['frequency_months'],
                    'frequency_weeks' => $work['frequency_weeks'],
                    'frequency_days' => $work['frequency_days'],
                    'date_last_done' => $work['date_last_done'],
                );
                if(!empty($work['oeuvre_id'])) {
                    $workAr['oeuvre_id'] = $work['oeuvre_id'];
                    $oeuvresTable = new Application_Model_Oeuvres();
                    $oeuvre = $oeuvresTable->getOeuvreAttrs($work['oeuvre_id'], array('title', 'coords_x', 'coords_y'));
                    $workAr['oeuvre_title'] = $oeuvre['title'];
                    if(!empty($oeuvre['coords_x']) && !empty($oeuvre['coords_y'])) {
                        $xExplode = explode(' ', $oeuvre['coords_x']);
                        $yExplode = explode(' ', $oeuvre['coords_y']);
                        $workAr['coords_x'] = substr($xExplode[0], 0, 1) . ' ' . substr($xExplode[0], 1, 2) . '° ' . $xExplode[1] . "' " . $xExplode[2] . "''";
                        $workAr['coords_y'] = substr($yExplode[0], 0, 1) . ' ' . substr($yExplode[0], 1, 2) . '° ' . $yExplode[1] . "' " . $yExplode[2] . "''";
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
                echo json_encode($workAr);
                return;
            }
        }
        // Invalid token
        echo json_encode(array('error' => 'token'));
        return;
    }
}
