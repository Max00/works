<?php

class Vdf_Form_Decorator_UIIconButton extends Zend_Form_Decorator_Abstract {
    protected $_format = '<div style="background-color:Red;">%s</div>';
    
    public function render($icon) {
        $element        = $this->getElement();
        $value          = $this->getValue($element);
        $attribs        = $this->getElementAttribs();
        $name           = $element->getFullyQualifiedName();
        $id             = $element->getId();
        $idStr = $id ? ' id="'.$id.'"' : '';
        
        $renderStr = '<div class="ui icon button"'.$idStr.'>' . $value . '<i class="icon '.$icon.'"></i></div>';
        
        $markup = sprintf($this->_format, $renderStr);
        return $markup;
    }
}