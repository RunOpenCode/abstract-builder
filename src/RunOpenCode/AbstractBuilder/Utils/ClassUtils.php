<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Utils;

use RunOpenCode\AbstractBuilder\AbstractBuilder;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;

/**
 * Class ClassUtils
 *
 * @package RunOpenCode\AbstractBuilder\Utils
 */
final class ClassUtils
{
    private function __construct() { /* noop */ }

    /**
     * Check if class name is valid.
     *
     * @param string $fqcn
     *
     * @return bool
     */
    public static function isClassNameValid($fqcn)
    {
        foreach (explode('\\', ltrim($fqcn, '\\')) as $part) {

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if class can have its builder pattern class implemented.
     *
     * @param ClassMetadata $class
     *
     * @return bool
     */
    public static function isBuildable(ClassMetadata $class)
    {
        if (!$class->hasPublicMethod('__construct')) {
            return false;
        }

        if (0 === count($class->getPublicMethod('__construct')->getParameters())) {
            return false;
        }

        return true;
    }

    /**
     * Check if class is implementing required builder class.
     *
     * @param ClassMetadata $class
     *
     * @return bool
     */
    public static function isBuilder(ClassMetadata $class)
    {
        if (null === $class->getParent()) {
            return false;
        }

        $instanceOfAbstractBuilder = function (ClassMetadata $class) use (&$instanceOfAbstractBuilder) {

            if (null === $class->getParent()) {
                return false;
            }

            if (
                ReflectiveAbstractBuilder::class === $class->getParent()->getName()
                ||
                AbstractBuilder::class === $class->getParent()->getName()
            ) {
                return true;
            }

            return $instanceOfAbstractBuilder($class->getParent());
        };

        return $instanceOfAbstractBuilder($class);
    }

    /**
     * Get namespace of class.
     *
     * @param $class
     *
     * @return string|null
     *
     * @throws \RunOpenCode\AbstractBuilder\Exception\InvalidArgumentException
     */
    public static function getNamespace($class)
    {
        if ($class instanceof ClassMetadata) {
            $class = $class->getName();
        }

        $class = trim($class, '\\');

        $parts = explode('\\', $class);

        if (0 === count($parts)) {
            throw new InvalidArgumentException(sprintf('Invalid class name "%s".', $class));
        }

        if (1 === count($parts)) {
            return null;
        }

        array_pop($parts);

        return implode('\\', $parts);
    }

    /**
     * Get short name of class.
     *
     * @param $class
     *
     * @return string
     */
    public static function getShortName($class)
    {
        if ($class instanceof ClassMetadata) {
            return $class->getShortName();
        }

        $class = trim($class, '\\');

        $parts = explode('\\', $class);

        return array_pop($parts);
    }
}
