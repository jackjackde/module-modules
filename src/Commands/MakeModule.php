<?php

namespace JackJack\Modules\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Validator as ValidatorInterface;
use Illuminate\Support\Facades\Validator;
use JackJack\Modules\Modules;
use JeroenG\Packager\Conveyor;
use JeroenG\Packager\ProgressBar;
use JeroenG\Packager\ValidationRules\ValidClassName;
use JeroenG\Packager\Wrapping;

/**
 * Create a new package and module.
 *
 * @author Stefan
 **/
class MakeModule extends Command
{
    use ProgressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:make {vendor?} {name?} {--i}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new package and module.';

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

        $skeleton = config('modules.skeleton');

        // Start the progress bar
        $this->startProgressBar(1);

        $vendor = $this->argument('vendor') ?? 'vendor-name';
        $name = $this->argument('name') ?? 'package-name';

        if (str_contains($vendor, '/')) {
            [$vendor, $name] = explode('/', $vendor);
        }

        // Defining vendor/package, optionally defined interactively
        if ($this->option('i')) {
            $this->conveyor->vendor($this->ask('What will be the vendor name?', $vendor));
            $this->conveyor->package($this->ask('What will be the package name?', $name));
        } else {
            $this->conveyor->vendor($vendor);
            $this->conveyor->package($name);
        }

        // Validate the vendor and package names
        $validator = $this->validateInput($this->conveyor->vendor(), $this->conveyor->package());

        if ($validator->fails()) {
            $this->showErrors($validator);

            return 1;
        }

        // create a new package
        $this->call('packager:new', [
            'vendor' => $this->argument('vendor'),
            'name' => $this->argument('name'),
            '--i' => $this->option('i'),
            '--skeleton' => $skeleton,
        ]);

        // Start creating the package
        $this->info(
            'Adding and enabling module package ' . $this->conveyor->vendor() . '\\' . $this->conveyor->package() . '...');

        $paths = [
            $this->conveyor->packagePath(),
            $this->conveyor->packagePath() . DIRECTORY_SEPARATOR . 'src',
        ];

        try {
            foreach ($paths as $path) {
                if (file_exists($path . DIRECTORY_SEPARATOR . 'registration.php')) {
                    include_once $path . DIRECTORY_SEPARATOR . 'registration.php';
                }
            }
        } catch (Exception $e) {
            $this->error('Module registration failed: ' . $e->getMessage());
            return 1;
        }

        $this->makeProgress();

        $this->conveyor->dumpAutoloads();
        $this->modules->afterSetup();

        // Finished creating the package, end of the progress bar
        $this->finishProgress('Module successfully installed!');

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
