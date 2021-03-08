<?php

class HTMLElement {
    
    private static $SELF_CLOSING_TAGS = [ "base", "meta", "link", "input", "img" ];
    
    private $tagName    = null;
    private $id         = null;
    private $classList  = [];
    private $attributes = [];
    private $children   = [];
    
    
    public function __construct(string $tagName, array $attributes = null, $children = null) {
        
        $this->tagName = $tagName;
        
        if (!empty($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        
        if ($children !== null) {
            $this->append($children);
        }
    }
    
    
    
    /* ======================================== ATTRIBUTES ======================================== */
    
    public function getAttribute(string $attribute) {
        return $this->attributes[$attribute] ?? null;
    }
    
    
    public function setAttribute(string $attribute, $value = "") {
        $this->attributes[$attribute] = $value;
        if ($attribute == "id") {
            $this->id = $value;
        } else if ($attribute == "class") {
            $this->classList = explode(" ", $value);
        }
    }
    
    
    public function removeAttribute(string $attribute) {
        unset($this->attributes[$attribute]);
        if ($attribute == "id") {
            $this->id = null;
        } else if ($attribute == "class") {
            $this->classList = [];
        }
    }
    
    
    
    /* ======================================== CLASS ======================================== */
    
    public function addClass(string $class) {
        $class = \explode(" ", $class);
        foreach ($class as $item) {
            if (!\in_array($item, $this->classList)) {
                $this->classList[] = $item;
            }
        }
        $this->syncClassAttribute();
    }
    
    
    public function removeClass(string $class) {
        $class = \explode(" ", $class);
        foreach ($class as $item) {
            if (\in_array($item, $this->classList)) {
                $this->classList = Array2::removeValue($item);
            }
        }
        $this->syncClassAttribute();
    }
    
    
    private function syncClassAttribute() {
        $this->attributes["class"] = \implode(" ", $this->classList);
    }
    
    
    public function hasClass(string $class): bool {
        return \in_array($class, $this->classList);
    }
    
    
    public function getClassList(): array {
        return $this->classList();
    }
    
    
    
    /* ======================================== CHILDREN ======================================== */
    
    public function append($element) {
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
    
    
    public function prepend($element) {
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
    
    
    
    /* ======================================== HTML ======================================== */
    
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
    
    
    public function outerHTML(callable $escapeFunction = null): string {
        return $this->openingTag() . $this->innerHMTL($escapeFunction) . $this->closingTag();
    }
    
    
    
    /* ======================================== OUTPUT ======================================== */
    
    public function output(callable $escapeFunction = null) {
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