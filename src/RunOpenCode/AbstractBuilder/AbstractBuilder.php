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

use RunOpenCode\AbstractBuilder\Exception\BadMethodCallException;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\Exception\RuntimeException;

/**
 * Class AbstractBuilder
 *
 * Prototype implementation of class builder pattern.
 *
 * @package RunOpenCode\AbstractBuilder
 */
abstract class AbstractBuilder implements \ArrayAccess
{
    /**
     * A placeholder for constructor arguments.
     *
     * @var array
     */
    protected $_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32;

    /**
     * AbstractBuilder constructor.
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public function __construct()
    {
        $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32 = $this->configureParameters();

        if (0 === count($this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32)) {
            throw new RuntimeException('Builder expects at least one parameter to be defined.');
        }
    }

    /**
     * Builds new building class from provided arguments.
     *
     * @return object
     */
    public function build()
    {
        $reflector = new \ReflectionClass($this->getObjectFqcn());
        return $reflector->newInstanceArgs(array_values($this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32));
    }

    /**
     * Set building class constructor arguments from array.
     *
     * @param array $values Values for constructor arguments of building class.
     * @return AbstractBuilder $this Fluent interface
     */
    public function fromArray(array $values)
    {
        foreach ($values as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Get all building class constructor arguments as array.
     *
     * @return array
     */
    public function toArray(array $keys = array())
    {
        if (0 === count($keys)) {
            return $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32;
        }

        return array_intersect_key($this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32, array_flip($keys));
    }

    /**
     * Set building class constructor argument.
     *
     * @param string $name Argument name.
     * @param mixed $value Argument value.
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32)) {
            throw new InvalidArgumentException(sprintf('Unknown property "%s" in "%s".', $name, get_class($this)));
        }

        $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$name] = $value;
    }

    /**
     * Get building class constructor argument.
     *
     * @param string $name Argument name.
     * @return mixed Argument value.
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32)) {
            throw new InvalidArgumentException(sprintf('Unknown property "%s" in "%s".', $name, get_class($this)));
        }

        return $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$name];
    }

    /**
     * Check if building class constructor argument is defined.
     *
     * @param string $name Argument name.
     * @return bool TRUE if argument is defined.
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32);
    }

    /**
     * Get/set building class constructor argument.
     *
     * @param string $name A method name.
     * @param array $arguments A method arguments.
     * @return $this|mixed Fluent interface or argument value, depending on method name.
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\BadMethodCallException
     */
    public function __call($name, array $arguments)
    {
        $property = lcfirst(substr($name, 3));

        if (
            !array_key_exists($property, $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32)
            ||
            (strpos($name, 'set') !== 0 && strpos($name, 'get') !== 0)
        ) {
            throw new BadMethodCallException(sprintf('Unknown method "%s" in "%s".', $name, get_class($this)));
        }

        if (count($arguments) !== 1 && strpos($name, 'set') === 0) {
            throw new BadMethodCallException(sprintf('Method "%s" in "%s" expects exactly one parameter.', $name, get_class($this)));
        }

        if (count($arguments) !== 0 && strpos($name, 'get') === 0) {
            throw new BadMethodCallException(sprintf('Method "%s" in "%s" does not use any parameter.', $name, get_class($this)));
        }

        if (strpos($name, 'get') === 0) {
            return $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$property];
        }

        $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$property] = $arguments[0];

        return $this;
    }

    /**
     * Function call to builder object instance will produce building class.
     *
     * @return object
     */
    public function __invoke()
    {
        return $this->build();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$offset];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->offsetExists($offset)) {
            throw new RuntimeException(sprintf('Undefined property "%s" provided.', $offset));
        }

        $this->_builder_placeholder_data_87cd3fb3_4fde_49d1_a91f_6411e0862c32[$offset] = $value;
    }

    /**
     * Unused, throws an exception.
     *
     * @param mixed $offset
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new BadMethodCallException('It is not allowed to unset builder property.');
    }

    /**
     * Produces new builder.
     *
     * @return static
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\RuntimeException
     */
    public static function createBuilder()
    {
        return new static();
    }

    /**
     * Configure builder parameters that will be passed to building class constructor.
     *
     * @return array
     */
    abstract protected function configureParameters();

    /**
     * Get full qualified class name of class which instance ought to be constructed.
     *
     * @return string
     */
    abstract protected function getObjectFqcn();
}
