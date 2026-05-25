<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('ticket:create {numero} {titulo}', function (string $numero, string $titulo) {
    $number = (int) $numero;
    $title = trim($titulo);

    if ($number <= 0) {
        $this->error('El numero del ticket debe ser mayor que cero.');

        return 1;
    }

    if ($title === '') {
        $this->error('El titulo no puede estar vacio.');

        return 1;
    }

    $viewPath = resource_path("views/tickets/ticket-{$number}.blade.php");
    $sqlPath = database_path("queries/ticket-{$number}.sql");

    File::ensureDirectoryExists(dirname($viewPath));
    File::ensureDirectoryExists(dirname($sqlPath));

    if (File::exists($viewPath) || File::exists($sqlPath)) {
        $this->error('Ya existe la vista o el archivo SQL para ese ticket.');

        return 1;
    }

    $viewContents = <<<BLADE
<x-app-layout>
    <div class="py-8">
        <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-10">
            <x-ticket-playground :ticket-number="{$number}" title="{$title}" />
        </div>
    </div>
</x-app-layout>
BLADE;

    $sqlContents = <<<SQL
SELECT 1 AS ejemplo
SQL;

    File::put($viewPath, $viewContents);
    File::put($sqlPath, $sqlContents);

    $this->info("Ticket {$number} creado correctamente.");
    $this->line($viewPath);
    $this->line($sqlPath);

    return 0;
})->purpose('Genera la vista y el archivo SQL base para un ticket del SQL Playground');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
