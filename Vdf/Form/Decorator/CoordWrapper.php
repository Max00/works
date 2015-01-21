<?php

class Vdf_Form_Decorator_CoordWrapper extends Zend_Form_Decorator_Abstract {
    protected $_format = '<div id="%s">%s</div>';
    protected $_id;
    
    public function render($content) {
        $markup = sprintf($this->_format, $this->getOption('id'), $content);
        return $markup;
    }
}