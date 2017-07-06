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

use PHPUnit\Framework\TestCase;
use RunOpenCode\AbstractBuilder\AbstractBuilder;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\MethodMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ParameterMetadata;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;

/**
 * Class ClassUtilsTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Utils
 */
class ClassUtilsTest extends TestCase
{
    /**
     * @test
     */
    public function validateClassName()
    {
        $data = [
            ['name' => 'this is invalid', 'expects' => false],
            ['name' => '\Valid\ClassName', 'expects' => true],
            ['name' => 'Invalid\\', 'expects' => false],
            ['name' => 'Seams\\Valid&\\But\\ItIsNot', 'expects' => false],
        ];

        foreach ($data as $case) {
            /**
             * @var $name
             * @var $expects
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertSame($expects, ClassUtils::isClassNameValid($name));
        }
    }

    /**
     * @test
     */
    public function isBuildable()
    {
        $data = [
            ['class' => new ClassMetadata('nonbuildable', null, [], false, false, [new MethodMetadata('__construct')]), 'expects' => false],
            ['class' => new ClassMetadata('nonbuildable'), 'expects' => false],
            ['class' => new ClassMetadata('nonbuildable', null, [], false, false, [new MethodMetadata('__construct',  false, false, MethodMetadata::PUBLIC, null, false, false, [new ParameterMetadata('param')])]), 'expects' => true],
        ];


        foreach ($data as $case) {
            /**
             * @var $class
             * @var $expects
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertSame($expects, ClassUtils::isBuildable($class));
        }
    }

    /**
     * @test
     */
    public function isBuilder()
    {
        $data = [
            ['class' => new ClassMetadata('notbuilder'), 'expects' => false],
            ['class' => new ClassMetadata(AbstractBuilder::class), 'expects' => false],
            ['class' => new ClassMetadata(ReflectiveAbstractBuilder::class), 'expects' => false],
            ['class' => new ClassMetadata('builder', new ClassMetadata(AbstractBuilder::class)), 'expects' => true],
            ['class' => new ClassMetadata('builder', new ClassMetadata(ReflectiveAbstractBuilder::class)), 'expects' => true],
            ['class' => new ClassMetadata('notbuilder', new ClassMetadata('not_builder_as_well')), 'expects' => false],
            ['class' => new ClassMetadata('builder', new ClassMetadata('builder', new ClassMetadata(ReflectiveAbstractBuilder::class))), 'expects' => true],
        ];


        foreach ($data as $case) {
            /**
             * @var $class
             * @var $expects
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertSame($expects, ClassUtils::isBuilder($class));
        }
    }

    /**
     * @test
     */
    public function getNamespace()
    {
        $data = [
            ['class' => '\\NoNamespace', 'expects' => null],
            ['class' => 'Namespace\\ClassName', 'expects' => 'Namespace'],
            ['class' => '\\Longer\\Namespace\\ClassName', 'expects' => 'Longer\\Namespace'],
            ['class' => new ClassMetadata('\\Longer\\Namespace\\ClassName'), 'expects' => 'Longer\\Namespace'],
        ];

        foreach ($data as $case) {
            /**
             * @var $class
             * @var $expects
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertSame($expects, ClassUtils::getNamespace($class));
        }
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function itThrowsExceptionWhenEmptyStringIsPassedForNamespace()
    {
        ClassUtils::getNamespace('');
    }

    /**
     * @test
     */
    public function getShortName()
    {
        $data = [
            ['class' => '\\NoNamespace', 'expects' => 'NoNamespace'],
            ['class' => 'Namespace\\ClassName', 'expects' => 'ClassName'],
            ['class' => new ClassMetadata('ClassName'), 'expects' => 'ClassName'],
        ];

        foreach ($data as $case) {
            /**
             * @var $class
             * @var $expects
             */
            extract($case, EXTR_OVERWRITE);

            $this->assertSame($expects, ClassUtils::getShortName($class));
        }
    }

    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function itThrowsExceptionWhenEmptyStringIsPassedForShortName()
    {
        ClassUtils::getShortName('');
    }

}