<?php

namespace Noreh\System\Libraries;

class HTMLElement {
    
    #region ==================== STATIC
    
    private static $ATTRIBUTES = [
        "id", "class", "style", "title", "hidden",
        "src", "alt",
        "href", "target",
        "type", "name", "readonly", "accept", "for", "value", "placeholder", "method", "action", "selected", "disabled", "multiple", "min", "max", "step",
        "content", "rel", "charset", "lang",
        "colspan", "rowspan",
        "onclick",
    ];
    
    private static function isAttributeValid(string $attribute) {
        return \in_array($attribute, self::$ATTRIBUTES)
            || String2::startsWith($attribute, "data-")
            || String2::startsWith($attribute, "on");
    }
    
    private static $SELF_CLOSING_TAGS = ["base", "meta", "link", "input", "img"];
    
    private static $ESCAPE_FUNCTION = null;
    
    #endregion
    
    
    #region ==================== DATA
    
    private $tagName    = null;
    private $id         = null;
    private $classList  = [];
    private $attributes = [];
    private $children   = [];
    private $escapeFunction = null;
    private $ignoreEscape = false;
    
    #endregion
    
    
    #region ==================== CONSTRUCT
    
    public function __construct(
        string $tagName,
        $attributes = null,
        $children = null,
        ?bool $escapeIfPossible = true
    ) {
        
        list(
            $attributes,
            $children,
            $escapeIfPossible
        ) = HTMLElement::swapArguments($attributes, $children, $escapeIfPossible);
        
        $this->ignoreEscape = !$escapeIfPossible;
        
        $this->tagName = $tagName;
        
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
        
        if ($children !== null) {
            $this->append($children);
        }
    }
    
    #endregion
    
    
    #region ==================== SELF
    
    public function getTagName() {
        return $this->tagName;
    }
    
    #endregion
    
    
    #region ==================== ATTRIBUTES
    
    public function getAttribute(string $attribute): ?string {
        return $this->attributes[$attribute] ?? null;
    }
    
