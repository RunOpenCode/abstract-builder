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

/**
 * Class ParameterMetadata
 *
 * @package RunOpenCode\AbstractBuilder\Ast\Metadata
 */
class ParameterMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $byRef;

    /**
     * @var bool
     */
    private $variadic;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var Param
     */
    private $ast;

    /**
     * ParameterMetadata constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $byRef
     * @param bool $variadic
     * @param mixed $default
     */
    public function __construct($name, $type = null, $byRef = false, $variadic = false, $default = null, Param $ast = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->byRef = $byRef;
        $this->variadic = $variadic;
        $this->default = $default;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
    public function isVariadic()
    {
        return $this->variadic;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @return Param
     */
    public function getAst()
    {
        return $this->ast;
    }

    /**
     * Creates parameter metadata instance from \PhpParser\Node\Param
     *
     * @param Param $param
     * @return ParameterMetadata|static
     */
    public static function fromParameter(Param $param)
    {
        return new static(
            $param->name,
            $param->type,
            $param->byRef,
            $param->variadic,
            $param->default,
            $param
        );
    }
}
