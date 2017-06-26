<?php

namespace RunOpenCode\AbstractBuilder\Utils;

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
}
