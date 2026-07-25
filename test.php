<?php
trait MyTrait {
    public array $foo = [];
}

class MyClass {
    use MyTrait;
    public ?array $foo = [];
}

$obj = new MyClass();
$obj->foo = null;
echo "Success\n";
