<?php

class Zend_View_Helper_GetUserTile {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function GetUserTile($id, $fname, $lname, $roleName) {
        echo<<<EOT
<div class="ui dropdown blue item">
    {$fname} {$lname} ({$roleName}) <i class="dropdown icon"></i>
    <div id="usertile" class="menu">
        <a class="item">Compte</a>
        <a class="item" href="{$this->_view->url(array('controller'=>'auth', 'action'=>'deconnecter'), null, true)}">Déconnexion</a>
    </div>
    <input type="hidden" name="user-id" id="user-id" value="$id"/>
</div>
EOT;
         /* return<<<EOT
<section id="user-tile"><span class="user">{$fname} {$lname} ({$roleName})</span>
<a class="pure-button button-small discon" href="{$this->_view->url(array('controller'=>'auth', 'action'=>'deconnecter'), null, true)}">Déconnexion</a>
</section>
<input type="hidden" name="user-id" id="user-id" value="$id"/>
EOT;
          * 
          * 
          */
    }
}