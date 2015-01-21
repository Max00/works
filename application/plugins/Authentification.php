<?php

class Application_Plugin_Authentification extends Zend_Controller_Plugin_Abstract {

    public function preDispatch(\Zend_Controller_Request_Abstract $request) {
        $front = Zend_Controller_Front::getInstance();
        $def_ctrl = 'auth';                                                     // Controlleur de base
        $def_actn = 'login';                                                    // Action de base
        $ctrl = $request->getControllerName();                                  // Controlleur courant
        $actn = $request->getActionName();                                      // Action courante
        Zend_Registry::get('logger')->log('CTRL => ' . $ctrl . ', DEF CTRL => ' . $def_ctrl, 6);
        if($ctrl != $def_ctrl) {                                                // Si controleur de base
        
            $auth = Zend_Auth::getInstance();
            if(!$auth->hasIdentity()) {                                         // Si on est pas identifié, on est redirigé sur le controleur de base
                $this->getResponse()->setRedirect($request->getBaseUrl() . '/' . $def_ctrl . '/' . $def_actn);
            }
            else {
                $front = Zend_Controller_Front::getInstance();
                $view = $front->getParam('bootstrap')->getResource('view');     // Bootstrap View
                $acl = $front->getParam('bootstrap')->getResource('acl');     // Bootstrap View
                //$acl = Zend_Registry::get('acl');
                $role = Zend_Auth::getInstance()->getIdentity()->role_id;
                if(Application_Model_Roles::$ROLE_SUPERVISOR == $role)
                    $view->mainMenuTemplateFile = 'main-menu-supervisor.phtml';
                else if(Application_Model_Roles::$ROLE_WORKER == $role)
                    $view->mainMenuTemplateFile = 'main-menu-worker.phtml';
                else {
                    throw new VDF_Exception_RoleException('Lors de l\'affichage du menu principal, le système n\'a pas trouvé le rôle ' . $role);
                }
            }
        }
        
        parent::preDispatch($request);
    }
}