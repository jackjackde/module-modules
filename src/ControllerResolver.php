<?php

declare(strict_types=1);

namespace JackJack\Modules;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JackJack\Modules\Dto\ControllerDirectoryDto;

/**
 * Class ControllerResolver
 *
 * @package JackJack\Modules
 */
class ControllerResolver implements Arrayable
{
    /**
     * @var Collection
     */
    private Collection $items;

    /**
     * ControllerResolver constructor.
     */
    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @param Module $module
     * @param string $directory
     * @param bool $isApi
     * @return $this
     */
    public function add(Module $module, string $directory, bool $isApi = false): self
    {
        if ($module->isActive()) {
            $this->items->push(
                new ControllerDirectoryDto($module, $directory, $isApi)
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->items as $item) {
            $data[$item->getControllerPath()] = $item->toArray();
        }

        return $data;
    }
}
