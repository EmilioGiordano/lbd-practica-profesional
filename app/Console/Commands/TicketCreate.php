<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class TicketCreate extends Command
{
    protected $signature = 'ticket:create {numero : Número del ticket} {titulo : Título del ticket}';

    protected $description = 'Genera la vista y el archivo SQL base para un ticket del SQL Playground';

    public function handle(TicketService $ticketService)
    {
        $numero = (int) $this->argument('numero');
        $titulo = $this->argument('titulo');

        if ($numero <= 0) {
            $this->error('El número del ticket debe ser mayor a 0.');
            return 1;
        }

        if (empty(trim($titulo))) {
            $this->error('El título del ticket no puede estar vacío.');
            return 1;
        }

        $result = $ticketService->createTicket($numero, $titulo);

        if ($result['success']) {
            $this->info($result['message']);
            return 0;
        }

        $this->error($result['message']);
        return 1;
    }
}
