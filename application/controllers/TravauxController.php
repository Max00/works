<?php

class TravauxController extends Zend_Controller_Action {
    
    private static $DEFAULT_LIST_ACTION = 'prios';
    protected static $CURRENT_LIST_ACTION = '';
    
    public function init() {
        $acl = new Zend_Acl();
        Zend_Registry::set('acl', $acl);
        $acl->addRole(new Zend_Acl_Role(Application_Model_Roles::$ROLE_WORKER));// Roles
        $acl->addRole(new Zend_Acl_Role(Application_Model_Roles::$ROLE_SUPERVISOR));
        $acl->addResource(new Zend_Acl_Resource('list_works'));                 // Privileges
        $acl->addResource(new Zend_Acl_Resource('add_work'));
        $acl->addResource(new Zend_Acl_Resource('edit_work'));
        $acl->addResource(new Zend_Acl_Resource('change_work_prio'));
        $acl->addResource(new Zend_Acl_Resource('set_work_done'));
        $acl->addResource(new Zend_Acl_Resource('remove_work'));
        $acl->allow(Application_Model_Roles::$ROLE_WORKER, array(
            'list_works',
            'set_work_done'
            ));                                                                 // Attribution des privileges
        $acl->allow(Application_Model_Roles::$ROLE_SUPERVISOR, array(
            'list_works',
            'add_work',
            'edit_work',
            'change_work_prio',
            'remove_work',
            'set_work_done',
            ));
        
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity()) {
            $roleId = $auth->getIdentity()->role_id;
            $rolesTable = new Application_Model_Roles();
            $role = $rolesTable->getRoleName($roleId);                          // Passage des informations utilisateur a la vue
            $this->view->roleName = $role->label;
            $this->view->mail = $auth->getIdentity()->mail;
        }
        
