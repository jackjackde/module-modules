<?php

declare(strict_types=1);

namespace JackJack\Modules;

use Illuminate\Support\Collection;
use LogicException;

class ModuleRegistrar
{
    /**#@+
     * Different types of components
     */
    public const string MODULE = 'module';
    public const string LIBRARY = 'library';
    public const string THEME = 'theme';
    public const string LANGUAGE = 'language';

    public const string SETUP = 'setup';

    /**
     * @var array<string, array<array-key, string>> $paths
     */
    private static array $paths = [
        self::MODULE => [],
        self::LIBRARY => [],
        self::LANGUAGE => [],
        self::THEME => [],
        self::SETUP => [],
    ];

    /**
     * Register the module.
     *
     * @param string $type
     * @param string $module
     * @param string $path
     * @return void
     */
    public static function register(string $type, string $module, string $path, string $serviceProvider = ''): void
    {

        self::validateType($type);
        if (isset(self::$paths[$type][$module])) {
            throw new \LogicException(
                ucfirst($type) . ' \'' . $module . '\' from \'' . $path . '\' '
                . 'has been already defined in \'' . self::$paths[$type][$module] . '\'.'
            );
        }
        self::$paths[$type][$module] = [
            'name' => $module,
            'path' => $path,
            'serviceProvider' => $serviceProvider,
        ];
    }

    /**
     * @param string $type
     * @return Collection
     */
    public function getPaths(string $type): Collection
    {
        self::validateType($type);
        return collect(self::$paths[$type]);
    }

    /**
     * @param string $type
     * @param string $moduleName
     * @return string|null
     */
    public function getPath(string $type, string $moduleName): ?string
    {
        self::validateType($type);
        return self::$paths[$type][$moduleName] ?? null;
    }

    /**
     * Checks if type of component is valid
     *
     * @param string $type
     * @return void
     * @throws LogicException
     */
    private static function validateType(string $type): void
    {
        if (!isset(self::$paths[$type])) {
            throw new LogicException('\'' . $type . '\' is not a valid component type');
        }
    }
}
