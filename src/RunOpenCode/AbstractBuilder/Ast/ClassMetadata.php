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

use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;

/**
 * Class ClassMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast
 */
class ClassMetadata
{
    /**
     * @var array
     */
    private $ast;

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
     * @var string
     */
    private $filename;

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
     * @var ClassMetadata
     */
    private $parent;

    /**
     * ClassMetadata constructor.
     *
     * @param $ast
     * @param string $namespace
     * @param string $class
     * @param null|string $filename
     * @param bool $final
     * @param bool $abstract
     * @param MethodMetadata[] $methods
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function __construct($ast, $namespace, $class, $filename = null, $final = false, $abstract = false, array $methods = [], ClassMetadata $parent = null)
    {
        $this->ast = $ast;
        $this->namespace = trim($namespace, '\\');
        $this->class = trim($class, '\\');
        $this->filename = $filename;
        $this->final = $final;
        $this->abstract = $abstract;
        $this->methods = $methods;
        $this->parent = $parent;

        $this->fqcn = '\\'.$this->class;

        if ($this->namespace) {
            $this->fqcn = '\\'.$this->namespace.'\\'.$this->class;
        }


        foreach (explode('\\', ltrim($this->fqcn, '\\')) as $part) {

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $part)) {
                throw new InvalidArgumentException(sprintf('Provided full qualified class name "%s" is not valid PHP class name.', $this->fqcn));
            }
        }
    }

    /**
     * @return
     */
    public function getAst()
    {
        return $this->ast;
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
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
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
     * @return MethodMetadata|null
     */
    public function getConstructor()
    {
        foreach ($this->methods as $method) {

            if ('__construct' === $method->getName()) {
                return $method;
            }
        }

        if (null !== $this->parent) {
            return $this->parent->getConstructor();
        }

        return null;
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
     * Check if class has public method, with optional tree traverse.
     *
     * @param string $name
     * @param bool $traverse
     *
     * @return bool
     */
    public function hasPublicMethod($name, $traverse = true)
    {
        foreach ($this->methods as $method) {

            if ($method->isPublic() && $name === $method->getName()) {
                return true;
            }
        }

        if ($traverse && $this->hasParent()) {
            return $this->getParent()->hasPublicMethod($name, $traverse);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAutoloadable()
    {
        return class_exists($this->getFqcn(), true);
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

        return new static([], $namespace, $class);
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
            'ast' => $original->getAst(),
            'namespace' => $original->getNamespace(),
            'class' => $original->getClass(),
            'filename' => $original->getFilename(),
            'final' => $original->isFinal(),
            'abstract ' => $original->isAbstract(),
            'methods' => $original->getMethods(),
            'parent' => $original->getParent()
        ];

        $data = array_merge($data, $overwrite);

        return (new \ReflectionClass(static::class))->newInstanceArgs(array_values($data));
    }
}
