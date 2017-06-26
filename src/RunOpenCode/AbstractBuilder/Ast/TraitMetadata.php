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

use PhpParser\Node\Stmt\Trait_;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;

class TraitMetadata
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fqcn;

    /**
     * @var TraitMetadata[]
     */
    private $traits;

    /**
     * @var MethodMetadata[]
     */
    private $methods;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var Trait_
     */
    private $ast;

    public function __construct($namespace, $name, array $traits = [], array $methods = [], $filename = null, Trait_ $ast = null)
    {
        $this->namespace = $namespace;
        $this->name = $name;

        $this->fqcn = '\\'.$this->name;

        if ($this->namespace) {
            $this->fqcn = '\\'.$this->namespace.'\\'.$this->name;
        }

        if (ClassUtils::isClassNameValid($this->fqcn)) {
            throw new InvalidArgumentException(sprintf('Provided full qualified class name "%s" is not valid PHP trait name.', $this->fqcn));
        }

        $this->traits = $traits;
        $this->methods = $methods;
        $this->filename = $filename;
        $this->ast = $ast;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFqcn()
    {
        return $this->fqcn;
    }

    /**
     * @return bool
     */
    public function hasTraits()
    {
        return count($this->traits) > 0;
    }

    /**
     * @return TraitMetadata[]
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @return MethodMetadata[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Check if trait has public method, with optional trait traverse.
     *
     * @param string $name
     * @param bool $traverse
     *
     * @return bool
     */
    public function hasPublicMethod($name, $traverse = true)
    {
        foreach ($this->methods as $method) {

            if ($name === $method->getName()) {
                return $method->isPublic();
            }
        }

        if ($traverse && $this->hasTraits()) {

            /**
             * @var TraitMetadata $trait
             */
            foreach ($this->traits as $trait) {

                if ($trait->hasPublicMethod($name, $traverse)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get public method for trait, with optional trait traverse.
     *
     * @param string $name
     * @param bool $traverse
     *
     * @return MethodMetadata
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function getPublicMethod($name, $traverse = true)
    {
        foreach ($this->methods as $method) {

            if ($name === $method->getName()) {

                if ($method->isPublic()) {
                    return $method;
                }

                throw new RuntimeException(sprintf('Method "%s()" for trait "%s" exists, but it is not public.', $name, $this->fqcn));
            }
        }

        if ($traverse && $this->hasTraits()) {

            /**
             * @var TraitMetadata $trait
             */
            foreach ($this->traits as $trait) {

                if ($trait->hasPublicMethod($name, $traverse)) {
                    return $trait->getPublicMethod($name, $traverse);
                }
            }
        }

        throw new RuntimeException(sprintf('Method "%s()" for trait "%s" does not exists.', $name, $this->fqcn));
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return Trait_
     */
    public function getAst()
    {
        return $this->ast;
    }


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getFqcn();
    }
}
