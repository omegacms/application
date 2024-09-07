<?php

namespace Omega\Application\Tests;

use Omega\Application\SingletonTrait;

class Sample1
{
    use SingletonTrait;

    public $property;

    private function __construct()
    {
        $this->property = 10;
    }
}
