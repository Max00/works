<?php

class Zend_View_Helper_GetUserTile {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function GetUserTile($mail, $roleName) {
        return<<<EOT
<section id="user-tile"><span class="user">{$mail} ({$roleName})</span>
<a class="pure-button button-small discon" href="{$this->_view->url(array('controller'=>'auth', 'action'=>'deconnecter'))}">Déconnexion</a>
</section>
EOT;
    }
}