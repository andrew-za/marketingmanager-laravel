<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfAnnotationService
{
    /**
     * Detect annotations in a PDF file
     * Returns array of annotation data if found
     */
    public function detectAnnotations(string $filePath): array
    {
        if (!Storage::exists($filePath)) {
            return [];
        }

        $fullPath = Storage::path($filePath);
        
        if (!file_exists($fullPath)) {
            return [];
        }

        $annotations = [];

        try {
            $annotations = $this->extractAnnotationsFromPdf($fullPath);
        } catch (\Exception $e) {
            Log::warning('Failed to extract PDF annotations', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $annotations;
    }

    /**
     * Extract annotations from PDF file
     * Uses pdftotext or similar tool if available
     */
    protected function extractAnnotationsFromPdf(string $filePath): array
    {
        $annotations = [];

        if (!function_exists('shell_exec')) {
            return $annotations;
        }

        $command = escapeshellarg($filePath);
        
        if ($this->hasPdftotext()) {
            $output = shell_exec("pdftotext -layout {$command} - 2>&1");
            
            if ($output) {
                $annotations = $this->parseAnnotationsFromText($output);
            }
        }

        return $annotations;
    }

    /**
     * Check if pdftotext command is available
     */
    protected function hasPdftotext(): bool
    {
        $output = shell_exec('which pdftotext 2>&1');
        return !empty($output) && strpos($output, 'not found') === false;
    }

    /**
     * Parse annotations from extracted text
     * This is a basic implementation - can be enhanced with actual PDF parsing libraries
     */
    protected function parseAnnotationsFromText(string $text): array
    {
        $annotations = [];
        
        $lines = explode("\n", $text);
        $currentAnnotation = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            if (preg_match('/^(Note|Comment|Annotation):\s*(.+)$/i', $line, $matches)) {
                if ($currentAnnotation) {
                    $annotations[] = $currentAnnotation;
                }
                
                $currentAnnotation = [
                    'type' => strtolower($matches[1]),
                    'content' => $matches[2],
                    'page' => null,
                ];
            } elseif ($currentAnnotation && preg_match('/^Page\s+(\d+):/i', $line, $matches)) {
                $currentAnnotation['page'] = (int) $matches[1];
            } elseif ($currentAnnotation) {
                $currentAnnotation['content'] .= ' ' . $line;
            }
        }
        
        if ($currentAnnotation) {
            $annotations[] = $currentAnnotation;
        }
        
        return $annotations;
    }

    /**
     * Check if file has annotations
     */
    public function hasAnnotations(string $filePath): bool
    {
        $annotations = $this->detectAnnotations($filePath);
        return !empty($annotations);
    }
}

