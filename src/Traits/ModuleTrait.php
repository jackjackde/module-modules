<?php

declare(strict_types=1);

namespace JackJack\Modules\Traits;

use JackJack\Modules\Module;
use JackJack\Modules\Modules;

trait ModuleTrait
{
    protected ?Module $module = null;

    protected function getModule(?string $moduleName = null): ?Module
    {
        if ($this->module === null) {
            $moduleName = $moduleName ?? $this->moduleName;
            $modules = new Modules();
            $this->module = $modules->get($moduleName);
        }

        return $this->module;
    }

    /**
     * @param string|null $moduleName
     * @return bool
     */
    public function isModuleEnabled(?string $moduleName = null): bool
    {
        $module = $this->getModule($moduleName);
        return $module !== null && $module->isActive();
    }
}
