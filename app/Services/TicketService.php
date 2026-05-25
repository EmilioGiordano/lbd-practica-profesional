<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TicketService
{
    public function getNextTicketNumber(): int
    {
        $ticketsPath = resource_path('views/tickets');
        $files = File::files($ticketsPath);

        $numbers = collect($files)
            ->map(function ($file) {
                if (preg_match('/ticket-(\d+)\.blade\.php$/', $file->getFilename(), $matches)) {
                    return (int) $matches[1];
                }
                return null;
            })
            ->filter()
            ->max() ?? 0;

        return $numbers + 1;
    }

    public function createTicket(int $number, string $title): array
    {
        $viewPath = resource_path("views/tickets/ticket-{$number}.blade.php");
        $queryPath = database_path("queries/ticket-{$number}.sql");

        if (File::exists($viewPath) || File::exists($queryPath)) {
            return [
                'success' => false,
                'message' => "Ticket {$number} ya existe.",
            ];
        }

        $bladeContent = <<<EOT
<x-app-layout>
    <div class="py-8">
        <div class="mx-auto w-full max-w-[1800px] px-4 sm:px-6 lg:px-10">
            <x-ticket-playground :ticket-number="{$number}" title="{$title}" />
        </div>
    </div>
</x-app-layout>
EOT;

        $sqlContent = "SELECT * FROM carriers\n";

        File::put($viewPath, $bladeContent);
        File::put($queryPath, $sqlContent);

        return [
            'success' => true,
            'message' => "Ticket {$number} creado exitosamente.",
            'number' => $number,
            'title' => $title,
        ];
    }
}
