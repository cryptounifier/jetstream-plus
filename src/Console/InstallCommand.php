<?php

namespace CryptoUnifier\JetstreamPlus\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jetstream-plus:install  {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Jetstream Plus components and resources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-concrete-configs', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-concrete-migrations', '--force' => true]);

        (new Filesystem())->copyDirectory(__DIR__ . '/../../stubs/app/Actions', app_path('Actions'));
        (new Filesystem())->copyDirectory(__DIR__ . '/../../stubs/app/Models', app_path('Models'));

        (new Filesystem())->copyDirectory(__DIR__ . '/../../stubs/tests', base_path('tests'));

        $this->installServiceMiddlewareAfter(
            "'verified' => \\Illuminate\\Auth\\Middleware\\EnsureEmailIsVerified::class,",
            "'not-banned' => \\CryptoUnifier\\JetstreamPlus\\Http\\Middleware\\RedirectBannedUser::class,"
        );

        return Command::SUCCESS;
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param mixed $packages
     */
    protected function requireDevComposerPackages($packages): void
    {
        $this->requireComposerPackagesInternal($packages, true);
    }

    /**
     * Installs the given Composer Packages into the application.
     */
    protected function requireComposerPackagesInternal(array $packages, bool $dev = false): void
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require', ($dev) ? '--dev' : ''],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output): void {
                $this->output->write($output);
            });
    }
}
