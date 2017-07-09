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
use RunOpenCode\AbstractBuilder\Ast\BuilderFactory;

/**
 * Class BuilderFactoryTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Ast
 */
class BuilderFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsBuilderFactory()
    {
        $this->assertInstanceOf(BuilderFactory::class, BuilderFactory::getInstance());
    }
}
