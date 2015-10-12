<?php

class OeuvreController extends Zend_Controller_Action {

    public function init() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->setIdentityInView();
            $this->view->supervisorRoleId = Application_Model_Roles::$ROLE_SUPERVISOR;
            $this->view->workerRoleId = Application_Model_Roles::$ROLE_WORKER;
        }
    }

    protected function setIdentityInView() {
        $auth = Zend_Auth::getInstance();
        $rolesTable = new Application_Model_Roles();
        $identity = $auth->getIdentity();
        $this->view->lname = $identity->lname;
        $this->view->fname = $identity->fname;
        $this->view->uid = $identity->id;
        $this->view->roleId = $identity->role_id;
        $role = $rolesTable->getRoleName($this->view->roleId);
        $this->view->roleName = $role->label;
    }

    protected function setFormElements(Zend_Form &$form, array $elements) {
        foreach ($elements as $key => $value) {
            $form->getElement($key)->setValue($value);
        }
    }

    public function indexAction() {
        $this->_redirect('oeuvre/liste/');
    }

    public function listeAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Oeuvres';
        $this->view->page = 'oeuvre-list';

        $oeuvresTable = new Application_Model_Oeuvres();
        $oeuvres = $oeuvresTable->getAllOeuvres();
        $this->view->oeuvres = $oeuvres;

        $this->setNotices();
    }
    
    public function ajouterAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Ajouter une oeuvre';
        $this->view->page = 'add-oeuvre';
        $addOeuvreForm = new Application_Form_AddOeuvre();
    
        if ($this->_request->getPost()) {

            // Soumission du formulaire
            $formData = $this->_request->getPost();
            if($addOeuvreForm->isValid($formData)) {

                // Formulaire valide
                $oeuvreTable = new Application_Model_Oeuvres();
                $oeuvreData['title'] = $formData['title'];
                $oeuvreData['artist'] = $formData['artist'];
                $oeuvreData['numero'] = $formData['numero'];
                $oeuvreData['coords_x'] = $formData['coords_x'];
                $oeuvreData['coords_y'] = $formData['coords_y'];

                $oeuvreTable->insert($oeuvreData);

                $this->view->noticeTemplate = 'oeuvre/notices/oeuvre-added.phtml';
                $this->_redirect('/oeuvre/liste');
            } else {
                // Formulaire non valide
                $this->view->addOeuvreForm = $addOeuvreForm;
            }
        } else {
            // Premier chargement
            $this->view->addOeuvreForm = $addOeuvreForm;
        }

        $this->setNotices();
    }

    /*
    Edition d'une oeuvre
     */
    public function editerAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Éditer l\'oeuvre';
        $this->view->page = 'oeuvre-edit';
        $editOeuvreForm = new Application_Form_EditOeuvre();
        
        $oid;
        if ($this->_request->has('oid') && (int)$this->_request->getParam('oid')) {
            $oid = (int)$this->_request->getParam('oid');
        } else {
            $this->_redirect('travaux/index');
        }
        
        if($this->_request->has('title')) {

            // Soumission de nouvelles valeurs
            $formData = $this->_request->getPost();
            if($editOeuvreForm->isValid($formData)) {
                // Formulaire valide
                $oeuvresTable = new Application_Model_Oeuvres();
                $oeuvreData = array();
                $oeuvreData['title'] = $formData['title'];
                $oeuvreData['artist'] = $formData['artist'];
                $oeuvreData['numero'] = $formData['numero'];
                $oeuvreData['coords_x'] = $formData['coords_x'];
                $oeuvreData['coords_y'] = $formData['coords_y'];

                $where = $oeuvresTable->getAdapter()->quoteInto('id = ?', $oid);
                $oeuvresTable->update($oeuvreData, $where);

                $this->view->noticeTemplate = 'oeuvre/notices/oeuvre-edited.phtml';
            } else {
                // Formulaire non valide
            }
            $this->view->editOeuvreForm = $editOeuvreForm;
        } else {
            // Ouverture pour modification
            $oeuvresTable = new Application_Model_Oeuvres();
            $oeuvre = $oeuvresTable->getOeuvreBasics($oid);
            $this->setFormElements($editOeuvreForm, array(
                'title' => $oeuvre['title'],
                'numero' => $oeuvre['numero'],
                'artist' => $oeuvre['artist'],
                'coords_x' => $oeuvre['coords_x'],
                'coords_y' => $oeuvre['coords_y'],
                'oid' => $oid,
            ));
            $this->view->editOeuvreForm = $editOeuvreForm;
        }

        $this->setNotices();
    }

    public function supprimerAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        try {
            $noticeSession = new Zend_Session_Namespace('notice');
            $oid = (int) $this->_request->getParam('id');
            $oeuvresTable = new Application_Model_Oeuvres();

            $travauxTable = new Application_Model_Travaux();
            $travauxTable->removeAllWorksForOeuvre($oid);

            $oeuvresTable->deleteById($oid);

            $noticeSession->noticeType = 'confirmation';
            $noticeSession->confirmationType = 'remove_oeuvre';
        } catch (Exception $e) {
            $noticeSession->noticeType = 'error';
            $noticeSession->errorType = 'remove_oeuvre';
        }

        $this->_redirect('/oeuvre/liste');
    }


    private function setNotices() {
        $noticeSession = new Zend_Session_Namespace('notice');

        if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'confirmation') {           // On confirme la réalisation d'une opération
            $confirmationType = $noticeSession->confirmationType;
            switch ($confirmationType) {
                case 'remove_oeuvre': {
                        $this->view->noticeTemplate = 'oeuvre/notices/confirmation-oeuvre-removed.phtml';
                        break;
                    }
                default: {
                        $this->view->noticeTemplate = 'index/notices/confirmation-default.phtml';
                        break;
                    }
            }
            $noticeSession->unsetAll();
        } elseif(isset($noticeSession->noticeType) && $noticeSession->noticeType == 'error') {
            $errorType = $noticeSession->errorType;
            switch ($errorType) {
                case 'remove_oeuvre': {
                        $this->view->noticeTemplate = 'oeuvre/notices/error-remove-oeuvre.phtml';
                        break;
                    }
                default: {
                        $this->view->noticeTemplate = 'index/notices/error-default.phtml';
                        break;
                    }
            }
            $noticeSession->unsetAll();
        }
    }

}