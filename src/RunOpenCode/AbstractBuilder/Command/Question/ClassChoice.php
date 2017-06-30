<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Command\Question;

use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\Ast\Metadata\FileMetadata;

/**
 * Class ClassChoice
 *
 * @package RunOpenCode\AbstractBuilder\Command\Question
 */
class ClassChoice
{
    private $file;

    private $class;

    public function __construct(FileMetadata $file, ClassMetadata $class)
    {
        $this->file = $file;
        $this->class = $class;
    }

    /**
     * @return FileMetadata
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return ClassMetadata
     */
    public function getClass()
    {
        return $this->class;
    }
}
