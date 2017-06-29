<?php

namespace RunOpenCode\AbstractBuilder\Utils;

use PhpParser\Node\Stmt\Class_;
use RunOpenCode\AbstractBuilder\Ast\Metadata\MethodMetadata;

final class MethodUtils
{
    private function __construct() { /* noop */ }

    /**
     * Get method visibility from AST definition
     *
     * @param int $value
     * @param string $default
     *
     * @return string
     */
    public static function getVisibility($value, $default = MethodMetadata::PUBLIC)
    {
        if (($value & Class_::MODIFIER_PUBLIC) !== 0) {
            return MethodMetadata::PUBLIC;
        }

        if (($value & Class_::MODIFIER_PROTECTED) !== 0) {
            return MethodMetadata::PROTECTED;
        }

        if (($value & Class_::MODIFIER_PRIVATE) !== 0) {
            return MethodMetadata::PRIVATE;
        }

        if (null === $value) {
            return $default;
        }
    }
}
