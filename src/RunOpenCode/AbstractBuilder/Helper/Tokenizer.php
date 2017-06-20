<?php

namespace RunOpenCode\AbstractBuilder\Helper;

use RunOpenCode\AbstractBuilder\Exception\RuntimeException;

class Tokenizer
{
    public static function findClass($path)
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new RuntimeException(sprintf('There is no file on path "%s".', $path));
        }

        if (!is_readable($path)) {
            throw new RuntimeException(sprintf('There is a file on path "%s", but it is not readable.', $path));
        }

        $contents = file_get_contents($path);
        $namespace = $class = '';
        $getting_namespace = $getting_class = false;

        foreach (token_get_all($contents) as $token) {

            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $getting_namespace = true;
            }

            if (is_array($token) && $token[0] === T_CLASS) {
                $getting_class = true;
            }

            if ($getting_namespace === true) {

                if(is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    $namespace .= $token[1];
                } elseif ($token === ';') {
                    $getting_namespace = false;
                }
            }

            if ($getting_class === true && is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];
                break;
            }
        }

        if ('' === $namespace.$class) {
            throw new RuntimeException(sprintf('There is no class definition in file on path "%s".', $path));
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
