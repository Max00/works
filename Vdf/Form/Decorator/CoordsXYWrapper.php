<?php

class Vdf_Form_Decorator_CoordsXYWrapper extends Zend_Form_Decorator_Abstract {
    protected $_format = '<div id="coords_wrapper_table">%s</div>';
    
    public function render($content) {
        $markup = sprintf($this->_format, $content);
        return $markup;
    }
}