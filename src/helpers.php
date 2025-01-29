<?php

if (!function_exists('module_path')) {
    /**
     * Get the path to the module.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    function module_path(string $name, string $path = ''): string
    {
        $module = app('modules')->find($name);

        return $module->path . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
