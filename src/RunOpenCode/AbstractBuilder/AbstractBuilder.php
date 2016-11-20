<?php

namespace RunOpenCode\AbstractBuilder;

/**
 * Class AbstractBuilder
 *
 * Prototype implementation of class builder pattern.
 *
 * @package RunOpenCode\AbstractBuilder
 */
abstract class AbstractBuilder
{
    /**
     * A placeholder for constructor arguments.
     *
     * @var array
     */
    protected $data;

    /**
     * AbstractBuilder constructor.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->data = $this->configureParameters();

        if (0 === count($this->data)) {
            throw new \RuntimeException('Builder expects at least one parameter to be defined.');
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
        return $reflector->newInstanceArgs(array_values($this->data));
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
     * Set building class constructor argument.
     *
     * @param string $name Argument name.
     * @param mixed $value Argument value.
     *
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new \InvalidArgumentException(sprintf('Unknown property "%s" in "%s".', $name, get_class($this)));
        }

        $this->data[$name] = $value;
    }

    /**
     * Get building class constructor argument.
     *
     * @param string $name Argument name.
     * @return mixed Argument value.
     *
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new \InvalidArgumentException(sprintf('Unknown property "%s" in "%s".', $name, get_class($this)));
        }

        return $this->data[$name];
    }

    /**
     * Check if building class constructor argument is defined.
     *
     * @param string $name Argument name.
     * @return bool TRUE if argument is defined.
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Get/set building class constructor argument.
     *
     * @param string $name A method name.
     * @param array $arguments A method arguments.
     * @return $this|mixed Fluent interface or argument value, depending on method name.
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, array $arguments)
    {
        $property = lcfirst(substr($name, 3));

        if (
            !array_key_exists($property, $this->data)
            ||
            (strpos($name, 'set') !== 0 && strpos($name, 'get') !== 0)
        ) {
            throw new \BadMethodCallException(sprintf('Unknown method "%s" in "%s".', $name, get_class($this)));
        }

        if (count($arguments) !== 1 && strpos($name, 'set') === 0) {
            throw new \BadMethodCallException(sprintf('Method "%s" in "%s" expects exactly one parameter.', $name, get_class($this)));
        }

        if (count($arguments) !== 0 && strpos($name, 'get') === 0) {
            throw new \BadMethodCallException(sprintf('Method "%s" in "%s" does not use any parameter.', $name, get_class($this)));
        }

        if (strpos($name, 'get') === 0) {
            return $this->data[$property];
        }

        $this->data[$property] = $arguments[0];

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
     * Produces new builder.
     *
     * @return static
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
    protected abstract function configureParameters();

    /**
     * Get full qualified class name of class which instance ought to be constructed.
     *
     * @return string
     */
    protected abstract function getObjectFqcn();
}

