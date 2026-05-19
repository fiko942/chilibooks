<?php

namespace CodeIgniter\Config;

/**
 * Optional shim for hosting environments where `putenv()` is disabled via php.ini.
 *
 * CodeIgniter's DotEnv uses `putenv()` to populate environment variables. When
 * `putenv()` is disabled, PHP reports it as an undefined function and CI will
 * fatal during boot. Defining a namespaced `CodeIgniter\\Config\\putenv()`
 * function allows DotEnv to call this instead.
 */
if (! function_exists(__NAMESPACE__ . '\\putenv')) {
    function putenv(string $assignment): bool
    {
        return \function_exists('\\putenv') ? \putenv($assignment) : false;
    }
}

