<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Tests\Ast;

use PHPUnit\Framework\TestCase;
use RunOpenCode\AbstractBuilder\Ast\Parser;

/**
 * Class ParserTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Ast
 */
class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsParser()
    {
        $this->assertInstanceOf(Parser::class, Parser::getInstance());
    }
}
