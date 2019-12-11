<?php

class HTMLElement {
    
    static $SELF_CLOSING_TAGS = [ "meta", "input", "img", "link", "base" ];
    
    public $tagName    = null;
    public $id         = null;
    public $classList  = [];
    public $attributes = [];
    public $children   = [];
    
    public function __construct(string $tagName, array $attributes = null, $children = null) {
        $this->tagName = $tagName;
        
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        
        if ($children) {
            $this->append($children);
        }
    }
    
    public function setAttribute(string $attribute, $value = "") {
        $this->attributes[$attribute] = $value;
    }
    
    public function append($element) {
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
    
    function innerHMTL(callable $escape_function = null) {
        $inner_html = "";
        foreach ($this->children as $child) {
            $type = gettype($child);
            if ($type == "string") {
                if ($escape_function && is_callable($escape_function)) {
                    $inner_html .= $escape_function($child);
                } else {
                    $inner_html .= $child;
                }
            } else if ($type == "object") {
                $inner_html .= $child->outerHTML($escape_function);
            }
        }
        return $inner_html;
    }
    
    public function outerHTML($escape_function = null) {
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
        
        return "<{$this->tagName}{$attributes}>" . $this->innerHMTL($escape_function) . "</{$this->tagName}>";
    }
    
    public function output($escape_function = null) {
        echo $this->outerHTML($escape_function);
    }
}

// class wrapper for easier access
function elem(string $tagName, array $attributes = null, $children = null) {
    return new HTMLElement($tagName, $attributes, $children);
}
