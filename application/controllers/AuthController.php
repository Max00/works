<?php

class AuthController extends Zend_Controller_Action {
    
    public function loginAction() {
        $this->view->title = 'Connexion';
        $auth = Zend_Auth::getInstance();
        $loginForm = new Application_Form_Login();                              // formLogin.ini
        $this->view->formLogin = $loginForm;                                    // Passage du formulaire a la vue
        if($this->_request->getPost()) {                                        // Si on a bien recu un $_POST...
            $formData = $this->_request->getPost();
            if($loginForm->isValid($formData)) {                                // Verification du formulaire
                $login = $loginForm->getValue('login');
                $password = $loginForm->getValue('pass');
                $db = Zend_Db_Table_Abstract::getDefaultAdapter();              // Défini automatiquement dans application.ini
                $dbAdapter = new Zend_Auth_Adapter_DbTable($db);
                $dbAdapter->setTableName('users')                               // @todo Dans un fichier de config
                        ->setIdentityColumn('mail')
                        ->setCredentialColumn('pass')
                        ->setCredential($password)
                        ->setIdentity($login);
                $result = $auth->authenticate($dbAdapter);
                if($result->isValid()) {                                        // Verification des identifiants
                    $data = $dbAdapter->getResultRowObject(null, 'pass');       // omission du mot de passe
                    $auth->getStorage()->write($data);                          // Default storage: session
                    $this->_redirect('/travaux');
                } else {
                    $this->view->message = "Identification incorrecte";
                }
            }
        }
    }
    
    public function deconnecterAction() {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        $this->_redirect('/');
    }
}