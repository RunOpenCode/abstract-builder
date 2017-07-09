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
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;
use RunOpenCode\AbstractBuilder\Ast\MetadataLoader;
use RunOpenCode\AbstractBuilder\Tests\Fixtures\MetadataLoader\SimpleClass;

/**
 * Class MetadataLoaderTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Ast
 */
class MetadataLoaderTest extends TestCase
{
    /**
     * @test
     * @expectedException \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function itThrowsExceptionWhenMetadataCanNotBeLoaded()
    {
        MetadataLoader::create()->load('Class\DoesNot\Exists');
    }

    /**
     * @test
     */
    public function itLoadsMetadata()
    {
        $metadata = MetadataLoader::create()->load(SimpleClass::class);

        $this->assertInstanceOf(FileMetadata::class, $metadata);
        $this->assertSame(SimpleClass::class, $metadata->getClass(SimpleClass::class)->getName());
    }

    /**
     * @test
     */
    public function itLoadsMetadataForFile()
    {
        $metadata = MetadataLoader::create()->load(__DIR__.'/../Fixtures/MetadataLoader/SimpleClass.php');

        $this->assertInstanceOf(FileMetadata::class, $metadata);
        $this->assertSame(SimpleClass::class, $metadata->getClass(SimpleClass::class)->getName());
    }
}
