<?php

namespace Modules\Website\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteImageManager
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function replace(?string $previousPath, UploadedFile $file, string $directory): string
    {
        $this->deleteManaged($previousPath);

        return $this->store($file, $directory);
    }

    public function deleteManaged(?string $path): void
    {
        if (! $this->isManagedPath($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    public function isManagedPath(?string $path): bool
    {
        return filled($path) && ! Str::startsWith($path, ['http://', 'https://', '/']);
    }
}
