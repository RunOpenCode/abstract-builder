<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\Lexer\Emulative;
use PhpParser\Parser\Php7;

/**
 * Class Parser
 *
 * @package RunOpenCode\AbstractBuilder\Ast
 */
class Parser extends Php7
{
    /**
     * @var Parser
     */
    private static $instance;

    /**
     * Get shared parser instance. Singleton implementation.
     *
     * @return Parser|static
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new Parser(new Emulative(), []);
        }

        return self::$instance;
    }
}
