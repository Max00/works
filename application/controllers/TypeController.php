<?php

class TypeController extends Zend_Controller_Action {

    protected function setFormElements(Zend_Form &$form, array $elements) {
        foreach ($elements as $key => $value) {
            $form->getElement($key)->setValue($value);
        }
    }

    public function listeAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Types';
        $this->view->page = 'type-list';

        $typesTable = new Application_Model_Types();
        $types = $typesTable->getAllTypes();
        $this->view->types = $types;

        $this->setNotices();
    }
    
    public function ajouterAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Ajouter un type';
        $this->view->page = 'add-type';
        $addTypeForm = new Application_Form_AddType();
    
        if ($this->_request->getPost()) {

            // Soumission du formulaire
            $formData = $this->_request->getPost();
            if($addTypeForm->isValid($formData)) {

                // Formulaire valide
                $typesTable = new Application_Model_Types();
                $typeData['name'] = $formData['label'];
                $typeData['color'] = $formData['color'];

                $typesTable->insert($typeData);

                $this->view->noticeTemplate = 'type/notices/type-added.phtml';
                $this->_redirect('/type/liste');
            } else {
                // Formulaire non valide
                $this->view->addTypeForm = $addTypeForm;
                if(!empty($formData['color'])) {
                    $this->view->addTypeForm->setColor($formData['color']);
                }
            }
        } else {
            // Premier chargement
            $this->view->addTypeForm = $addTypeForm;
        }

        $this->setNotices();
    }

    /*
    Edition d'un type
     */
    public function editerAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Éditer le type';
        $this->view->page = 'type-edit';
        $editTypeForm = new Application_Form_EditType();
        
        $tid;
        if ($this->_request->has('tid') && (int)$this->_request->getParam('tid')) {
            $tid = (int)$this->_request->getParam('tid');
        } else {
            $this->_redirect('travaux/index');
        }
        
        if($this->_request->has('label')) {

            // Soumission de nouvelles valeurs
            $formData = $this->_request->getPost();
            if($editTypeForm->isValid($formData)) {
                // Formulaire valide
                $typesTable = new Application_Model_Types();
                $typeData['name'] = $formData['label'];
                $typeData['color'] = $formData['color'];

                $where = $typesTable->getAdapter()->quoteInto('id = ?', $tid);
                $typesTable->update($typeData, $where);

                $this->view->noticeTemplate = 'type/notices/type-edited.phtml';
            } else {
                // Formulaire non valide
            }
            $this->view->editTypeForm = $editTypeForm;
            $this->view->editTypeForm->setColor($formData['color']);
        } else {
            // Ouverture pour modification
            $typesTable = new Application_Model_Types();
            $type = $typesTable->getTypeBasics($tid);
            $this->setFormElements($editTypeForm, array(
                'label' => $type['name'],
                'tid' => $tid,
            ));
            $this->view->editTypeForm = $editTypeForm;
            $this->view->editTypeForm->setColor($type['color']);
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
            $tid = (int) $this->_request->getParam('id');
            $typesTable = new Application_Model_Types();

            $travauxTypesTable = new Application_Model_TravauxTypes();

            $travauxTypesTable->removeWorkTypesByType($tid);
            $typesTable->deleteById($tid);
            $noticeSession->noticeType = 'confirmation';
            $noticeSession->confirmationType = 'remove_type';
        } catch (Exception $e) {
            $noticeSession->noticeType = 'error';
            $noticeSession->errorType = 'remove_type';
        }

        $this->_redirect('/type/liste');
    }


    private function setNotices() {
        $noticeSession = new Zend_Session_Namespace('notice');

        if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'confirmation') {           // On confirme la réalisation d'une opération
            $confirmationType = $noticeSession->confirmationType;
            switch ($confirmationType) {
                case 'remove_type': {
                        $this->view->noticeTemplate = 'type/notices/confirmation-type-removed.phtml';
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
                case 'remove_user': {
                        $this->view->noticeTemplate = 'type/notices/error-remove-type.phtml';
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