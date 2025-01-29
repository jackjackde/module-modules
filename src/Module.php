<?php

declare(strict_types=1);

namespace JackJack\Modules;

use Illuminate\Contracts\Support\Arrayable;

class Module implements Arrayable
{
    public readonly string $vendor;

    public readonly string $package;

    /**
     * @param string $name
     * @param string $path
     * @param bool $isActive
     * @throws \Exception
     */
    public function __construct(
        public string $name,
        public string $path = '' {
            get {
                return $this->path;
            }
        },
        protected string $serviceProvider = '',
        protected bool $isActive = true
    )
    {
        if (!str_contains($name, '/')) {
            throw new \Exception('Invalid module name.');
        }

        [$vendor, $name] = explode('/', $name);
        $this->vendor = $vendor;
        $this->package = $name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'vendor' => $this->vendor,
            'package' => $this->package,
            'path' => $this->path,
            'serviceProvider' => $this->serviceProvider,
            'active' => $this->isActive,
        ];
    }

    /**
     * @param array $array
     * @return Module
     * @throws \Exception
     */
    public static function __set_state(array $array): Module
    {
        return new Module(
            $array['name'],
            $array['path'],
            $array['serviceProvider'] ?? '',
            $array['isActive']
        );
    }

    /**
     * @return string
     */
    public function getServiceProvider(): string
    {
        return $this->serviceProvider;
    }

    /**
     * @return bool
     */
    public function hasServiceProvider(): bool
    {
        return !empty($this->serviceProvider);
    }

    /**
     * @return void
     */
    public function disable(): void
    {
        $this->isActive = false;
    }

    /**
     * @return void
     */
    public function enable(): void
    {
        $this->isActive = true;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getPackageLower(): string
    {
        return strtolower($this->package);
    }

    /**
     * @return string
     */
    public function getControllerNamespace(): string
    {
        return $this->vendor . '\\' . $this->package . '\Http\Controllers';
    }

    /**
     * @return string
     */
    public function getControllerPath(): string
    {
        return $this->path . '/Http/Controllers';
    }

    /**
     * @return string
     */
    public function getApiControllerNamespace(): string
    {
        return $this->vendor . '\\' . $this->package . '\Http\Controllers\Api';
    }

    /**
     * @return string
     */
    public function getApiControllerPath(): string
    {
        return $this->path . '/Http/Controllers/Api';
    }

    public function getModuleJsAlias(): string
    {
        return '@' . $this->vendor . '/' . $this->package;
    }

    public function getModuleTsAlias(): string
    {
        return '@' . $this->vendor . '/' . $this->package . '/*';
    }

    public function getModuleJsAliasPath(): string
    {
        return $this->getModuleAppPath('/') . '/views';
    }

    public function getModuleTsAliasPath(): array
    {
        return [$this->getModuleAppPath() . '/views/*'];
    }

    /**
     * @return string
     */
    public function getModuleAppPath(string $prefix = './'): string
    {
        $modulesPath = config('packager.paths.modules', 'app/code');
        return $prefix . trim(strstr($this->path, $modulesPath), '/');
    }
}
