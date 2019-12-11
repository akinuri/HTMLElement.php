# HTMLElement.php

A class for creating HTML dynamically.

I'm not a fan of mixing PHP with HTML when creating HTML code with lots of PHP variables. It just looks ugly and confusing. You have to pay attention to quotes, (if) blocks, PHP tags, etc. I'd rather generate the HTML with PHP. `HTMLElement` does exactly that.


## Syntax

```php
new HTMLElement(string $tagName [, array $attributes [, $children]])
```


### Parameters

* `tagName` : tag name of the element
* `attributes` : array with `property : value` pairs
* `children` : child(ren) element(s); can be a `string`, `number`, `HTMLElement`, or an `array` of the listed


### Methods

* `setAttribute(string $attribute, $value = "")`
* `append($element)`
* `innerHTML(callable $escapeFunction = null)`
* `outerHTML(callable $escapeFunction = null)`
* `output(callable $escapeFunction = null)`

## Usage

```php
$list = new HTMLElement("ul", ["id"=>"mylist", "class"=>"fancy-list"], [
    new HTMLElement("li", null, "Item 1"),
    new HTMLElement("li", null, "Item 2"),
    new HTMLElement("li", null, "Item 3"),
]);
$list->append(new HTMLElement("li", null, "Item 4"));
$list->output();

// or alternatively
$list = elem("ul", ["id"=>"mylist", "class"=>"fancy-list"], [
    elem("li", null, "Item 1"),
    elem("li", null, "Item 2"),
    elem("li", null, "Item 3"),
]);
$list->append(elem("li", null, "Item 4"));
$list->output();
```

The above code produces the following HTML:

```html
<ul id="mylist" class="fancy-list">
    <li>Item 1</li>
    <li>Item 2</li>
    <li>Item 3</li>
    <li>Item 4</li>
</ul>
```

<small>**Note**: The actual output is not indented. Might add that feature later. Maybe.</small>
