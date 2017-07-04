<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Tests\Utils;

use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use RunOpenCode\AbstractBuilder\Utils\MethodUtils;

/**
 * Class MethodUtilsTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Utils
 */
class MethodUtilsTest extends TestCase
{
    /**
     * @test
     */
    public function itGetsDefaultVisibilityOnUnknownValue()
    {
        $this->assertEquals('default', MethodUtils::getVisibility(1000, 'default'));
    }

    /**
     * @test
     */
    public function itGetsPublic()
    {
        $this->assertEquals('public', MethodUtils::getVisibility(1));
    }

    /**
     * @test
     */
    public function itGetsProtected()
    {
        $this->assertEquals('protected', MethodUtils::getVisibility(2));
    }

    /**
     * @test
     */
    public function itGetsPrivate()
    {
        $this->assertEquals('private', MethodUtils::getVisibility(4));
    }

    /**
     * @test
     */
    public function itAcceptsInstanceOfClassMethod()
    {
        $this->assertEquals('default', MethodUtils::getVisibility(new ClassMethod('test'), 'default'));
    }
}
