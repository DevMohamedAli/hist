<?php

use Illuminate\Support\Facades\Validator;

it('shows required validation errors in Arabic with localized qualification field names', function () {
    app()->setLocale('ar');

    $validator = Validator::make(
        ['qualifications' => [['degree_name' => 'ماجستير']]],
        ['qualifications.*.institution' => ['required']],
    );

    $message = $validator->errors()->first('qualifications.0.institution');

    expect($message)->toContain('المؤسسة المانحة')
        ->and($message)->not->toContain('The institution field is required');
});
