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

use PhpParser\PrettyPrinter\Standard;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;

/**
 * Class Printer
 *
 * @package RunOpenCode\AbstractBuilder\Ast
 */
class Printer extends Standard
{
    /**
     * @var Printer
     */
    private static $instance;

    /**
     * Get shared printer instance. Singleton implementation.
     *
     * @return Printer|static
     */
    public static function getInstance()
    {
        if (null === self::$instance) {

            self::$instance = new static([
                'shortArraySyntax' => true
            ]);
        }

        return self::$instance;
    }

    /**
     * Get PHP code for file metadata.
     *
     * @param FileMetadata $file
     *
     * @return string
     */
    public function print(FileMetadata $file)
    {
        return $this->prettyPrintFile($file->getAst());
    }

    /**
     * Save PHP code for file metadata into file.
     *
     * @param FileMetadata $file
     */
    public function dump(FileMetadata $file)
    {
        file_put_contents($file->getFilename(), $this->print($file));
    }
}
