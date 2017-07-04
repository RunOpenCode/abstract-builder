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


}