<?php

class Zend_View_Helper_GetWorkView {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function GetWorkView() {
        return $this->view->render('travaux/work-view.phtml');
    }
}