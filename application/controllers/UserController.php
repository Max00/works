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
    }
}