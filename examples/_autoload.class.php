<?php

function __autoload($namespace) {
    $var = explode('\\', $namespace);
    $class = end($var);
    if (file_exists(dirname(__FILE__).'/../src/' . $class . '.php'))
    require dirname(__FILE__).'/../src/' . $class . '.php';
}