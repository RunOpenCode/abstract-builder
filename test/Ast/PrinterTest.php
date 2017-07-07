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
use RunOpenCode\AbstractBuilder\Ast\Printer;

/**
 * Class PrinterTest
 *
 * @package RunOpenCode\AbstractBuilder\Tests\Ast
 */
class PrinterTest extends TestCase
{
    /**
     * @var FileMetadata
     */
    private static $file;

    /**
     * @test
     */
    public function itPrints()
    {
        $this->assertContains('<?php', Printer::getInstance()->print(self::$file));
    }

    /**
     * @test
     */
    public function itDumps()
    {
        Printer::getInstance()->dump(self::$file);

        $this->assertTrue(file_exists(self::$file->getFilename()));
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$file = new FileMetadata(tempnam(sys_get_temp_dir(), 'test_printer'));
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        try {
            unlink(self::$file->getFilename());
        } catch (\Exception $e) { /* noop */ }
    }
}
