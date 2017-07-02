<?php

namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\PrettyPrinter\Standard;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;

class Printer extends Standard
{
    /**
     * @var Printer
     */
    private static $instance;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static([
                'shortArraySyntax' => true
            ]);
        }

        return self::$instance;
    }

    public function print(FileMetadata $file)
    {
        return $this->prettyPrintFile($file->getAst());
    }

    public function dump(FileMetadata $file)
    {
        file_put_contents($file->getFilename(), $this->print($file));
    }
}
