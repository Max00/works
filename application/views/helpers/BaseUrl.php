<?php

class Zend_View_Helper_BaseUrl {
    private $_view;
    
    public function setView($view) {
        $this->_view = $view;
    }
    
    public function BaseUrl($extraPath = '') {
        $fc = Zend_Controller_Front::getInstance();
        $res = preg_replace(':/index.php$:', '', $fc->getBaseUrl() . '/' . $extraPath);
        return $res;
    }
}