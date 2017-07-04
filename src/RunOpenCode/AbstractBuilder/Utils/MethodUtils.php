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

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use RunOpenCode\AbstractBuilder\Ast\Metadata\MethodMetadata;

/**
 * Class MethodUtils
 *
 * @package RunOpenCode\AbstractBuilder\Utils
 */
final class MethodUtils
{
    private function __construct() { /* noop */ }

    /**
     * Get method visibility from AST definition of method
     *
     * @param int|ClassMethod $value
     * @param string $default
     *
     * @return string
     */
    public static function getVisibility($value, $default = MethodMetadata::PUBLIC)
    {
        if ($value instanceof ClassMethod) {
            $value = $value->flags;
        }

        if (($value & Class_::MODIFIER_PUBLIC) !== 0) {
            return MethodMetadata::PUBLIC;
        }

        if (($value & Class_::MODIFIER_PROTECTED) !== 0) {
            return MethodMetadata::PROTECTED;
        }

        if (($value & Class_::MODIFIER_PRIVATE) !== 0) {
            return MethodMetadata::PRIVATE;
        }

        return $default;
    }
}
