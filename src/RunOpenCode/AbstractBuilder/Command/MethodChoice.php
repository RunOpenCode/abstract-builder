<?php

namespace RunOpenCode\AbstractBuilder\Command;

use RunOpenCode\AbstractBuilder\Ast\MethodMetadata;
use RunOpenCode\AbstractBuilder\Ast\ParameterMetadata;

class MethodChoice
{
    private $method;

    public function __construct(MethodMetadata $method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function __toString()
    {
        $parameters = [];

        /**
         * @var ParameterMetadata $parameter
         */
        foreach ($this->method->getParameters() as $parameter) {
            $parameters[] =(($parameter->getType()) ? $parameter->getType().' ' : '').'$'.$parameter->getName();
        }

        return sprintf('%s(%s)%s', $this->method->getName(), implode(', ', $parameters), ($this->method->getReturnType()) ? ' :'.$this->method->getReturnType() : '');
    }
}
