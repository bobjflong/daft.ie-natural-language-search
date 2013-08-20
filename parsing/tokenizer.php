<?php

  class Tokenizer {
    
    public $delimeter = " ";
    
    public function __set($property, $value) {
      if (property_exists($this, $property)) {
        $this->$property = $value;
      }
      return $this;
    }
    
    function tokenize($str) {
      return(explode($this->delimeter, $str));
    }
    
  }
