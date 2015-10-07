<?php

/*
 * 
 */

class TravauxController extends Zend_Controller_Action {

    private static $DEFAULT_LIST_ACTION = 'prios';
    protected static $CURRENT_LIST_ACTION = '';

    /*
     * Convertir les coordonnées DMS en XY
     */

    public function convertAction() {
        $oeuvresTable = new Application_Model_Oeuvres();
        $oeuvres = $oeuvresTable->fetchAll();

        foreach ($oeuvres as $oeuvre) {
            $coords_x = explode(' ', $oeuvre['coords_x']);
            $coords_y = explode(' ', $oeuvre['coords_y']);

            if (count($coords_x) > 1 && count($coords_y) > 1) {
                // On part de l'hypothese que toutes les mesures sont en EAST et NORTH
                $dd_coords_x = substr($coords_x[0], 1) + ((($coords_x[1] * 60) + ($coords_x[2])) / 3600);
                $dd_coords_y = substr($coords_y[0], 1) + ((($coords_y[1] * 60) + ($coords_y[2])) / 3600);

//                    var_dump($coords_x);
//                    var_dump($coords_y);
//                    var_dump($dd_coords_x);
//                    var_dump($dd_coords_y);

                $oeuvre->coords_x = $dd_coords_x;
                $oeuvre->coords_y = $dd_coords_y;
                $oeuvre->save();
            }
        }

        die();
    }

    /*
     * Initialisation du controller
     */

    public function init() {
        // Roles
        $acl = new Zend_Acl();
        Zend_Registry::set('acl', $acl);
        $acl->addRole(new Zend_Acl_Role(Application_Model_Roles::$ROLE_WORKER)); // Roles
        $acl->addRole(new Zend_Acl_Role(Application_Model_Roles::$ROLE_SUPERVISOR));
        $acl->addResource(new Zend_Acl_Resource('list_works'));                 // Privileges
        $acl->addResource(new Zend_Acl_Resource('add_work'));
        $acl->addResource(new Zend_Acl_Resource('edit_work'));
        $acl->addResource(new Zend_Acl_Resource('change_work_prio'));
        $acl->addResource(new Zend_Acl_Resource('set_work_done'));
        $acl->addResource(new Zend_Acl_Resource('remove_work'));
        $acl->addResource(new Zend_Acl_Resource('add_work_to_user_list'));
        $acl->addResource(new Zend_Acl_Resource('have_user_list'));
        $acl->addResource(new Zend_Acl_Resource('remove_work_from_list'));
//        $acl->addResource(new Zend_Acl_Resource('have_work_suggestion'));
        $acl->allow(Application_Model_Roles::$ROLE_WORKER, array(
            'list_works',
            'set_work_done',
            'add_work_to_user_list',
            'remove_work_from_list',
            'have_user_list',
//            'have_work_suggestion',
        ));                                                                 // Attribution des privileges
        $acl->allow(Application_Model_Roles::$ROLE_SUPERVISOR, array(
            'list_works',
            'add_work',
            'edit_work',
            'change_work_prio',
            'remove_work',
            'set_work_done',
        ));

        // Récupération de l'identité pour la view
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $roleId = $auth->getIdentity()->role_id;
            $rolesTable = new Application_Model_Roles();
            $role = $rolesTable->getRoleName($roleId);                          // Passage des informations utilisateur a la vue
            $this->view->roleName = $role->label;
            $usersTable = new Application_Model_Users();
            $user = $usersTable->getUserBasics($auth->getIdentity()->id);
            $this->view->lname = $user['lname'];
            $this->view->fname = $user['fname'];
            $this->view->uid = $auth->getIdentity()->id;
            $this->view->roleId = $roleId;
            $this->view->supervisorRoleId = Application_Model_Roles::$ROLE_SUPERVISOR;
            $this->view->workerRoleId = Application_Model_Roles::$ROLE_WORKER;
        }
        
        /* Selon le role, on ne crée pas le même token */
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $ns;
        if($role == Application_Model_Roles::$ROLE_SUPERVISOR) {                // Si on est superviseur
            $ns = 'authTokenSupervisor';
        } else {                                                                // Si on est Worker
            $ns = 'authTokenWorker';
        }
        $authNS = new Zend_Session_Namespace('authToken');
        $authNS->setExpirationSeconds(TOKEN_EXPIRATION_SECS);

