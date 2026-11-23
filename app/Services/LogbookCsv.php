<?php

namespace App\Services;

use App\Support\CsvSafe;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LogbookCsv
{
    public static function download(Builder $logs): StreamedResponse
    {
        $logs->with(['placement.application.user.studentProfile', 'placement.application.unit']);

        return response()->streamDownload(function () use ($logs) {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Nama', 'NIM/NIS', 'Unit', 'Tanggal', 'Aktivitas', 'Status instansi', 'Catatan instansi', 'Status dosen', 'Catatan dosen'], ',', '"', '');
            foreach ($logs->lazyById(300) as $log) {
                $application = $log->placement->application;
                fputcsv($stream, CsvSafe::row([
                    $application->user?->name, $application->user?->studentProfile?->nim,
                    $application->unit?->name, $log->date, $log->activity,
                    $log->status, $log->feedback, $log->lecturer_status, $log->lecturer_feedback,
                ]), ',', '"', '');
            }
            fclose($stream);
        }, 'logbook-'.now()->toDateString().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
