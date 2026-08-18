<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateCvService
{
    public function resolvePath(Candidate $candidate): ?string
    {
        if (! filled($candidate->cv_file)) {
            return null;
        }

        foreach ($this->candidatePathCandidates($candidate->cv_file) as $path) {
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    public function hasCv(Candidate $candidate): bool
    {
        return $this->resolvePath($candidate) !== null;
    }

    public function viewUrl(Candidate $candidate, string $routeName): ?string
    {
        if (! $this->hasCv($candidate)) {
            return null;
        }

        return route($routeName, $candidate);
    }

    public function stream(Candidate $candidate): StreamedResponse
    {
        $path = $this->resolvePath($candidate);

        if ($path === null) {
            abort(404, 'Không tìm thấy file CV.');
        }

        $filename = $this->downloadFilename($candidate, $path);

        return Storage::disk('public')->response($path, $filename, [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    private function downloadFilename(Candidate $candidate, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $slug = str($candidate->full_name)->slug('_');

        return "CV_{$slug}.{$extension}";
    }

    /**
     * @return list<string>
     */
    private function candidatePathCandidates(string $storedPath): array
    {
        $normalized = ltrim(str_replace('\\', '/', $storedPath), '/');

        $variants = [$normalized];

        if (str_contains($normalized, 'candidate_cvs/')) {
            $variants[] = str_replace('candidate_cvs/', 'candidate-cvs/', $normalized);
        }

        if (str_contains($normalized, 'candidate-cvs/')) {
            $variants[] = str_replace('candidate-cvs/', 'candidate_cvs/', $normalized);
        }

        return array_values(array_unique($variants));
    }
}
