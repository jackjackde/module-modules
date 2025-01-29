<?php

declare(strict_types=1);

namespace JackJack\Modules\Macros;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JackJack\Modules\Exceptions\FilePathIsIncorrect;
use JackJack\Modules\Exceptions\FilePathNotSpecified;
use JackJack\Modules\Exceptions\ModuleNameNotFound;
use JackJack\Modules\Exceptions\ModuleNotExist;
use JackJack\Modules\Facades\Modules;

class InertiaModules
{
    /**
     * @param string $source
     * @return string
     * @throws FilePathIsIncorrect
     * @throws FilePathNotSpecified
     * @throws ModuleNameNotFound
     * @throws ModuleNotExist
     */
    public function build(string $source): string
    {
        $sourceData = $this->explodeSource($source);
        $moduleName = $this->getModuleName($sourceData[0]);
        $resourcePath = config('modules.paths.source');
        $path = $this->getPath($sourceData[1]);

        return $this->getFullPath($moduleName, $resourcePath, $path);
    }

    /**
     * @param string $moduleName
     * @param string $resourcePath
     * @param string $path
     * @return string
     * @throws FilePathIsIncorrect
     */
    private function getFullPath(string $moduleName, string $resourcePath, string $path): string
    {
        $fullPath = module_path($moduleName, $resourcePath . DIRECTORY_SEPARATOR . $path . '.vue');

        if (!File::exists($fullPath)) {
            throw FilePathIsIncorrect::make($fullPath);
        }

        return $moduleName . "::" . $resourcePath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param string $string
     * @return string
     * @throws FilePathNotSpecified
     */
    private function getPath(string $string): string
    {
        if (blank($string)) {
            throw FilePathNotSpecified::make();
        }

        $path = "";
        $pathSource = $this->explodeString($string);

        foreach ($pathSource as $item) {
            $path .= $item . DIRECTORY_SEPARATOR;
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * @param string $string
     * @return array
     */
    private function explodeString(string $string): array
    {
        if (Str::contains($string, '.vue')) {
            $string = Str::before($string, '.vue');
        }

        return explode(".", $string);
    }

    /**
     * @param string $moduleName
     * @return string
     * @throws ModuleNotExist
     */
    private function getModuleName(string $moduleName): string
    {
        $moduleName = Str::studly($moduleName);

        $modules = Modules::all();

        if (!Modules::has($moduleName)) {
            throw ModuleNotExist::make($moduleName);
        }

        return $moduleName;
    }

    /**
     * @param string $source
     * @return array
     * @throws ModuleNameNotFound
     */
    private function explodeSource(string $source): array
    {
        if (stripos($source, "::", 0) === false) {
            throw ModuleNameNotFound::make();
        }

        return explode("::", $source);
    }
}
