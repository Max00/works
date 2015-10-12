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
    
    public function indexAction() {
        $this->_redirect('user/liste/');
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
    
    public function ajouterAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
            $this->_redirect('/travaux/index');
        }
        
        $this->view->title = 'Ajouter un utilisateur';
        $this->view->page = 'add-user';
        $addUserForm = new Application_Form_AddUser();
    
        if ($this->_request->getPost()) {
            // Soumission du formulaire
            $formData = $this->_request->getPost();
            if($addUserForm->isValid($formData)) {
                $role = (int)$formData['role'];
                if($role != Application_Model_Roles::$ROLE_SUPERVISOR && $role != Application_Model_Roles::$ROLE_WORKER ) {
                    $this->_redirect('/travaux/index');
                }
                // Formulaire valide
                $usersTable = new Application_Model_Users();
                $userData['lname'] = $formData['lname'];
                $userData['fname'] = $formData['fname'];
                $userData['mail']  = $formData['mail'];
                $userData['pass'] = sha1($formData['pass']);
                $userData['role_id'] = $role;

                $usersTable->insert($userData);

                $this->view->noticeTemplate = 'user/notices/user-added.phtml';
                $this->_redirect('/user/liste');
            } else {
                // Formulaire non valide
                $this->view->addUserForm = $addUserForm;
            }
        } else {
            // Premier chargement
            $this->view->addUserForm = $addUserForm;
        }

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
            $editUserForm->role->setRequired(false);
            if($editUserForm->isValid($formData)) {
                // Formulaire valide
                $usersTable = new Application_Model_Users();
                $uid = $auth->getIdentity()->id;
                $userData['lname'] = $formData['lname'];
                $userData['fname'] = $formData['fname'];
                $userData['mail']  = $formData['mail'];
                if(!empty($formData['pass'])) {
                    // Si un mot de passe a été rentré
                    $userData['pass'] = sha1($formData['pass']);
                }
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
            // Si l'utilisateur en cours d'édition est l'utilisateur connecté, il ne peut pas changer son rôle
            $editUserForm->removeElement('role');
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
            // Si l'utilisateur en cours d'édition est l'utilisateur connecté, il ne peut pas changer son rôle
            $editUserForm->removeElement('role');
            $this->view->editUserForm = $editUserForm;
        }

        $this->setNotices();
    }

    /*
    Edition d'un utilisateur depuis la liste des utilisateurs ('/user/liste')
     */
    public function editerAction() {
        $auth = Zend_Auth::getInstance();
        
        if(!$auth->hasIdentity() || $auth->getIdentity()->role_id != Application_Model_Roles::$ROLE_SUPERVISOR) {
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
            if($uid == $auth->getIdentity()->id) {
                // Si l'utilisateur courant est en cours de modification, le champ 'role' n'est pas requis.
                // En principe, il ne doit d'ailleurs pas exister dans $_POST
                $editUserForm->role->setRequired(false);
            }
            if($editUserForm->isValid($formData)) {
                // Formulaire valide
                $usersTable = new Application_Model_Users();
                $userData['lname'] = $formData['lname'];
                $userData['fname'] = $formData['fname'];
                $userData['mail']  = $formData['mail'];

                if($uid != $auth->getIdentity()->id) {
                    // Si l'utilisateur modifié n'est pas l'utilisateur courant, on prend en compte un éventuel changement de role
                    $userData['role_id'] = $formData['role'];
                    // Dans le cas d'un changement de WORKER vers SUPERVISOR, on supprime la liste de travaux associée
                    $userEditing = $usersTable->getUserBasics($uid);
                    if($formData['role'] == Application_Model_Roles::$ROLE_SUPERVISOR && $formData['role'] != $userEditing['role_id']) {
                        $travauxTravailleursTable = new Application_Model_TravauxTravailleurs();
                        $travauxTravailleursTable->deleteAllWorksForUser($uid);
                    }
                }
                if(!empty($formData['pass'])) {
                    // Si un mot de passe a été rentré
                    $userData['pass'] = sha1($formData['pass']);
                }
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
            if($uid == $auth->getIdentity()->id) {
                // Si l'utilisateur en cours d'édition est l'utilisateur connecté, il ne peut pas changer son rôle
                $editUserForm->removeElement('role');
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
                'role' => $user['role_id'],
                'uid' => $uid,
            ));
            if($uid == $auth->getIdentity()->id) {
                // Si l'utilisateur en cours d'édition est l'utilisateur connecté, il ne peut pas changer son rôle
                $editUserForm->removeElement('role');
            }
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
            $noticeSession->noticeType = 'confirmation';
            $noticeSession->confirmationType = 'remove_user';
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