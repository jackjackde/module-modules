<?php

namespace JackJack\Modules\Commands;

use Illuminate\Console\Command;
use JackJack\Modules\Modules;
use JeroenG\Packager\Conveyor;
use JeroenG\Packager\ProgressBar;
use JeroenG\Packager\Wrapping;

/**
 * Create a new package and module.
 *
 * @author Stefan
 **/
class RegisterModuleRoutes extends Command
{
    use ProgressBar;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:routes:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all module routes.';

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
        // Start the progress bar
        $this->startProgressBar(1);

        // Start disable module
        $this->info(
            'Scan module controller directories...');

        $this->modules->registerController();

        $this->makeProgress();

        $this->finishProgress('Routes registered!');

        return 0;
    }
}
