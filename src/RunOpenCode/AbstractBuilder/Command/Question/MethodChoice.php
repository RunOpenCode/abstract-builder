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

use RunOpenCode\AbstractBuilder\Ast\ParameterMetadata;

/**
 * Class MethodChoice
 *
 * @package RunOpenCode\AbstractBuilder\Command\Question
 */
abstract class MethodChoice
{
    protected $parameter;

    public function __construct(ParameterMetadata $parameter)
    {
        $this->parameter = $parameter;
    }
    
    public function getParameter()
    {
        return $this->parameter;
    }

    abstract public function getMethodName();

    abstract public function __toString();
}
