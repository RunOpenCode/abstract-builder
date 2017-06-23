<?php

namespace RunOpenCode\AbstractBuilder\Ast;

use PhpParser\Node\Param;

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
     * ParameterMetadata constructor.
     *
     * @param string $name
     * @param string $type
     * @param bool $byRef
     * @param bool $variadic
     * @param mixed $default
     */
    public function __construct($name, $type, $byRef, $variadic, $default)
    {
        $this->name = $name;
        $this->type = $type;
        $this->byRef = $byRef;
        $this->variadic = $variadic;
        $this->default = $default;
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
            $param->default
        );
    }
}
