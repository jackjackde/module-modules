<?php

namespace JackJack\Modules\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Validator as ValidatorInterface;
use Illuminate\Support\Facades\Validator;
use JackJack\Modules\ModuleRegistrar;
use JackJack\Modules\Modules;
use JeroenG\Packager\Conveyor;
use JeroenG\Packager\ProgressBar;
use JeroenG\Packager\ValidationRules\ValidClassName;
use JeroenG\Packager\Wrapping;

/**
 * Reinit modules, refresh modules and controllers cache.
 *
 * @author Stefan
 **/
class RefreshModulesCache extends Command
{
    use ProgressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reinit modules, refresh modules and controllers cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected Modules $modules,
        protected Conveyor $conveyor,
        protected Wrapping $wrapping
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // $this->conveyor->dumpAutoloads();

        $modulesPath = base_path(config('packager.paths.modules', 'app/code'));

        $fileNameToSearch = 'registration.php'; // Gesuchte Datei
        // Start
        $this->info(
            'Scanning modules path...');

        $moduleRegistration = new ModuleRegistrar();
        $foundModulesPaths = $moduleRegistration->getPaths(ModuleRegistrar::MODULE);

        // Start the progress bar
        $this->startProgressBar(count($foundModulesPaths));
        $this->line('');
        $somethingToRegister = false;

        foreach ($foundModulesPaths as $module) {
            [$vendor, $name] = explode('/', $module['path']);

            $this->conveyor->vendor($vendor);
            $this->conveyor->package($name);

            // Validate the vendor and package names
            $validator = $this->validateInput($this->conveyor->vendor(), $this->conveyor->package());

            if ($validator->fails()) {
                $this->showErrors($validator);

                return 1;
            }

            try {
                if (!$this->modules->has($module['name'])) {
                    $somethingToRegister = true;
                    $this->modules->register(new \JackJack\Modules\Module(
                        $module['name'],
                        $module['path'],
                        $module['serviceProvider'],
                    ));

                    $this->info('Registered module ' . $module['name'] . '...');
                }
                $this->makeProgress();
            } catch (Exception $e) {
                $this->error('Module registration failed: ' . $e->getMessage());
                return 1;
            }
        }

        if (!$somethingToRegister) {
            $this->info("\n" . 'No new modules found.');
        }

        $this->modules->afterSetup();

        // Finished creating the package, end of the progress bar
        $this->finishProgress("\n" . 'Modules successfully refreshed!');

        return 0;
    }

    private function validateInput(string $vendor, string $name)
    {
        return Validator::make(compact('vendor', 'name'), [
            'vendor' => new ValidClassName,
            'name' => new ValidClassName,
        ]);
    }

    private function showErrors(ValidatorInterface $validator): void
    {
        $this->info('Package was not created. Please choose a valid name.');

        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }
    }
}
