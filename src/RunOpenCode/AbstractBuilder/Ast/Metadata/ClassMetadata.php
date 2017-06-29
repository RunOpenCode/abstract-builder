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

use PhpParser\Node\Stmt\Class_;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;
use RunOpenCode\AbstractBuilder\Utils\ClassUtils;

/**
 * Class ClassMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Metadata
 */
class ClassMetadata
{
    /**
     * @var string
     */
    private $name;

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
     * @var Class_
     */
    private $ast;

    /**
     * ClassMetadata constructor.
     *
     * @param string $name
     * @param ClassMetadata|null $parent
     * @param bool $final
     * @param bool $abstract
     * @param MethodMetadata[] $methods
     * @param Class_ $ast
     */
    public function __construct($name, ClassMetadata $parent = null, array $traits = [], $final = false, $abstract = false, array $methods = [], Class_ $ast = null)
    {
        $this->name = trim($name, '\\');

        if (!ClassUtils::isClassNameValid($this->name)) {
            throw new InvalidArgumentException(sprintf('Provided class name "%s" is not valid PHP class name.', $this->name));
        }

        $this->parent = $parent;
        $this->traits = $traits;
        $this->final = $final;
        $this->abstract = $abstract;
        $this->methods = $methods;
        $this->ast = $ast;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isAutoloadable()
    {
        return class_exists($this->getName(), true);
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
     * Check if class has method, with optional inheritance tree and trait traverse.
     *
     * @param string $name
     * @param bool $traverse
     *
     * @return bool
     */
    public function hasMethod($name, $traverse = true)
    {
        foreach ($this->methods as $method) {

            if ($name === $method->getName()) {
                return true;
            }
        }

        if ($traverse && $this->hasTraits()) {

            /**
             * @var TraitMetadata $trait
             */
            foreach ($this->traits as $trait) {

                if ($trait->hasMethod($name, $traverse)) {
                    return true;
                }
            }
        }


        if ($traverse && $this->hasParent()) {
            return $this->getParent()->hasMethod($name, $traverse);
        }

        return false;
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

                throw new RuntimeException(sprintf('Method "%s()" for class "%s" exists, but it is not public.', $name, $this->name));
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

        throw new RuntimeException(sprintf('Method "%s()" for class "%s" does not exists.', $name, $this->name));
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
        return $this->getName();
    }

    /**
     * Initialize new, non-existing class.
     *
     * @param string $name
     *
     * @return ClassMetadata|static $this
     */
    public static function create($name)
    {
        return new static($name);
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
            'name' => $original->getName(),
            'parent' => $original->getParent(),
            'traits' => $original->getTraits(),
            'final' => $original->isFinal(),
            'abstract ' => $original->isAbstract(),
            'methods' => $original->getMethods(),
            'ast' => $original->getAst(),
        ];

        $data = array_merge($data, $overwrite);

        return (new \ReflectionClass(static::class))->newInstanceArgs(array_values($data));
    }
}
