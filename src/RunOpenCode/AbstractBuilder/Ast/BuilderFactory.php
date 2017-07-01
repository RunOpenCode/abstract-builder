<?php

namespace RunOpenCode\AbstractBuilder\Ast;

class BuilderFactory extends \PhpParser\BuilderFactory
{
    /**
     * @var BuilderFactory
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
