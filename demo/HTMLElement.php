<?php

class HTMLElement {
    
    static $SELF_CLOSING_TAGS = [ "meta", "input", "img", "link", "base" ];
    
    public $tagName    = null;
    public $id         = null;
    public $classList  = [];
    public $attributes = [];
    public $children   = [];
    
    function __construct($tagName = null, $attributes = null, $children = null) {
        if ($tagName) {
            $this->tagName = $tagName;
        }
        
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        
        if ($children) {
            $this->append($children);
        }
    }
    
    public function setAttribute($attr = null, $value = "") {
        if ($attr) {
            $this->attributes[$attr] = $value;
        }
    }
    
    public function append($element = null) {
        if (!$element) return;
        
        switch (gettype($element)) {
            
            case "string":
            case "integer":
            case "double":
                $this->children[] = (string) $element;
                break;
            
            case "array":
                foreach ($element as $child) {
                    $this->append($child);
                }
                break;
                
            case "object":
                if ($element instanceof HTMLElement) {
                    $this->children[] = $element;
                } else if (is_callable($element)) {
                    $this->append( $element() );
                }
                break;
        }
    }
    
    function innerHMTL() {
        $inner_html = "";
        foreach ($this->children as $child) {
            $type = gettype($child);
            if ($type == "string") {
                $inner_html .= $child;
            } else if ($type == "object") {
                $inner_html .= $child->outerHTML();
            }
        }
        return $inner_html;
    }
    
    public function outerHTML() {
        
        $attributes = "";
        
        if ($this->id) {
            $attributes .= " id=\"{$this->id}\"";
        }
        
        if (!empty($this->classList)) {
            $classes     = implode(" ", $this->classList);
            $attributes .= " class=\"{$classes}\"";
        }
        
        foreach ($this->attributes as $attr => $value) {
            $attributes .= " {$attr}=\"{$value}\"";
        }
        
        if (in_array($this->tagName, HTMLElement::$SELF_CLOSING_TAGS)) {
            return "<{$this->tagName}{$attributes} />";
        }
        
        return "<{$this->tagName}{$attributes}>" . $this->innerHMTL() . "</{$this->tagName}>";
    }
    
    public function output() {
        echo $this->outerHTML();
    }
}

// class wrapper for easier access
function elem($tagName = null, $attributes = null, $children = null) {
    return new HTMLElement($tagName, $attributes, $children);
}
