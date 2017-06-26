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

use PhpParser\Node\Stmt\Class_;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;

/**
 * Class ClassMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast
 */
class ClassMetadata
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $fqcn;

    /**
     * @var ClassMetadata
     */
    private $parent;

    /**
     * @var TraitMetadata[]
     */
    private $traits;

    /**
     * @var bool
     */
    private $final;

    /**
     * @var bool
     */
    private $abstract;

    /**
     * @var MethodMetadata[]
     */
    private $methods;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var Class_
     */
    private $ast;

    /**
     * ClassMetadata constructor.
     *
     * @param string $namespace
     * @param string $class
     * @param ClassMetadata|null $parent
     * @param bool $final
     * @param bool $abstract
     * @param MethodMetadata[] $methods
     * @param string|null $filename
     * @param Class_ $ast
     */
    public function __construct($namespace, $class, ClassMetadata $parent = null, array $traits = [], $final = false, $abstract = false, array $methods = [], $filename = null, Class_ $ast = null)
    {
        $this->namespace = trim($namespace, '\\');
        $this->class = trim($class, '\\');

        $this->fqcn = '\\'.$this->class;

        if ($this->namespace) {
            $this->fqcn = '\\'.$this->namespace.'\\'.$this->class;
        }

        if (ClassUtils::isClassNameValid($this->fqcn)) {
            throw new InvalidArgumentException(sprintf('Provided full qualified class name "%s" is not valid PHP class name.', $this->fqcn));
        }

        $this->parent = $parent;
        $this->traits = $traits;
        $this->final = $final;
        $this->abstract = $abstract;
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
    public function getClass()
    {
        return $this->class;
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
    public function isAutoloadable()
    {
        return class_exists($this->getFqcn(), true);
    }

    /**
     * Check if class inherits some other class.
     *
     * @return bool
     */
    public function hasParent()
    {
        return null !== $this->parent;
    }

    /**
     * @return ClassMetadata|null
     */
    public function getParent()
    {
        return $this->parent;
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
     * @return bool
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * @return bool
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * @return MethodMetadata[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Check if class has public method, with optional inheritance tree and trait traverse.
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

        if ($traverse && $this->hasParent()) {
            return $this->getParent()->hasPublicMethod($name, $traverse);
        }

        return false;
    }

    /**
     * Get public method for class, with optional inheritance tree and trait traverse.
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

                throw new RuntimeException(sprintf('Method "%s()" for class "%s" exists, but it is not public.', $name, $this->fqcn));
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

        if ($traverse && $this->hasParent() && $this->getParent()->hasPublicMethod($name, $traverse)) {
            return $this->getParent()->getPublicMethod($name, $traverse);
        }

        throw new RuntimeException(sprintf('Method "%s()" for class "%s" does not exists.', $name, $this->fqcn));
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return Class_
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

    /**
     * Initialize new, non-existing class.
     *
     * @param string $fqcn
     *
     * @return ClassMetadata|static $this
     */
    public static function create($fqcn)
    {
        $parts = explode('\\', trim($fqcn, '\\'));
        $class = array_pop($parts);
        $namespace = implode('\\', $parts);

        return new static($namespace, $class);
    }

    /**
     * Clones original metadata object, with possible values overwrite
     *
     * @param ClassMetadata $original
     * @param array $overwrite
     *
     * @return ClassMetadata|static $this
     */
    public static function clone(ClassMetadata $original, array $overwrite = [])
    {
        $data = [
            'namespace' => $original->getNamespace(),
            'class' => $original->getClass(),
            'parent' => $original->getParent(),
            'final' => $original->isFinal(),
            'abstract ' => $original->isAbstract(),
            'methods' => $original->getMethods(),
            'filename' => $original->getFilename(),
            'ast' => $original->getAst(),
        ];

        $data = array_merge($data, $overwrite);

        return (new \ReflectionClass(static::class))->newInstanceArgs(array_values($data));
    }
}
