<?php

include "../src/HTMLElement.php";

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
