<?php

namespace Modules\Correspondence\Actions;

use Illuminate\Http\UploadedFile;
use Modules\Correspondence\Models\Correspondence;
use Modules\Correspondence\Models\CorrespondenceAttachment;
use Modules\User\Models\User;

class AttachCorrespondenceFile
{
    public function execute(Correspondence $correspondence, User $uploader, UploadedFile $file, ?string $description = null): CorrespondenceAttachment
    {
        $disk = config('correspondence.attachments_disk', 'local');
        $path = $file->store('correspondence/'.$correspondence->id, $disk);

        return $correspondence->attachments()->create([
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $path,
            'storage_disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize() ?: 0,
            'uploaded_by' => $uploader->id,
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'visibility' => 'private',
            'description' => $description,
        ]);
    }
}
