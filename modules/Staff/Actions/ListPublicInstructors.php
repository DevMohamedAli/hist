<?php

namespace Modules\Staff\Actions;

use Illuminate\Support\Collection;
use Modules\Staff\Models\Instructor;

class ListPublicInstructors
{
    public function execute(): Collection
    {
        return Instructor::query()
            ->with('department:id,name')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'department_id', 'name', 'academic_rank', 'email'])
            ->map(fn (Instructor $instructor): array => [
                'id' => $instructor->id,
                'name' => $instructor->name,
                'academic_rank' => $instructor->academic_rank,
                'email' => $instructor->email,
                'department' => $instructor->department ? [
                    'id' => $instructor->department->id,
                    'name' => $instructor->department->name,
                ] : null,
            ]);
    }
}
