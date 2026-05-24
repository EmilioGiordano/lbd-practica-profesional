<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TicketController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $forbiddenPatterns = [
        '/;/',
        '/\bDROP\s+TABLE\b/i',
        '/\bDELETE\b/i',
        '/\bINSERT\b/i',
        '/\bUPDATE\b/i',
        '/\bTRUNCATE\b/i',
        '/\bALTER\s+TABLE\b/i',
        '/\bGRANT\b/i',
        '/\bREVOKE\b/i',
        '/\bEXECUTE\b/i',
        '/\bCALL\b/i',
    ];

    public function show(int $ticketNumber)
    {
        $view = "tickets.ticket-{$ticketNumber}";

        abort_unless(view()->exists($view), Response::HTTP_NOT_FOUND, 'La vista del ticket no existe.');

        return view($view, [
            'ticketNumber' => $ticketNumber,
        ]);
    }

    public function execute(int $ticketNumber): JsonResponse
    {
        $sqlPath = database_path("queries/ticket-{$ticketNumber}.sql");

        abort_unless(File::exists($sqlPath), Response::HTTP_NOT_FOUND, 'El archivo SQL del ticket no existe.');

        $sql = trim(File::get($sqlPath));

        if ($sql === '') {
            return response()->json([
                'message' => 'El archivo SQL esta vacio.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($message = $this->validateSqlSecurity($sql)) {
            return response()->json([
                'message' => $message,
            ], Response::HTTP_FORBIDDEN);
        }

        if (! $this->startsWithAllowedStatement($sql)) {
            return response()->json([
                'message' => 'Solo se permiten consultas SELECT, WITH/RECURSIVE, EXPLAIN, CREATE INDEX y DROP INDEX.',
            ], Response::HTTP_FORBIDDEN);
        }

        $bindings = $this->resolveBindings($sql);

        if (isset($bindings['error'])) {
            return response()->json([
                'message' => $bindings['error'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isResultSetQuery = $this->isResultSetQuery($sql);
        $sqlToExecute = $this->applySafetyLimitIfNeeded($sql);
        $startedAt = hrtime(true);

        try {
            if ($isResultSetQuery) {
                $rows = collect(DB::select($sqlToExecute, $bindings));
                $executionTimeMs = round((hrtime(true) - $startedAt) / 1_000_000, 2);
                $normalizedRows = $rows
                    ->map(fn (object $row) => (array) $row)
                    ->values()
                    ->all();

                return response()->json([
                    'columns' => array_keys($normalizedRows[0] ?? []),
                    'rows' => $normalizedRows,
                    'executionTimeMs' => $executionTimeMs,
                    'rowCount' => count($normalizedRows),
                ]);
            }

            DB::statement($sqlToExecute, $bindings);
            $executionTimeMs = round((hrtime(true) - $startedAt) / 1_000_000, 2);

            return response()->json([
                'columns' => [],
                'rows' => [],
                'executionTimeMs' => $executionTimeMs,
                'rowCount' => 0,
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'message' => $exception->getPrevious()?->getMessage() ?? $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateSqlSecurity(string $sql): ?string
    {
        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $sql) === 1) {
                return 'La consulta contiene una instruccion prohibida. Solo se permiten operaciones de lectura, EXPLAIN y gestion de indices sin punto y coma.';
            }
        }

        return null;
    }

    private function startsWithAllowedStatement(string $sql): bool
    {
        $normalizedSql = Str::upper($this->stripLeadingComments($sql));

        return preg_match('/^(SELECT|WITH|EXPLAIN|CREATE\s+(UNIQUE\s+)?INDEX|DROP\s+INDEX)/', $normalizedSql) === 1;
    }

    private function isResultSetQuery(string $sql): bool
    {
        return preg_match('/^(SELECT|WITH|EXPLAIN)/', Str::upper($this->stripLeadingComments($sql))) === 1;
    }

    private function applySafetyLimitIfNeeded(string $sql): string
    {
        $normalizedSql = Str::upper($this->stripLeadingComments($sql));

        if (preg_match('/^(SELECT|WITH)/', $normalizedSql) !== 1) {
            return $sql;
        }

        if (preg_match('/\bLIMIT\b/i', $sql) === 1) {
            return $sql;
        }

        return "SELECT * FROM ({$sql}) AS subq LIMIT 1000";
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveBindings(string $sql): array
    {
        preg_match_all('/(?<!:):([A-Za-z_][A-Za-z0-9_]*)/', $sql, $matches);

        $placeholders = array_values(array_unique($matches[1] ?? []));

        if ($placeholders === []) {
            return [];
        }

        $bindings = [];

        foreach ($placeholders as $placeholder) {
            $value = request()->query($placeholder);

            if ($value === null || $value === '') {
                return [
                    'error' => "Falta el parametro requerido :{$placeholder}. Envialo en la query string.",
                ];
            }

            $bindings[$placeholder] = $value;
        }

        return $bindings;
    }

    private function stripLeadingComments(string $sql): string
    {
        $normalizedSql = ltrim($sql);

        while (true) {
            if (preg_match('/^--[^\r\n]*(\r\n|\r|\n)?/', $normalizedSql, $matches) === 1) {
                $normalizedSql = ltrim(substr($normalizedSql, strlen($matches[0])));

                continue;
            }

            if (preg_match('/^\/\*.*?\*\//s', $normalizedSql, $matches) === 1) {
                $normalizedSql = ltrim(substr($normalizedSql, strlen($matches[0])));

                continue;
            }

            break;
        }

        return $normalizedSql;
    }
}
