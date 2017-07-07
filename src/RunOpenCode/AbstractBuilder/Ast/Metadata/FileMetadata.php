<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Ast\Metadata;

/**
 * Class FileMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Metadata
 */
class FileMetadata
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     */
    private $uses;

    /**
     * @var ClassMetadata[]
     */
    private $classes;

    /**
     * @var TraitMetadata[]
     */
    private $traits;

    /**
     * @var array
     */
    private $ast;

    /**
     * FileMetadata constructor.
     *
     * @param string $filename
     * @param array $classes
     * @param array $traits
     * @param array $ast
     */
    public function __construct($filename, array $uses = [], array $classes = [], array $traits = [], array $ast = [])
    {
        $this->filename = $filename;
        $this->uses = $uses;
        $this->classes = $classes;
        $this->traits = $traits;
        $this->ast = $ast;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Check if class is defined within file.
     *
     * @param string $name
     * @return bool
     */
    public function hasClass($name)
    {
        return isset($this->classes[trim($name, '\\')]);
    }

    /**
     * Get class definition from file.
     *
     * @param string $name
     * @return ClassMetadata
     */
    public function getClass($name)
    {
        return $this->classes[trim($name, '\\')];
    }

    /**
     * @return ClassMetadata[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return TraitMetadata[]
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @return array
     */
    public function getAst()
    {
        return $this->ast;
    }
}