        $authNS->$ns = md5(uniqid(rand(), 1));                                  // Token
        
        $this->view->$ns = $authNS->$ns;
    }

    /*
     * Index : redirection vers la liste, type de vue (prio/type) par défaut
     */

    public function indexAction() {
        $viewDefaults = new Zend_Session_Namespace('viewDefaults');
        if (!empty($viewDefaults->worksListMode)) {
            $this->_redirect('travaux/liste/mode/' . $viewDefaults->worksListMode);
        } else {
            $this->_redirect('travaux/liste/mode/' . self::$DEFAULT_LIST_ACTION);
        }
    }

    /*
     * Liste des travaux
     */

    public function listeAction() {
        $this->addGMapsHeadScript();
        $mode = '';
        $viewDefaults = new Zend_Session_Namespace('viewDefaults');

        if (!$this->getRequest()->has('mode')) {
            if (isset($viewDefaults->worksListMode))
                $mode = $viewDefaults->worksListMode;
            else
                $mode = self::$DEFAULT_LIST_ACTION;
            $this->_redirect('travaux/liste/mode/' . $mode);
        } else
            $mode = $this->getRequest()->getParam('mode');

        if($this->getRequest()->has('id')) {
            $workViewId = $this->getRequest()->getParam('id');
            $workViewId = (int)$workViewId;
            if($workViewId) {
                $this->view->workViewId = $workViewId;
            }
        }
        $this->view->title = 'Travaux';
        $this->view->page = 'list';
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'list_works')) {
            $this->view->addWork = $acl->isAllowed($role, 'add_work');
            $this->view->editWork = $acl->isAllowed($role, 'edit_work');
            $this->view->removeWork = $acl->isAllowed($role, 'remove_work');
            $this->view->changeWorkPrio = $acl->isAllowed($role, 'change_work_prio');
            $this->view->setWorkDone = $acl->isAllowed($role, 'set_work_done');
            $this->view->addWorkToUserList = $acl->isAllowed($role, 'add_work_to_user_list');
//            $this->view->haveWorkSuggestion = $acl->isAllowed($role, 'have_work_suggestion');
            $this->view->workDonePrio = Application_Model_Travaux::$PRIORITIES['Déjà effectué'];
            
            
            $travauxTable = new Application_Model_Travaux();

            $this->view->noTypeLabel = Application_Model_Travaux::$NOTYPE;

            $this->setNotices();                                                // Notices
        } else {
            $this->_redirect('/auth/deconnecter');
            // On ne devrait jamais arriver jusqu'ici.
            // C'est juste dans le cas ou on voudrait ajouter un role qui n'a pas
            // l'autorisation de lister les travaux
        }

        $userId = Zend_Auth::getInstance()->getIdentity()->id;
        if ('types' == $mode) { // Not done ! Ne devrait jamais rentrer ici
            $this->view->viewMode = $viewDefaults->worksListMode = 'types';
            $this->view->works = $travauxTable->getAllByTypes($userId);
            $this->_helper->viewRenderer('list-types');
        } else if ('prios' == $mode || self::$DEFAULT_LIST_ACTION == $mode) {
            $this->view->viewMode = $viewDefaults->worksListMode = 'prios';
            $this->view->works = $travauxTable->getAllByPrios($userId);

            /* Not usefull for now 
            $worksSoonScheduled = $travauxTable->getWorksSoonScheduled();
            $worksSoonScheduledSpliced = array(
                'urgent' => array(),
                'normal' => array(),
            );
            foreach($worksSoonScheduled as $currentWorkScheduled) {
                if($currentWorkScheduled['prio'] == Application_Model_Travaux::$PRIORITIES['Important']) {
                    $worksSoonScheduledSpliced['normal'] []= $currentWorkScheduled;
                } else {
                    $worksSoonScheduledSpliced['Important'] []= $currentWorkScheduled;
                }
            }
            $this->view->worksSoonScheduled = $worksSoonScheduledSpliced;
            */
           

            $this->_helper->viewRenderer('liste-prios');
        }
    }

    protected function addGMapsHeadScript() {
        $this->view->headScript()->prependFile('https://maps.googleapis.com/maps/api/js?key=' . GMAPS_V3_API_KEY);
    }

    public function changerPrioAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $workId = (int) $this->getParam('travail');
        $prioId = (int) $this->getParam('prio');
        if ($acl->isAllowed($role, 'change_work_prio') && !empty($workId) && !empty($prioId)) {
            // On a la permission de modifier la priorité, et on a les parametres requis
            $travauxTable = new Application_Model_Travaux();
            $travauxTable->changeWorkPrio($workId, $prioId);
            // Si on passe à la prio "Déjà effectué", on supprime les travaux des listes utilisateurs
            if ($prioId == Application_Model_Travaux::$PRIORITIES['Déjà effectué']) {
                $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
                $travauxTravailleursTable->deleteWorksFromAllCurrentLists($workId);
            }
            $this->_redirect('/travaux/index');
        } else {                                                                  // Si on a pas la permission de modifier la priorité d'un travail, on est renvoyé vers l'index
            $this->_redirect('/travaux/index');
        }
    }

    public function marquerFaitAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'set_work_done')) {
            if ($this->_request->has('id') && (int)$this->_request->getParam('id')) {
                $noticeSession = new Zend_Session_Namespace('notice');
                $workId = (int)$this->_request->getParam('id');
                try {
                    // Marquer le travail comme effectué (prio 3)
                    $worksTable = new Application_Model_Travaux();
                    $oldPrio = $worksTable->getWorkPrio($workId);
                    $worksTable->setWorkDone($workId);
                    // Suppression des listes utilisateurs
                    $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
                    $travauxTravailleursTable->deleteWorksFromAllCurrentLists($workId);
                    // Mettre a jour la table WORKS_WORKERS
                    $worksWorkersTable = new Application_Model_TravauxTravailleurs();
                    $noticeSession = new Zend_Session_Namespace('notice');
                    $oldWorkSession = new Zend_Session_Namespace('oldWork');
                    $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);   // Expiration par défaut
                    $oldWorkSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);  // Expiration par défaut
                    $noticeSession->noticeType = 'cancel';
                    $noticeSession->cancelOperation = 'set_work_done';
                    $oldWorkSession->id = $workId;
                    $oldWorkSession->prio = $oldPrio;
                    $this->_redirect('/travaux/index');
                } catch (Exception $ex) {
                    echo $ex->getTraceAsString();
                }
            } else {
                Zend_Session::namespaceUnset('notice');
            }
        } else {
            $this->_redirect('/travaux/index');
        }
    }

    public function supprimerAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'remove_work')) {                           // Autorisé à supprimer un travail
            try {
                $noticeSession = new Zend_Session_Namespace('notice');
                $id = (int) $this->_request->getParam('id');
                $worksTable = new Application_Model_Travaux();
                $additionalWorkersTable = new Application_Model_IntervenantsExterieurs();
                // Suppression des intervenants extérieurs
                $result = $worksTable->deleteById($id);
                $additionalWorkersTable->delete('work_id = ' . $id);
                if ($result) {
                    $noticeSession->noticeType = 'confirmation';
                    $noticeSession->confirmationType = 'remove';
                } else {
                    throw new Exception();
                }
            } catch (Exception $e) {
                $noticeSession->noticeType = 'error';
                $noticeSession->errorType = 'remove';
            }
        } else {
            Zend_Session::namespaceUnset('notice');
            Zend_Session::namespaceUnset('work');
        }
        $this->_redirect('/travaux/index');                                    // Dans tous les cas, on redirige vers la liste
    }

    public function ajouterAction() {
        $this->addGMapsHeadScript();
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'add_work')) {
            $addWorkForm;
            if ($this->_request->getPost()) {
                $formData = $this->_request->getPost();
                $addWorkForm = new Application_Form_AddWork();
                $addWorkForm->populate($formData);
                $formIsValid = $addWorkForm->isValid($formData);
                $worktype = $this->_request->getParam('worktype');              // On vide les champs qui ne sont plus nécessaires
                if ('question' != $worktype) {                                  // Travail classique ou balisage
                    $addWorkForm->getElement('title_question')->setValue('');
                    $addWorkForm->getElement('title_question')->removeDecorator('Errors'); // Errors reseting : UPDATE 2015-03-17 - TO TEST
                } else {
                    $addWorkForm->getElement('title')->setValue('');
                    $addWorkForm->getElement('title')->removeDecorator('Errors');
                    
                }
                if ($formIsValid) {                        // Form valide
                    $travauxTable = new Application_Model_Travaux();
                    $travauxTable->addWork($formData);

                    $noticeSession = new Zend_Session_Namespace('notice');
                    $noticeSession->noticeType = 'confirmation';
                    $noticeSession->confirmationType = 'add';
                    $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS); // Expiration par défaut
                    $this->_redirect('/travaux/index');
                } else {                                                       // Erreurs
                    // Ajouter les intervenants externes, qui ne font pas partie du FORM initial
                    $curIdx = 0;
                    if(isset($formData['additional-workers']) && count($formData['additional-workers'])) {
                        foreach ($formData['additional-workers'] as $curAdditionalWorker) {
                            $elt = $addWorkForm->getDisplayGroup('additional_workersG')->addElement(new Zend_Form_Element_Hidden(array(
                                'id' => 'prevAddWorker-' . ++$curIdx,
                                'name' => 'prevAddWorker-' . $curIdx,
                                'class' => 'prevAddWorker',
                                'value' => $curAdditionalWorker)));
                        }
                    }
                    $this->view->noticeTemplate = 'index/notices/error-form.phtml';
                }
            } else {
                $addWorkForm = new Application_Form_AddWork();
                $addWorkForm->initToken();                                      // Si on charge le formulaire pour la premiere fois
            }
            $this->view->title = 'Ajouter un travail';
            $this->view->page = 'add-work';
            $this->view->addWorkForm = $addWorkForm;
        } else {                                                                // Si on a pas la permission d'ajouter un travail
            $this->_redirect('/travaux/index');
        }
    }

    public function editerAction() {
        $this->addGMapsHeadScript();
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'edit_work')) {
            $editWorkForm;
            $workId;
            if ($this->getRequest()->has('id'))                                  // S'il manque l'ID du travail qu'on veut modifier...
                $workId = $this->getRequest()->getParam('id');
            else
                $this->_redirect('/travaux/index');                             // ..Retour a l'index
            if ($this->_request->getPost()) {
                $formData = $this->_request->getPost();
                $editWorkForm = new Application_Form_EditWork();
                if ($editWorkForm->isValid($formData)) {                        // Si formulaire valide
                    $travauxTable = new Application_Model_Travaux();
                    // 1. Update works
                    $workData = array(
                        'date_update' => date('Y-m-d'),
                        'prio' => $this->getParam('prio'),
                    );
                    $worksTable = new Application_Model_Travaux();
                    // Récupération des valeurs du formulaire
                    $isQuestion = false;
                    $title = $description = '';
                    if ($this->hasParam('title_question') &&
                            $this->getParam('title_question')) {
                        $isQuestion = true;
                        $workData['question'] = 1;
                    }
                    if ($isQuestion) {
                        $title = $this->getParam('title_question');
                        $description = $this->getParam('description_question');
                    } else {
                        $title = $this->getParam('title');
                        $description = $this->getParam('description');
                    }
                    $workData['title'] = $title;
                    $workData['description'] = $description;

                    if ($this->hasParam('tools') &&
                            $this->getParam('tools')) {
                        $workData['tools'] = $this->getParam('tools');
                    } else {
                        $workData['tools'] = NULL;
                    }
                    if ($this->hasParam('emplacement_coords_x') &&
                            $this->getParam('emplacement_coords_x')) {
                        $workData['coords_x'] = $this->getParam('emplacement_coords_x');
                        $workData['coords_y'] = $this->getParam('emplacement_coords_y');
                    } else {
                        $workData['coords_x'] = NULL;
                        $workData['coords_y'] = NULL;
                    }
                    if ($this->hasParam('oeuvre_id') &&
                            $this->getParam('oeuvre_id')) {
                        $workData['oeuvre_id'] = $this->getParam('oeuvre_id');
                        $workData['coords_x'] = NULL;
                        $workData['coords_y'] = NULL;
                    } else {
                        $workData['oeuvre_id'] = NULL;
                    }
                    if ($this->hasParam('desc_emplacement') &&
                            $this->getParam('desc_emplacement')) {
                        $workData['desc_emplact'] = $this->getParam('desc_emplacement');
                    } else {
                        $workData['desc_emplact'] = NULL;
                    }
                    if ($this->hasParam('frequency_type') &&
                            $this->getParam('frequency_type') &&
                            $this->hasParam('frequency') &&
                            $this->getParam('frequency')) {
                        $frequency = $this->getParam('frequency');
                        switch ($this->getParam('frequency_type')) {
                            case 'weeks': {
                                    $workData['frequency_weeks'] = $frequency;
                                    $workData['frequency_days'] = NULL;
                                    break;
                                }
                            case 'days': {
                                    $workData['frequency_weeks'] = NULL;
                                    $workData['frequency_days'] = $frequency;
                                    break;
                                }
                            default : {
                                    break;
                                }
                        }
                    } else {
                        $workData['frequency_days'] = NULL;
                        $workData['frequency_weeks'] = NULL;
                    }
                    if ($this->hasParam('worktype') && $this->getParam('worktype')) {
                        switch ($this->getParam('worktype')) {
                            case 'normal': {
                                    $workData['markup'] = NULL;
                                    $workData['question'] = NULL;
                                    break;
                                }
                            case 'question': {
                                    $workData['markup'] = NULL;
                                    $workData['question'] = 1;
                                    break;
                                }
                            case 'markup': {
                                    $workData['markup'] = 1;
                                    $workData['question'] = NULL;
                                    break;
                                }
                        }
                    }
                    if ($this->hasParam('date_last_done') && $this->getParam('date_last_done')) {
                        $workData['date_last_done'] = $this->getParam('date_last_done');
                    }

                    // 2. Update works_types
                    $worksTypesTable = new Application_Model_TravauxTypes();
                    $worksTypesTable->removeWorkTypesByWork($workId);
                    // Si on a des types
                    if ($this->hasParam('types') && $this->getParam('types')) {
                        $worksTypesAr = array();
                        foreach ($this->getParam('types') as $curTypeId) {
                            $worksTypesTable->insert(array(
                                'work_id' => $workId,
                                'type_id' => $curTypeId,
                            ));
                        }
                    }

                    // 3. Update additional_workers
                    $intervenantsExterieursTable = new Application_Model_IntervenantsExterieurs();
                    $intervenantsExterieursTable->removeAllByWork($workId);
                    if ($this->hasParam('additional-workers') && $this->getParam('additional-workers')) {
                        $additionalWorkersArray = array();
                        foreach ($this->getParam('additional-workers') as $curAW) {
                            if (!empty($curAW)) {
                                $intervenantsExterieursTable->insert(array(
                                    'work_id' => $workId,
                                    'label' => $curAW,
                                ));
                            }
                        }
                    }

                    $worksTable->update($workData, 'id = ' . $workId);
                    $noticeSession = new Zend_Session_Namespace('notice');
                    $noticeSession->noticeType = 'confirmation';
                    $noticeSession->confirmationType = 'edit';
                    $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS); // Expiration par défaut
                    $this->_redirect('/travaux/index');
                }
            } else {                                                            // Ouverture pour modification
                $editWorkForm = new Application_Form_EditWork();
                // Récupération des données du travail courant
                $travauxTable = new Application_Model_Travaux();
                $work = $travauxTable->getWorkById($workId);
                $worktype;
                if ($work['markup']) {
                    $worktype = 'markup';
                } else if ($work['question']) {
                    $worktype = 'question';
                } else {
                    $worktype = 'normal';
                }
                $this->setFormElements($editWorkForm, array(
                    'worktype' => $worktype,
                    'prio' => $work['prio'],
                ));
                // Normal / Question: different fields
                if (!empty($work['question']) && $work['question']) {
                    $this->setFormElements($editWorkForm, array('title_question' => $work['title']));
                    $this->setFormElements($editWorkForm, array('description_question' => $work['description']));
                } else {
                    $this->setFormElements($editWorkForm, array('title' => $work['title']));
                    $this->setFormElements($editWorkForm, array('description' => $work['description']));
                }
                if (!empty($work['tools']) && $work['tools']) {
                    $this->setFormElements($editWorkForm, array('tools' => $work['tools']));
                }
                if (!empty($work['oeuvre_id'])) {                                // Si on a une oeuvre associee
                    $oeuvreTable = new Application_Model_Oeuvres();
                    $oeuvre = $oeuvreTable->getOeuvreBasics($work['oeuvre_id']);
                    $this->setFormElements($editWorkForm, array(
                        'emplacement' => $oeuvre['numero'] . ' - ' . $oeuvre['title'],
                        'emplacement_coords_x' => $oeuvre['coords_x'],
                        'emplacement_coords_y' => $oeuvre['coords_y'],
                        'oeuvre_id' => $work['oeuvre_id'],
                    ));
                    // Zend_Registry::get('logger')->log(var_export($editWorkForm->getElement('oeuvre_id'), true),6);
                    if (!empty($oeuvre['coords_x'])) {
                        $this->setFormElements($editWorkForm, array(
                            'maponload' => true,
                        ));
                    }
                } else if (!empty($work['coords_x'])) {
                    $this->setFormElements($editWorkForm, array(
                        'emplacement_coords_x' => $work['coords_x'],
                        'emplacement_coords_y' => $work['coords_y'],
                        'maponload' => true));
                }
                // Description de l'emplacement
                if(!empty($work['desc_emplact'])) {
                    $this->setFormElements($editWorkForm, array(
                        'desc_emplacement' => $work['desc_emplact'],
                    ));
                }
                // Recuperer les types
                $travauxTypesTable = new Application_Model_TravauxTypes();
                $workRow = $travauxTable->find($work['id'])->current();
                $rs = $workRow->findDependentRowset('Application_Model_TravauxTypes');
                // Cocher les types associés
                $checkedTypes = array();
                foreach ($rs as $curTravType) {
                    $checkedTypes[] = $curTravType['type_id'];
                }
                $this->setFormElements($editWorkForm, array(
                    'types' => $checkedTypes,
                ));
                // Frequence
                $freqUnit = $freqNumber = '';
                if (!empty($work['frequency_weeks'])) {
                    $freqUnit = 'weeks';
                    $freqNumber = $work['frequency_weeks'];
                }
                if (!empty($work['frequency_days'])) {
                    $freqUnit = 'days';
                    $freqNumber = $work['frequency_days'];
                }
                if (!empty($freqUnit)) {
                    $this->setFormElements($editWorkForm, array(
                        'frequency' => $freqNumber,
                        'frequency_type' => $freqUnit
                    ));
                }
                // Récupération des intervenants externes
                $intervenantsExterieursTable = new Application_Model_IntervenantsExterieurs();
                $intervenantsExterieurs = $intervenantsExterieursTable->getAllByWork($work['id']);
                foreach ($intervenantsExterieurs as $curAdditionalWorker) {
                    $elt = $editWorkForm->addElement(new Zend_Form_Element_Hidden(array(
                        'id' => 'prevAddWorker-' . $curAdditionalWorker['id'],
                        'name' => 'prevAddWorker-' . $curAdditionalWorker['id'],
                        'class' => 'prevAddWorker',
                        'value' => $curAdditionalWorker['label'])));
                }
                // Token
                $editWorkForm->initToken();
            }
            $this->view->title = 'Éditer le travail';
            $this->view->page = 'edit-work';
            $this->view->editWorkForm = $editWorkForm;
        } else {
            $this->_redirect('/travaux/index');
        }
    }

    protected function setFormElements(Zend_Form &$form, array $elements) {
        foreach ($elements as $key => $value) {
            $form->getElement($key)->setValue($value);
        }
    }

    public function cancelWorkDoneAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $oldWorkSession = new Zend_Session_Namespace('oldWork');
        $noticeSession = new Zend_Session_Namespace('notice');
        $workId = (int) $oldWorkSession->id;
        $workPrio = (int) $oldWorkSession->prio;
        if ($acl->isAllowed($role, 'set_work_done') && !empty($workId) && !empty($workPrio)) {
            try {
                $worksTable = new Application_Model_Travaux();
                $worksTable->changeWorkPrio($workId, $workPrio);
                $oldWorkSession->unsetAll();
                $noticeSession->noticeType = 'confirmation';
                $noticeSession->confirmationType = 'cancel';
                $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);   // Expiration par défaut
            } catch (Exception $ex) {
                $noticeSession->noticeType = 'error';
                $noticeSession->errorType = 'cancel';
                $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);   // Expiration par défaut
            }
            $this->_redirect('/travaux/index');
        } else {
            $this->_redirect('/travaux/index');
        }
    }

    public function cancelAddWorkToUListAction() {
        $wWToDeleteSession = new Zend_Session_Namespace('wwToDel');
        $noticeSession = new Zend_Session_Namespace('notice');
        $userId = $wWToDeleteSession->userId;                                  // PK: user_id, work_id, date_added
        $workId = $wWToDeleteSession->workId;
        $dateAdded = $wWToDeleteSession->dateAdded;
        // Suppression du dernier enregistrement par sa clé primaire
        $wwTable = new Application_Model_TravauxTravailleurs();
        $wwTable->deleteById($userId, $workId, $dateAdded);
        $noticeSession->noticeType = 'confirmation';
        $noticeSession->confirmationType = 'cancel-add-ulist';
        $this->_redirect('travaux/index');
    }
