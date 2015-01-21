<?php

class Vdf_Filter_UCFirst implements Zend_Filter_Interface {
    public function filter($value) {
        return ucfirst($value);
    }
}