<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
    
    public function indexAction()
    {
    	// Passage des travaux en mode "Normal" si leur date d'échéance passe en-dessous de 10 jours
    	$travauxTable = new Application_Model_Travaux();
    	// if($travauxTable)

        $this->_redirect('/travaux');                                           // Pour avoir /travaux dans l'url
    }
}