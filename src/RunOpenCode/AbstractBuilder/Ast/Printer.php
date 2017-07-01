<?php

namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\PrettyPrinter\Standard;

class Printer extends Standard
{
    /**
     * @var Printer
     */
    private static $instance;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