/*
 * 
 * NOW AJAX
 * 
    public function ajouterListePersoAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'add_work_to_user_list')) {
            $userId = $workId = null;
            if ($this->getRequest()->has('operateur') && $this->getRequest()->has('travail')) {
                $userId = $this->getRequest()->getParam('operateur');
                $workId = $this->getRequest()->getParam('travail');             // Recuperation des parametres : operateur et travail
            } else {
                $this->_redirect('/travaux/index');
            }
            try {
                $worksWorkersTable = new Application_Model_TravauxTravailleurs();
                // Vérifier si l'enregistrement n'existe pas déjà, avec une date antérieure
                if ($worksWorkersTable->alreadyExists($userId, $workId)) {
                    $noticeSession = new Zend_Session_Namespace('notice');
                    $noticeSession->noticeType = 'error';
                    $noticeSession->errorType = 'work-already-added';
                    $this->_redirect('travaux/index');
                }

                $dateAdded = date('Y-m-d H:i:s');                               // Date d'ajout                
                $worksWorkersTable->insert(array('user_id' => $userId, 'work_id' => $workId, 'date_added' => $dateAdded));
                // Insertion d'une ligne: ajout a la liste perso

                $noticeSession = new Zend_Session_Namespace('notice');          // Possibilité d'annuler
                $wWToDeleteSession = new Zend_Session_Namespace('wwToDel');
                $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);   // Expiration par défaut
                $wWToDeleteSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS); // Expiration par défaut
                $noticeSession->noticeType = 'cancel';
                $noticeSession->cancelOperation = 'add_work_to_ulist';
                $wWToDeleteSession->userId = $userId;                           // PK: user_id, work_id, date_added
                $wWToDeleteSession->workId = $workId;
                $wWToDeleteSession->dateAdded = $dateAdded;
                $this->_redirect('/travaux/index');
            } catch (Exception $ex) {
                echo $ex->getTraceAsString();
            }
        } else {
            $this->_redirect('/travaux/index');
        }
    }
*/
    public function retirerListePersoAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'remove_work_from_list')) {
            $userId = $workId = null;
            if ($this->getRequest()->has('operateur') && $this->getRequest()->has('travail')) {
                $userId = $this->getRequest()->getParam('operateur');
                $workId = $this->getRequest()->getParam('travail');             // Recuperation des parametres : operateur et travail
            } else {
                $this->_redirect('/travaux/index');
            }
            //try {
            $worksWorkersTable = new Application_Model_TravauxTravailleurs();
            $worksWorkersTable->deleteFromUserList($userId, $workId);
            // Les travaux de la liste ont date_added mais pas date_done

            $noticeSession = new Zend_Session_Namespace('notice');          // Possibilité d'annuler
            $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);   // Expiration par défaut
            $noticeSession->noticeType = 'confirmation';
            $noticeSession->confirmationType = 'remove-from-ulist';
            if ($this->getRequest()->has('redirect')) {
                if ($this->getRequest()->getParam('redirect') == 'list')
                    $this->_redirect('/travaux/liste');
            } else
                $this->_redirect('/travaux/liste-perso');
            /* } catch (Exception $ex) {
              echo $ex->getTraceAsString();
              } */
        } else {
            $this->_redirect('/travaux/index');
        }
    }

    static function getWorksListCount($userId) {
        $wwTable = new Application_Model_TravauxTravailleurs();
        return $wwTable->getCountForUser($userId);
    }

    private function setNotices() {
        $noticeSession = new Zend_Session_Namespace('notice');

        if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'cancel') {                        // On propose une operation d'annulation
            $cancelOperation = $noticeSession->cancelOperation;
            switch ($cancelOperation) {
                case 'set_work_done' : {
                        $oldWorkSession = new Zend_Session_Namespace('oldWork');
                        $workId = $oldWorkSession->id;
                        $workPrio = $oldWorkSession->prio;
                        if (!empty($workId) && !empty($workPrio)) {
                            $this->view->noticeTemplate = 'travaux/notices/cancel-work-done.phtml';
                        }
                        break;
                    }
                case 'add_work_to_ulist' : {
                        $wWToDeleteSession = new Zend_Session_Namespace('wwToDel');
                        $this->view->noticeTemplate = 'travaux/notices/cancel-work-added-ulist.phtml';
                        break;
                    }
                default: {
                        break;
                    }
            }
            $noticeSession->unsetAll();
        } else if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'confirmation') {           // On confirme la réalisation d'une opération
            $confirmationType = $noticeSession->confirmationType;
            switch ($confirmationType) {
                case 'cancel': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-cancel.phtml';
                        break;
                    }
                case 'remove': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-remove.phtml';
                        break;
                    }
                case 'edit': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-edit.phtml';
                        break;
                    }
                case 'add': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-add.phtml';
                        break;
                    }
                case 'cancel-add-ulist': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-cancel-add-ulist.phtml';
                        break;
                    }
                case 'remove-from-ulist': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-remove-ulist.phtml';
                        break;
                    }
                default: {
                        $this->view->noticeTemplate = 'index/notices/confirmation-default.phtml';
                        break;
                    }
            }
            $noticeSession->unsetAll();
        } else if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'error') {                  // On signale une erreur
            $errorType = $noticeSession->errorType;
            switch ($errorType) {
                case 'cancel': {
                        $this->view->noticeTemplate = 'index/notices/error-cancel.phtml';
                        break;
                    }
                case 'remove': {
                        $this->view->noticeTemplate = 'travaux/notices/error-remove.phtml';
                        break;
                    }
                case 'work-already-added': {
                        $this->view->noticeTemplate = 'travaux/notices/error-work-already-added.phtml';
                        break;
                    }
                default: {
                        $this->view->noticeTemplate = 'index/notices/error-default.phtml';
                        break;
                    }
            }
            $noticeSession->unsetAll();
        } else if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'yes-no') {                // Demande de confirmation
            $operation = $noticeSession->operation;
            switch ($operation) {
                case 'set-done': {
                        $workSession = new Zend_Session_Namespace('work');
                        $workId = (int) $workSession->id;
                        if (!empty($workId)) {
                            $this->view->noticeTemplate = 'travaux/notices/yes-no-set-done.phtml';
                        }
                        break;
                    }
                case 'remove': {
                        $workSession = new Zend_Session_Namespace('work');
                        $workId = (int) $workSession->id;
                        if (!empty($workId)) {
                            $this->view->noticeTemplate = 'travaux/notices/yes-no-remove.phtml';
                        }
                        break;
                    }
                default: {
                        $this->view->noticeTemplate = 'index/notices/yes-no-default.phtml';
                        break;
                    }
            }
        }
    }

    public function listePersoAction() {
        $this->view->title = 'Ma liste';
        $this->view->page = 'liste-perso';

        $this->addGMapsHeadScript();
        
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if ($acl->isAllowed($role, 'list_works')) {
            $this->view->removeWorkFromList = $acl->isAllowed($role, 'remove_work_from_list');
            $this->view->setWorkDone = $acl->isAllowed($role, 'set_work_done');
            $this->view->workDonePrio = Application_Model_Travaux::$PRIORITIES['Déjà effectué'];
            
            $travauxTable = new Application_Model_Travaux();

            $this->view->noTypeLabel = Application_Model_Travaux::$NOTYPE;      // Travaux sans type

            $this->setNotices();                                                // Notices
        } else {
            $this->_redirect('/auth/deconnecter');
            // On ne devrait jamais arriver jusqu'ici.
            // C'est juste dans le cas ou on voudrait ajouter un role qui n'a pas
            // l'autorisation de lister les travaux
        }

        $userId = $this->view->userId = Zend_Auth::getInstance()->getIdentity()->id;
        
        $this->view->works = $travauxTable->getFromUserAndPrio($userId);
        $this->_helper->viewRenderer('liste-perso');
    }

}
