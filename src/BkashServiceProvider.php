<?php

namespace ItsRafsanJani\Bkash;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ItsRafsanJani\Bkash\Commands\BkashCommand;

class BkashServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-bkash')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-bkash_table')
            ->hasCommand(BkashCommand::class);
    }
}
