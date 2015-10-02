<?php

class UserController extends Zend_Controller_Action {

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

    public function listeAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Utilisateurs du système';
        $this->view->page = 'users-list';

        $usersTable = new Application_Model_Users();
        $users = $usersTable->getAllUsers();
        $this->view->users = $users;

        $this->setNotices();
    }
    
    /**
     * Affiche et traite la page de modification des parametres utilisateur
     */
    public function editAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity()) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Mon compte';
        $this->view->page = 'list';
        $editUserForm = new Application_Form_EditUser();
    
        if ($this->_request->getPost()) {
            // Soumission de nouvelles valeurs
            $formData = $this->_request->getPost();
            if($editUserForm->isValid($formData)) {
                // Formulaire valide
                $usersTable = new Application_Model_Users();
                $uid = $auth->getIdentity()->id;
                $userData['lname'] = $formData['lname'];
                $userData['fname'] = $formData['fname'];
                $userData['mail']  = $formData['mail'];
                $where = $usersTable->getAdapter()->quoteInto('id = ?', $uid);
                $usersTable->update($userData, $where);
                $identity = $auth->getIdentity();
                $identity->lname = $userData['lname'];
                $identity->fname = $userData['fname'];
                $identity->mail = $userData['mail'];
                $auth->clearIdentity();
                $auth->getStorage()->write($identity);
                $this->setIdentityInView();

                $this->view->noticeTemplate = 'user/notices/user-edited.phtml';
            } else {
                // Formulaire non valide
            }
            $this->view->editUserForm = $editUserForm;
        } else {
            // Ouverture pour modification
            $usersTable = new Application_Model_Users();
            $uid = $auth->getIdentity()->id;
            $user = $usersTable->getUserBasics($uid);
            $this->setFormElements($editUserForm, array(
                'fname' => $user['fname'],
                'lname' => $user['lname'],
                'mail' => $user['mail'],
            ));
            $this->view->editUserForm = $editUserForm;
        }

        $this->setNotices();
    }

    public function editerAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity()) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Éditer l\'utilisateur';
        $this->view->page = 'user-edit';
        $editUserForm = new Application_Form_EditUser();
        
        $uid;
        if ($this->_request->has('uid') && (int)$this->_request->getParam('uid')) {
            $uid = (int)$this->_request->getParam('uid');
        } else {
            $this->_redirect('travaux/index');
        }

        $this->_helper->viewRenderer('edit');
        
        if($this->_request->has('lname')) {

            // Soumission de nouvelles valeurs
            $formData = $this->_request->getPost();
            if($editUserForm->isValid($formData)) {
                // Formulaire valide
                $usersTable = new Application_Model_Users();
                $userData['lname'] = $formData['lname'];
                $userData['fname'] = $formData['fname'];
                $userData['mail']  = $formData['mail'];
                $where = $usersTable->getAdapter()->quoteInto('id = ?', $uid);
                $usersTable->update($userData, $where);
                
                
                if($uid == $auth->getIdentity()->id) {
                    $identity = $auth->getIdentity();
                    $identity->lname = $userData['lname'];
                    $identity->fname = $userData['fname'];
                    $identity->mail = $userData['mail'];
                    $auth->clearIdentity();
                    $auth->getStorage()->write($identity);
                    $this->setIdentityInView();
                }

                $this->view->noticeTemplate = 'user/notices/user-edited.phtml';
            } else {
                // Formulaire non valide
            }
            $this->view->editUserForm = $editUserForm;
        } else {
            // Ouverture pour modification
            $usersTable = new Application_Model_Users();
            $user = $usersTable->getUserBasics($uid);
            $this->setFormElements($editUserForm, array(
                'fname' => $user['fname'],
                'lname' => $user['lname'],
                'mail' => $user['mail'],
                'uid' => $uid,
            ));
            $this->view->editUserForm = $editUserForm;
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
            $uid = (int) $this->_request->getParam('id');
            $usersTable = new Application_Model_Users();
            $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();

            $resultW = $travauxTravailleursTable->deleteAllWorksForUser($uid);
            $resultU = $usersTable->deleteById($uid);
            if (!$resultW || !$resultU) {
                $noticeSession->noticeType = 'confirmation';
                $noticeSession->confirmationType = 'remove_user';
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            $noticeSession->noticeType = 'error';
            $noticeSession->errorType = 'remove_user';
        }

        $this->_redirect('/user/liste');
    }


    private function setNotices() {
        $noticeSession = new Zend_Session_Namespace('notice');

        if (isset($noticeSession->noticeType) && $noticeSession->noticeType == 'confirmation') {           // On confirme la réalisation d'une opération
            $confirmationType = $noticeSession->confirmationType;
            switch ($confirmationType) {
                case 'remove_user': {
                        $this->view->noticeTemplate = 'user/notices/confirmation-user-removed.phtml';
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
                        $this->view->noticeTemplate = 'user/notices/error-remove-user.phtml';
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