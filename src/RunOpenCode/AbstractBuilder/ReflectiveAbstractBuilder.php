<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder;

/**
 * Class ReflectiveAbstractBuilder
 *
 * Reflective abstract builder uses reflections to determine constructor parameters.
 *
 * @package RunOpenCode\AbstractBuilder
 */
abstract class ReflectiveAbstractBuilder extends AbstractBuilder
{
    /**
     * {@inheritdoc}
     */
    protected function configureParameters()
    {
        $parameters = [];

        $reflectionClass = new \ReflectionClass($this->getObjectFqcn());
        $constructor = $reflectionClass->getConstructor();

        foreach ($constructor->getParameters() as $reflectionParameter) {
            $parameters[$reflectionParameter->getName()] = ($reflectionParameter->isDefaultValueAvailable()) ? $reflectionParameter->getDefaultValue() : null;
        }

        return $parameters;
    }
}
