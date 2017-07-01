<?php

namespace RunOpenCode\AbstractBuilder\Utils;

use RunOpenCode\AbstractBuilder\AbstractBuilder;
use RunOpenCode\AbstractBuilder\Ast\Metadata\ClassMetadata;
use RunOpenCode\AbstractBuilder\ReflectiveAbstractBuilder;

final class ClassUtils
{
    private function __construct() { /* noop */ }

    public static function isClassNameValid($fqcn)
    {
        foreach (explode('\\', ltrim($fqcn, '\\')) as $part) {

            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $part)) {
                return false;
            }
        }

        return true;
    }

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

    public static function isBuilder(ClassMetadata $class)
    {
        if (null === $class->getParent()) {
            return false;
        }

        if (
            null !== $class->getParent()
            &&
            (
                ReflectiveAbstractBuilder::class === $class->getParent()->getName()
                ||
                AbstractBuilder::class === $class->getParent()->getName()
            )
        ) {
            return true;
        }

        return self::isBuilder($class->getParent());
    }

    public static function getNamespace($class)
    {
        if ($class instanceof ClassMetadata) {
            $class = $class->getName();
        }

        $parts = explode('\\', $class);
        array_pop($parts);

        return implode('\\', $parts);
    }

    public static function getShortName($class)
    {
        if ($class instanceof ClassMetadata) {
            return $class->getShortName();
        }

        $parts = explode('\\', $class);
        return array_pop($parts);
    }
}
