<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DocumentConversionService
{
    public function convertWordToPdf(string $publicDiskPath): array
    {
        $binary = $this->libreOfficeBinary();

        if (! $binary) {
            return ['path' => null, 'status' => 'missing_converter'];
        }

        $sourcePath = Storage::disk('public')->path($publicDiskPath);
        $outputDirectory = Storage::disk('public')->path('documents/iso-17025/pdf');

        if (! is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0775, true);
        }

        $process = new Process([
            $binary,
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $outputDirectory,
            $sourcePath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            return ['path' => null, 'status' => 'conversion_failed'];
        }

        $pdfFileName = pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
        $pdfPath = 'documents/iso-17025/pdf/' . $pdfFileName;

        if (! Storage::disk('public')->exists($pdfPath)) {
            return ['path' => null, 'status' => 'conversion_failed'];
        }

        return ['path' => $pdfPath, 'status' => 'converted'];
    }

    private function libreOfficeBinary(): ?string
    {
        $candidates = array_filter([
            config('documents.libreoffice_binary'),
            'soffice',
            'libreoffice',
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR) || str_contains($candidate, ':')) {
                if (is_file($candidate)) {
                    return $candidate;
                }

                continue;
            }

            $process = new Process([$candidate, '--version']);
            $process->setTimeout(10);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        }

        return null;
    }
}
