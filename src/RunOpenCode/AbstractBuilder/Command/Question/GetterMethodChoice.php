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

/**
 * Class GetterMethodChoice
 *
 * @package RunOpenCode\AbstractBuilder\Command\Question
 */
class GetterMethodChoice extends MethodChoice
{
    public function getMethodName()
    {
        return sprintf('get%s', ucfirst($this->parameter->getName()));
    }

    public function __toString()
    {
        return sprintf('get%s()', ucfirst($this->parameter->getName()));
    }
}
