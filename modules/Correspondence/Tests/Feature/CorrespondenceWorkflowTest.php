<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Correspondence\Actions\CreateCorrespondence;
use Modules\Correspondence\Actions\GenerateCorrespondenceReference;
use Modules\Correspondence\Actions\SubmitCorrespondence;
use Modules\Correspondence\Enums\CorrespondenceClassification;
use Modules\Correspondence\Enums\CorrespondenceRecipientType;
use Modules\Correspondence\Enums\CorrespondenceStatus;
use Modules\Correspondence\Models\CorrespondenceCategory;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach ([
        'correspondence.view-own',
        'correspondence.create',
        'correspondence.reply',
        'correspondence.view-confidential',
        'correspondence.send',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('employee', 'web')->syncPermissions([
        'correspondence.view-own',
        'correspondence.create',
        'correspondence.reply',
        'correspondence.view-confidential',
        'correspondence.send',
    ]);
});

test('authorized user can create a draft', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $correspondence = app(CreateCorrespondence::class)->execute($user, [
        'subject' => 'Draft subject',
        'body' => 'Draft body',
    ]);

    expect($correspondence->status)->toBe(CorrespondenceStatus::Draft);
});

test('reference number generation is unique', function () {
    $first = app(GenerateCorrespondenceReference::class)->execute(2026);
    $second = app(GenerateCorrespondenceReference::class)->execute(2026);

    expect($first)->toBe('COR-2026-000001')
        ->and($second)->toBe('COR-2026-000002');
});

test('sender can submit a draft and receives a reference number', function () {
    $user = User::factory()->create();
    $user->assignRole('employee');

    $correspondence = app(CreateCorrespondence::class)->execute($user, [
        'subject' => 'Submit subject',
        'body' => 'Submit body',
    ]);

    $submitted = app(SubmitCorrespondence::class)->execute($correspondence, $user);

    expect($submitted->status)->toBe(CorrespondenceStatus::Submitted)
        ->and($submitted->reference_number)->not()->toBeNull();
});

test('student cannot use staff-only category', function () {
    Permission::findOrCreate('correspondence.view-own', 'web');
    Permission::findOrCreate('correspondence.create', 'web');
    Role::findOrCreate('student', 'web')->syncPermissions(['correspondence.view-own', 'correspondence.create']);

    $student = User::factory()->create();
    $student->assignRole('student');
    $category = CorrespondenceCategory::create([
        'name' => 'Staff only',
        'code' => 'STAFF_ONLY',
        'is_student_available' => false,
    ]);

    $this->actingAs($student)
        ->post(route('correspondence.store'), [
            'category_id' => $category->id,
            'type' => 'student_request',
            'subject' => 'Request',
            'body' => 'Body',
            'priority' => 'normal',
            'classification' => CorrespondenceClassification::Internal->value,
        ])
        ->assertSessionHasErrors('category_id');
});

test('search never returns unauthorized correspondence', function () {
    $sender = User::factory()->create();
    $sender->assignRole('employee');
    $recipient = User::factory()->create();
    $recipient->assignRole('employee');
    $outsider = User::factory()->create();
    $outsider->assignRole('employee');

    $visible = app(CreateCorrespondence::class)->execute($sender, [
        'subject' => 'Visible budget request',
        'body' => 'Body',
    ]);
    $visible->recipients()->create([
        'user_id' => $recipient->id,
        'recipient_type' => CorrespondenceRecipientType::To->value,
    ]);

    app(CreateCorrespondence::class)->execute($sender, [
        'subject' => 'Hidden budget request',
        'body' => 'Body',
    ]);

    $this->actingAs($recipient)
        ->get(route('correspondence.inbox', ['search' => 'budget']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Correspondence/Inbox')
            ->has('correspondences.data', 1)
            ->where('correspondences.data.0.subject', 'Visible budget request'));

    $this->actingAs($outsider)
        ->get(route('correspondence.inbox', ['search' => 'budget']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('correspondences.data', 0));
});

test('authorized user can upload and download private attachment', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $user->assignRole('employee');

    $correspondence = app(CreateCorrespondence::class)->execute($user, [
        'subject' => 'Attachment subject',
        'body' => 'Body',
    ]);

    $this->actingAs($user)
        ->post(route('correspondence.attachments.store', $correspondence), [
            'file' => UploadedFile::fake()->create('letter.pdf', 10, 'application/pdf'),
            'description' => 'Signed letter',
        ])
        ->assertRedirect();

    $attachment = $correspondence->attachments()->first();

    expect($attachment)->not()->toBeNull();
    Storage::disk('local')->assertExists($attachment->stored_filename);

    $this->actingAs($user)
        ->get(route('correspondence.attachments.show', $attachment))
        ->assertOk();
});
