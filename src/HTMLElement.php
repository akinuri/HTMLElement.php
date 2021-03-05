<?php

class HTMLElement {
    
    private static $SELF_CLOSING_TAGS = [ "meta", "input", "img", "link", "base" ];
    
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
        
        if ($children !== null) {
            $this->append($children);
        }
    }
    
    
    public function getAttribute(string $attribute) {
        return $this->attributes[$attribute] ?? null;
    }
    
    
    public function setAttribute(string $attribute, $value = ""): void {
        $this->attributes[$attribute] = $value;
        if ($attribute == "id") {
            $this->id = $value;
        } else if ($attribute == "class") {
            $this->classList = explode(" ", $value);
        }
    }
    
    
    public function removeAttribute(string $attribute): void {
        unset($this->attributes[$attribute]);
        if ($attribute == "id") {
            $this->id = null;
        } else if ($attribute == "class") {
            $this->classList = [];
        }
    }
    
    
    public function append($element): void {
        switch (\gettype($element)) {
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
                } else if (\is_callable($element)) {
                    $this->append( $element() );
                }
                break;
        }
    }
    
    
    public function prepend($element): void {
        switch (gettype($element)) {
            case "string":
            case "integer":
            case "double":
                \array_unshift($this->children, (string) $element);
                break;
            
            case "array":
                foreach ($element as $child) {
                    $this->prepend($child);
                }
                break;
                
            case "object":
                if ($element instanceof HTMLElement) {
                    \array_unshift($this->children, $element);
                } else if (\is_callable($element)) {
                    $this->prepend( $element() );
                }
                break;
        }
    }
    
    
    function innerHMTL(callable $escapeFunction = null): string {
        $innerHMTL = "";
        foreach ($this->children as $child) {
            $type = \gettype($child);
            if ($type == "string") {
                if ($escapeFunction && \is_callable($escapeFunction)) {
                    $innerHMTL .= $escapeFunction($child);
                } else {
                    $innerHMTL .= $child;
                }
            } else if ($type == "object") {
                $innerHMTL .= $child->outerHTML($escapeFunction);
            }
        }
        return $innerHMTL;
    }
    
    
    public function openingTag(): string {
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
        
        if (\in_array($this->tagName, self::$SELF_CLOSING_TAGS)) {
            return "<{$this->tagName}{$attributes} />";
        }
        
        return "<{$this->tagName}{$attributes}>";
    }
    
    
    public function closingTag(): string {
        return "</{$this->tagName}>";
    }
    
    
    public function outerHTML($escapeFunction = null): string {
        return $this->openingTag() . $this->innerHMTL($escapeFunction) . $this->closingTag();
    }
    
    
    public function output($escapeFunction = null): void {
        echo $this->outerHTML($escapeFunction);
    }
    
    
    public function __toString(): string {
        return $this->outerHTML();
    }
    
    
}

/**
 * A wrapper for the HTMLElement for quicker access.
 */
function elem(string $tagName, array $attributes = null, $children = null) {
    return new HTMLElement($tagName, $attributes, $children);
}