# HTMLElement.php

A class for creating HTML dynamically.

I'm not a fan of mixing PHP with HTML when creating HTML code with lots of PHP variables. It just looks ugly and confusing. You have to pay attention to quotes, (if) blocks, PHP tags, etc. I'd rather generate the HTML with PHP. `HTMLElement` does exactly that.


## Syntax

The syntax and the usage is very similar to [HTMLElement](https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement) (JS API).

```php
new HTMLElement(string $tagName [, array $attributes = null [, $children = null]])
```


### Parameters

* `tagName` : tag name of the element
* `attributes` : array with `property => value` pairs (can be skipped)
* `children` : an item (that can be a `string`, `number`, `HTMLElement`) or an array of items

### Methods

* `getAttribute(string $attribute): ?string`
* `setAttribute(string $attribute, mixed $value = "")`
* `removeAttribute(string $attribute)`
* `setId($id)`
* `addClass(string $class)`
* `removeClass(string $class)`
* `hasClass(string $class): bool`
* `getClassList(): array`
* `getChildren(): array`
* `append(mixed $element)`
* `prepend(mixed $element)`
* `innerHTML(callable $escapeFunction = null): string`
* `outerHTML(callable $escapeFunction = null): string`
* `openingTag(): string`
* `closingTag(): string`
* `output(callable $escapeFunction = null)`

## Usage

```php
$mylist = new HTMLElement("ul", ["id" => "mylist", "class" => "fancy-list"], [
    new HTMLElement("li", null, "Item 1"),
    new HTMLElement("li", "Item 2"),
    new HTMLElement("li", "Item 3"),
]);
$mylist->append(new HTMLElement("li", "Item 4"));
$mylist->output();

// or alternatively
$mylist = elem("ul", ["id" => "mylist", "class" => "fancy-list"], [
    elem("li", null, "Item 1"),
    elem("li", "Item 2"),
    elem("li", "Item 3"),
]);
$mylist->append(elem("li", "Item 4"));
$mylist->output();
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

# [element.js](https://github.com/akinuri/element.js)

This PHP code has a similar version in JS. In case you want to do the same thing at both the server and the client.

