<?php

class Vdf_Decorator_NewType extends Zend_Form_Decorator_Abstract {
    protected $_format = '<div id="add_type_container">Hahahaha%s</div>';
    
    public function render($content) {
        $markup = sprintf($this->_format, $content);
        return $markup;
    }
}