        $this->_initGMaps();
    }
    
    public function _initGMaps() {
        if('ajouter' == $this->getRequest()->getActionName()) {
            
            
        }
    }
    
    public function indexAction() {
        $viewDefaults = new Zend_Session_Namespace('viewDefaults');
        if(!empty($viewDefaults->worksListMode)) {
            $this->_redirect('travaux/liste/mode/' . $viewDefaults->worksListMode);
        }
        else {
            $this->_redirect('travaux/liste/mode/' . self::$DEFAULT_LIST_ACTION);
        }
    }
    
    public function listeAction() {
        $mode = '';
        $viewDefaults = new Zend_Session_Namespace('viewDefaults');
        
        if(!$this->getRequest()->has('mode')) {
            if(isset($viewDefaults->worksListMode))
                $mode = $viewDefaults->worksListMode;
            else
                $mode = self::$DEFAULT_LIST_ACTION;
            $this->_redirect('travaux/liste/mode/' . $mode);
        }
        else
            $mode = $this->getRequest()->getParam('mode');
        
        $this->view->title = 'Travaux';
        $this->view->page = 'list';
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if($acl->isAllowed($role, 'list_works')) {
            $this->view->addWork = $acl->isAllowed($role, 'add_work');
            $this->view->editWork = $acl->isAllowed($role, 'edit_work');
            $this->view->removeWork = $acl->isAllowed($role, 'remove_work');
            $this->view->changeWorkPrio = $acl->isAllowed($role, 'change_work_prio');
            $this->view->setWorkDone = $acl->isAllowed($role, 'set_work_done');
            $this->view->workDonePrio = Application_Model_Travaux::$PRIORITIES['Déjà effectué'];
            $authNS = new Zend_Session_Namespace('authToken');
            $authNS->setExpirationSeconds(TOKEN_EXPIRATION_SECS);
            $authNS->authToken = $this->view->auth_token = md5(uniqid(rand(), 1));                                  // Token
            $travauxTable = new Application_Model_Travaux();
            
            $this->view->noTypeLabel = Application_Model_Travaux::$NOTYPE;
            
            $noticeSession = new Zend_Session_Namespace('notice');
            
            if(isset($noticeSession->noticeType) && $noticeSession->noticeType == 'cancel') {                        // On propose une operation d'annulation
                $cancelOperation = $noticeSession->cancelOperation;
                switch($cancelOperation) {
                    case 'set_work_done' : {
                        $oldWorkSession = new Zend_Session_Namespace('oldWork');
                        $workId = $oldWorkSession->id;
                        $workPrio = $oldWorkSession->prio;
                        if(!empty($workId) && !empty($workPrio)) {
                            $this->view->noticeTemplate = 'travaux/notices/cancel-work-done.phtml';
                        }
                        break;
                    }
                    default: {
                        break;
                    }
                }
                $noticeSession->unsetAll();
            } else if(isset($noticeSession->noticeType) && $noticeSession->noticeType == 'confirmation') {           // On confirme la réalisation d'une opération
                $confirmationType = $noticeSession->confirmationType;
                switch($confirmationType) {
                    case 'cancel': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-cancel.phtml';
                        break;
                    }
                    case 'remove': {
                        $this->view->noticeTemplate = 'travaux/notices/confirmation-remove.phtml';
                        break;
                    }
                    default: {
                        $this->view->noticeTemplate = 'index/notices/confirmation-default.phtml';
                        break;
                    }
                }
                $noticeSession->unsetAll();
            } else if(isset($noticeSession->noticeType) && $noticeSession->noticeType == 'error') {                  // On signale une erreur
                $errorType = $noticeSession -> errorType;
                switch($errorType) {
                    case 'cancel': {
                        $this->view->noticeTemplate = 'index/notices/error-cancel.phtml';
                        break;
                    }
                    case 'remove': {
                        $this->view->noticeTemplate = 'travaux/notices/error-remove.phtml';
                        break;
                    }
                    default: {
                        $this->view->noticeTemplate = 'index/notices/error-default.phtml';
                        break;
                    }
                }
                $noticeSession->unsetAll();
            } else if(isset($noticeSession->noticeType) && $noticeSession->noticeType == 'yes-no') {                // Demande de confirmation
                $operation = $noticeSession->operation;
                switch($operation) {
                    case 'remove': {
                        $workSession = new Zend_Session_Namespace('work');
                        $workId = (int)$workSession->id;
                        if(!empty($workId)) {
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
        else {
            $this->_redirect('/auth/deconnecter'); 
            // On ne devrait jamais arriver jusqu'ici.
            // C'est juste dans le cas ou on voudrait ajouter un role qui n'a pas
            // l'autorisation de lister les travaux
        }
        
        if('types' == $mode) {
            $viewDefaults->worksListMode = 'types';
            $this->view->works = $travauxTable->getAllByTypes();
            $this->_helper->viewRenderer('liste-types');
        }
        else if ('prios' == $mode || self::$DEFAULT_LIST_ACTION == $mode) {
            $viewDefaults->worksListMode = 'prios';
            $this->view->works = $travauxTable->getAllByPrios();
            $this->_helper->viewRenderer('liste-prios');
        }
    }
    
    
    public function changerPrioAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $workId = (int) $this->getParam('travail');
        $prioId = (int) $this->getParam('prio');
        if($acl->isAllowed($role, 'change_work_prio') && !empty($workId) && !empty($prioId)) {
            // On a la permission de modifier la priorité, et on a les parametres requis
            $travauxTable = new Application_Model_Travaux();
            $travauxTable->changeWorkPrio($workId, $prioId);
            $this->_redirect('/travaux/index');
        }
        else {                                                                  // Si on a pas la permission de modifier la priorité d'un travail, on est renvoyé vers l'index
            $this->_redirect('/travaux/index');
        }
    }
    
    public function marquerFaitAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $workId = (int)$this->_request->getParam('id');
        if($acl->isAllowed($role, 'set_work_done') && !empty($workId)) {
            try {
                $worksTable = new Application_Model_Travaux();
                $oldPrio = $worksTable->getWorkPrio($workId);
                $worksTable->setWorkDone($workId);
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
            $this->_redirect('/travaux/index');
        }
    }
    
    public function supprimerAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $workId = (int)$this->_request->getParam('id');
        if($acl->isAllowed($role, 'remove_work') && !empty($workId)) {
            $noticeSession = new Zend_Session_Namespace('notice');
            $workSession = new Zend_Session_Namespace('work');
            $noticeSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);
            $workSession->setExpirationSeconds(NOTICE_EXPIRATION_SECS);
            $noticeSession->noticeType = 'yes-no';
            $noticeSession->operation = 'remove';
            $workSession->id = $workId;
            $this->_redirect('/travaux/index');
        } else {
            $this->_redirect('/travaux/index');
        }
    }
    
    public function confirmerSuppressionAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $confirm = (bool)$this->_request->getParam('confirmer');
        if($acl->isAllowed($role, 'remove_work')) {                             // Autorisé à supprimer un travail
            $workSession = new Zend_Session_Namespace('work');
            $noticeSession = new Zend_Session_Namespace('notice');
            $id = (int)$workSession->id;
            if(!empty($confirm) && $confirm && !empty($id)) {
                $worksTable = new Application_Model_Travaux();
                $result = $worksTable->deleteById($id);
                if($result) {
                    $noticeSession->noticeType = 'confirmation';
                    $noticeSession->confirmationType = 'remove';
                } else {
                    $noticeSession->noticeType = 'error';
                    $noticeSession->errorType = 'remove';
                }
            }
            else {
                Zend_Session::namespaceUnset('notice');
                Zend_Session::namespaceUnset('work');
            }
        }
        $this->_redirect('/travaux/index');                                     // Dans tous les cas, on redirige vers la liste
    }
    
    public function ajouterAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        if($acl->isAllowed($role, 'add_work')) {
            $addWorkForm;
            if($this->_request->getPost()) {
                $formData = $this->_request->getPost();
                $addWorkForm = new Application_Form_AddWork();
                if($addWorkForm->isValid($formData)) {
                    $travauxTable = new Application_Model_Travaux();
                    $result = $travauxTable->addWork($formData);
                    if($result) {
                        die ('Okay');
                    } else {
                        die( 'Nope');
                    }
                } else {
                    $worktype = $this->_request->getParam('worktype');          // On vide les champs qui ne sont plus nécessaires
                    if('normal' == $worktype) {
                        $addWorkForm->getElement('title_question')->setValue('');
                        $addWorkForm->getElement('description_question')->setValue('');
                    } else {
                        $addWorkForm->getElement('title')->setValue('');
                        $addWorkForm->getElement('description')->setValue('');
                    }
                    $this->view->noticeTemplate = 'index/notices/error-form.phtml';
                }
            }
            else {
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
    
    public function cancelWorkDoneAction() {
        $acl = Zend_Registry::get('acl');
        $role = Zend_Auth::getInstance()->getIdentity()->role_id;
        $oldWorkSession = new Zend_Session_Namespace('oldWork');
        $noticeSession = new Zend_Session_Namespace('notice');
        $workId = (int)$oldWorkSession->id;
        $workPrio = (int)$oldWorkSession->prio;
        if($acl->isAllowed($role, 'set_work_done') && !empty($workId) && !empty($workPrio)) {
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
}