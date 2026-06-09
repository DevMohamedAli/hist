<?php

namespace Modules\Qualification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Modules\Staff\Models\Instructor;

class Qualification extends Model
{
    protected $fillable = [
        'instructor_id',
        'qualifiable_type',
        'qualifiable_id',
        'degree_name',
        'institution',
    ];

    protected static function booted(): void
    {
        static::saving(function (Qualification $qualification): void {
            if ($qualification->instructor_id && ! $qualification->qualifiable_type && ! $qualification->qualifiable_id) {
                $qualification->qualifiable_type = Instructor::class;
                $qualification->qualifiable_id = $qualification->instructor_id;
            }
        });
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function qualifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function instructors(): MorphedByMany
    {
        return $this->morphedByMany(Instructor::class, 'qualifiable', 'qualificationables')
            ->withTimestamps();
    }

    public static function findMatching(string $degreeName, string $institution): ?self
    {
        $degreeKey = self::textKey($degreeName);
        $institutionKey = self::textKey($institution);

        return self::query()
            ->get()
            ->first(function (Qualification $qualification) use ($degreeKey, $institutionKey): bool {
                return self::textKey($qualification->degree_name) === $degreeKey
                    && self::textKey($qualification->institution) === $institutionKey;
            });
    }

    public static function firstOrCreateByText(string $degreeName, string $institution): self
    {
        $degreeName = self::cleanText($degreeName);
        $institution = self::cleanText($institution);

        return self::findMatching($degreeName, $institution)
            ?? self::query()->create([
                'instructor_id' => null,
                'qualifiable_type' => null,
                'qualifiable_id' => null,
                'degree_name' => $degreeName,
                'institution' => $institution,
            ]);
    }

    public static function cleanText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    public static function textKey(?string $value): string
    {
        $value = self::cleanText((string) $value);
        $value = str_replace(['أ', 'إ', 'آ'], 'ا', $value);
        $value = str_replace('ى', 'ي', $value);
        $value = str_replace('ة', 'ه', $value);

        return Str::lower($value);
    }
}
