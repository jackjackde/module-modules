<?php

declare(strict_types=1);

namespace JackJack\Modules\src\Services;

class TsConfig extends ConfigFile
{
    /**
     * @var string $aliasesFilePath
     */
    protected string $configFile = 'tsconfig.json';

    protected array $data;

    protected function readAliases(): array
    {
        $this->data = parent::readAliases();

        if (isset($this->data['compilerOptions']['paths'])) {
            return $this->data['compilerOptions']['paths'];
        }

        return [];
    }

    protected function getData(): array
    {
        $this->data['compilerOptions']['paths'] = $this->getAliases();
        return $this->data;
    }
}
