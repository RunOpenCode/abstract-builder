<?php

namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\Parser\Multiple;
use PhpParser\ParserFactory;

class Parser extends Multiple
{
    private static $instance;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        }

        return self::$instance;
    }
}
