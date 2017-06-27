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

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;

/**
 * Class MethodMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Metadata
 */
class MethodMetadata
{
    const PRIVATE = 'private';
    const PUBLIC = 'public';
    const PROTECTED = 'protected';

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $abstract;

    /**
     * @var bool
     */
    private $final;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var string
     */
    private $returnType;

    /**
     * @var bool
     */
    private $byRef;

    /**
     * @var bool
     */
    private $static;

    /**
     * @var ParameterMetadata[]
     */
    private $parameters;

    /**
     * MethodMetadata constructor.
     *
     * @param string $name
     * @param bool $abstract
     * @param bool $final
     * @param string $visibility
     * @param string $returnType
     * @param bool $byRef
     * @param bool $static
     * @param ParameterMetadata[] $parameters
     */
    public function __construct($name, $abstract = false, $final = false, $visibility = self::PUBLIC, $returnType = null, $byRef = false, $static = false, array $parameters)
    {
        $this->name = $name;
        $this->abstract = $abstract;
        $this->final = $final;
        $this->visibility = $visibility;
        $this->returnType = $returnType;
        $this->byRef = $byRef;
        $this->static = $static;
        $this->parameters = $parameters;
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
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * @return bool
     */
    public function isFinal()
    {
        return $this->final;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return bool
     */
    public function byReference()
    {
        return $this->byRef;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return $this->static;
    }

    /**
     * @return bool
     */
    public function hasParameters()
    {
        return count($this->parameters) > 0;
    }

    /**
     * @return ParameterMetadata[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Check if method is constructor.
     *
     * @return bool
     */
    public function isConstructor()
    {
        return $this->getName() === '__construct';
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->visibility === self::PRIVATE;
    }

    /**
     * @return bool
     */
    public function isProtected()
    {
        return $this->visibility === self::PROTECTED;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->visibility === self::PUBLIC;
    }

    /**
     * Create method metadata instance from \PhpParser\Node\Stmt\ClassMethod
     *
     * @param ClassMethod $method
     * @return MethodMetadata|static
     */
    public static function fromClassMethod(ClassMethod $method)
    {
        $parameters = [];

        /**
         * @var Param $param
         */
        foreach ($method->getParams() as $param) {
            $parameters[] = ParameterMetadata::fromParameter($param);
        }

        $visibility = self::PRIVATE;

        if ($method->isProtected()) {
            $visibility = self::PROTECTED;
        }

        if ($method->isPublic()) {
            $visibility = self::PUBLIC;
        }

        return new static(
            $method->name,
            $method->isAbstract(),
            $method->isFinal(),
            $visibility,
            $method->getReturnType(),
            $method->returnsByRef(),
            $method->isStatic(),
            $parameters
        );
    }
}