    public function setAttribute(string $attribute, $value = "") {
        if (!self::isAttributeValid($attribute)) {
            return;
        }
        if (\is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->escape( (string) $val );
            }
        } else {
            $value = $this->escape( (string) $value );
        }
        $this->attributes[$attribute] = $value;
        if ($attribute == "id") {
            $this->id = $value;
        } else if ($attribute == "class") {
            if (\is_string($value)) {
                $value = \explode(" ", $value);
            }
            $this->classList = $value;
        }
    }
    
    public function setAttributes(array $attributes) {
        if (Array2::isSequential($attributes)) {
            return false;
        }
        foreach ($attributes as $key => $value) {
            if ($key == "+class") {
                $this->addClass($value);
            } else {
                $this->setAttribute($key, $value);
            }
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
    
    public function setId($id) {
        $this->setAttribute("id", $id);
    }
    
    #endregion
    
    
    #region ==================== CLASS
    
    public function addClass(string $class) {
        $class = \explode(" ", $class);
        foreach ($class as $item) {
            if (!\in_array($item, $this->classList)) {
                $item = $this->escape( (string) $item );
                $this->classList[] = $item;
            }
        }
        $this->syncClassAttribute();
    }
    
    public function removeClass(string $class) {
        $class = \explode(" ", $class);
        foreach ($class as $item) {
            if (\in_array($item, $this->classList)) {
                // $this->classList = Array2::removeValue($item);
                Array2::removeValue($this->classList, $item);
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
    
    #endregion
    
    
    #region ==================== CHILDREN
    
    public function getChild(int $n = 0): mixed {
        return $this->children[$n] ?? null;
    }
    
    public function getChildren(): array {
        return $this->children;
    }
    
    public function getChildrenCount(): int {
        return \count($this->children);
    }
    
    public function append() {
        foreach (\func_get_args() as $arg) {
            switch (\gettype($arg)) {
                case "string":
                case "integer":
                case "double":
                    $this->children[] = (string) $arg;
                    break;
                case "array":
                    $this->append(...$arg);
                    break;
                case "object":
                    if ($arg instanceof HTMLElement) {
                        $this->children[] = $arg;
                    } else if (\is_callable($arg)) {
                        $this->append( $arg() );
                    }
                    break;
            }
        }
    }
    
    public function prepend() {
        foreach (\func_get_args() as $arg) {
            switch (\gettype($arg)) {
                case "string":
                case "integer":
                case "double":
                    \array_unshift($this->children, (string) $arg);
                    break;
                case "array":
                    $this->prepend(...$arg);
                    break;
                case "object":
                    if ($arg instanceof HTMLElement) {
                        \array_unshift($this->children, $arg);
                    } else if (\is_callable($arg)) {
                        $this->prepend( $arg() );
                    }
                    break;
            }
        }
    }
    
    #endregion
    
    
    #region ==================== HTML
    
    function innerHMTL(callable $escapeFunction = null): string {
        $escapeFunction = $this->getEscapeFunction($escapeFunction);
        $innerHMTL = "";
        foreach ($this->children as $child) {
            $type = \gettype($child);
            if ($type == "string") {
                if ($escapeFunction) {
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
        
        $alreadyOutputAttributes = ["id", "class"];
        foreach ($this->attributes as $attr => $value) {
            if (!\in_array($attr, $alreadyOutputAttributes)) {
                $attributes .= " {$attr}=\"{$value}\"";
            }
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
        $escapeFunction = $this->getEscapeFunction($escapeFunction);
        return $this->openingTag() . $this->innerHMTL($escapeFunction) . $this->closingTag();
    }
    
    #endregion
    
    
    #region ==================== ESCAPE
    
    public static function setEscapeFunctionGlobally(callable $function) {
        self::$ESCAPE_FUNCTION = $function;
    }
    
    public function setEscapeFunction(callable $function, bool $isGlobal = false) {
        $this->escapeFunction = $function;
    }
    
    public function getEscapeFunction(callable $escapeFunction = null) {
        $function = null;
        if (!$this->ignoreEscape) {
            $function = $escapeFunction ?? $this->escapeFunction ?? self::$ESCAPE_FUNCTION;
            if (!\is_callable($function)) {
                $function = null;
            }
        }
        return $function;
    }
    
    public function escape($value, callable $function = null) {
        $function = $this->getEscapeFunction($function);
        if ($function) {
            $value = $function($value);
        }
        return $value;
    }
    
    #endregion
    
    
    #region ==================== OUTPUT
    
    public function output(callable $escapeFunction = null) {
        echo $this->outerHTML($escapeFunction);
    }
    
    public function __toString(): string {
        return $this->outerHTML();
    }
    
    #endregion
    
    
    #region ==================== ARGS SWAPPING
    
    /**
     * I'm tired of using null as the $attributes, e.g.:
     *  * new HTMLElement("div", null, ["some", "elements"])
     * 
     * Wouldn't it be nice if I could just do:
     *  * new HTMLElement("div", ["some", "elements"])
     * 
     * $attributes expects a non-sequential associative array;
     * so we can use that to differentiate arguments.
     */
    public static function swapArguments(
        $attributes = null,
        $children = null,
        ?bool $escapeIfPossible = true
    ) {
        if (!empty($attributes) && ($children == null || \is_bool($children))) {
            if (
                (\is_array($attributes) &&
                Array2::isSequential($attributes)) || !\is_array($attributes)
            ) {
                $escapeIfPossible = $children;
                $children = $attributes;
                $attributes = null;
            }
        }
        return [$attributes, $children, $escapeIfPossible];
    }
    
    #endregion
    
}

/**
 * A wrapper function for the HTMLElement class for quicker access.
 */
function elem(
    string $tagName,
    $attributes = null,
    $children = null,
    ?bool $escapeIfPossible = true
) {
    list(
        $attributes,
        $children,
        $escapeIfPossible
    ) = HTMLElement::swapArguments($attributes, $children, $escapeIfPossible);
    return new HTMLElement($tagName, $attributes, $children, $escapeIfPossible);
}