<?php

namespace Modules\Qualification\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Modules\Qualification\Models\Qualification;
use Modules\Shared\Http\Controllers\Controller;

class QualificationController extends Controller
{
    public function index(): InertiaResponse
    {
        $qualifications = Qualification::query()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Qualification/Index', [
            'qualifications' => $qualifications,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $existing = Qualification::findMatching($validated['degree_name'], $validated['institution']);

        $qualification = $existing ?? Qualification::firstOrCreateByText(
            $validated['degree_name'],
            $validated['institution'],
        );

        if (! $existing) {
            activity()
                ->causedBy($request->user())
                ->performedOn($qualification)
                ->withProperties(['attributes' => $qualification->toArray()])
                ->log('تم إضافة مؤهل علمي جديد');
        }

        return redirect()->route('qualifications.index')
            ->with('success', $existing ? 'المؤهل موجود مسبقا، لم يتم إنشاء نسخة مكررة.' : 'تم إضافة المؤهل العلمي بنجاح.');
    }

    public function update(Request $request, Qualification $qualification): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $duplicate = Qualification::findMatching($validated['degree_name'], $validated['institution']);

        if ($duplicate && $duplicate->isNot($qualification)) {
            throw ValidationException::withMessages([
                'degree_name' => 'يوجد مؤهل بنفس الاسم والجهة التعليمية مسبقا.',
            ]);
        }

        $old = $qualification->toArray();
        $qualification->update([
            'degree_name' => Qualification::cleanText($validated['degree_name']),
            'institution' => Qualification::cleanText($validated['institution']),
        ]);
        $new = $qualification->fresh()->toArray();

        activity()
            ->causedBy($request->user())
            ->performedOn($qualification)
            ->withProperties(['old' => $old, 'attributes' => $new])
            ->log('تم تحديث مؤهل علمي');

        return redirect()->route('qualifications.index')
            ->with('success', 'تم تحديث المؤهل العلمي بنجاح.');
    }

    public function destroy(Qualification $qualification): RedirectResponse
    {
        $old = $qualification->toArray();
        $qualification->delete();

        activity()
            ->causedBy(request()->user())
            ->performedOn($qualification)
            ->withProperties(['old' => $old])
            ->log('تم حذف مؤهل علمي');

        return redirect()->route('qualifications.index')
            ->with('success', 'تم حذف المؤهل العلمي بنجاح.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'degree_name' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
        ]);
    }
}
