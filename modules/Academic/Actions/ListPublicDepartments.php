<?php

namespace Modules\Academic\Actions;

use Illuminate\Support\Collection;
use Modules\Academic\Models\Department;

class ListPublicDepartments
{
    public function execute(): Collection
    {
        return Department::query()
            ->with(['specializations:id,department_id,name,code,description,semesters_count'])
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'description'])
            ->map(fn (Department $department): array => [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'description' => $department->description,
                'specializations' => $department->specializations
                    ->map(fn ($specialization): array => [
                        'id' => $specialization->id,
                        'name' => $specialization->name,
                        'code' => $specialization->code,
                        'description' => $specialization->description,
                        'semesters_count' => $specialization->semesters_count,
                    ])
                    ->values(),
            ]);
    }
}
