<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Shared\Http\Controllers\Controller;
use Modules\Staff\Models\Instructor;
use Modules\Student\Models\Student;
use Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with(['roles:id,name', 'permissions:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
                'direct_permissions' => $user->permissions->pluck('name')->values(),
            ]);

        $roles = Role::query()
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name'])
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->values(),
                'is_system' => in_array($role->name, ['super_admin', 'employee', 'teacher', 'student'], true),
            ]);

        $permissions = Permission::query()->orderBy('name')->pluck('name')->values();
        $roleNames = $roles->pluck('name')->values();

        return Inertia::render('Admin/AccessControl/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'linkableStudents' => Student::query()
                ->whereNull('user_id')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'registration_number'])
                ->map(fn (Student $student): array => [
                    'id' => $student->id,
                    'label' => "{$student->full_name} ({$student->registration_number})",
                ]),
            'linkableInstructors' => Instructor::query()
                ->whereNull('user_id')
                ->orderBy('name')
                ->get(['id', 'name', 'employee_id'])
                ->map(fn (Instructor $instructor): array => [
                    'id' => $instructor->id,
                    'label' => trim($instructor->name.($instructor->employee_id ? " ({$instructor->employee_id})" : '')),
                ]),
            'roleLabels' => $this->labelsFor($roleNames, 'roles'),
            'permissionLabels' => $this->labelsFor($permissions, 'permissions'),
            'permissionDescriptions' => $this->labelsFor($permissions, 'permission_descriptions'),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'linked_type' => ['nullable', 'in:student,instructor,staff'],
            'linked_id' => ['nullable', 'required_if:linked_type,student,instructor', 'integer'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->syncRoles(collect($validated['roles'] ?? [])->unique()->values()->all());

            $this->linkUserToEntity(
                $user,
                $validated['linked_type'] ?? null,
                $validated['linked_id'] ?? null,
            );

            return $user;
        });

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'roles' => $user->roles()->pluck('name')->all(),
                'linked_type' => $validated['linked_type'] ?? null,
                'linked_id' => $validated['linked_id'] ?? null,
            ])
            ->log('تم إنشاء مستخدم جديد');

        return back()->with('success', 'تم إنشاء المستخدم وربطه بنجاح.');
    }

    private function labelsFor(iterable $keys, string $group): array
    {
        return collect($keys)
            ->mapWithKeys(fn (string $key): array => [$key => $this->localizedAccessLabel($group, $key)])
            ->all();
    }

    private function localizedAccessLabel(string $group, string $key): string
    {
        $translationKey = "access.{$group}.{$key}";
        $translation = trans($translationKey, [], 'ar');

        if ($translation !== $translationKey) {
            return $translation;
        }

        return Str::of($key)
            ->replace(['_', '-'], ' ')
            ->headline()
            ->toString();
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'direct_permissions' => ['array'],
            'direct_permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $roles = collect($validated['roles'] ?? [])->unique()->values()->all();
        $permissions = collect($validated['direct_permissions'] ?? [])->unique()->values()->all();

        if ((int) $user->id === (int) $request->user()->id && ! in_array('super_admin', $roles, true)) {
            return back()->withErrors([
                'roles' => 'لا يمكنك إزالة دور المدير العام من حسابك الحالي.',
            ]);
        }

        $user->syncRoles($roles);
        $user->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'roles' => $roles,
                'direct_permissions' => $permissions,
            ])
            ->log('تم تحديث صلاحيات المستخدم');

        return back()->with('success', 'تم تحديث أدوار وصلاحيات المستخدم بنجاح.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'alpha_dash:ascii', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.alpha_dash' => 'اسم الدور يجب أن يكون بالإنجليزية وبدون مسافات. مثال: registrar_manager',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($validated['permissions'] ?? []);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'تم إنشاء الدور الجديد بنجاح.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if ($role->name === 'super_admin') {
            $role->syncPermissions(Permission::all());
        } else {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        activity()
            ->causedBy($request->user())
            ->performedOn($role)
            ->withProperties([
                'permissions' => $role->permissions()->pluck('name')->all(),
            ])
            ->log('تم تحديث صلاحيات الدور');

        return back()->with('success', 'تم تحديث صلاحيات الدور بنجاح.');
    }

    private function linkUserToEntity(User $user, ?string $linkedType, mixed $linkedId): void
    {
        if (! $linkedType || ! $linkedId) {
            return;
        }

        match ($linkedType) {
            'student' => Student::query()
                ->whereKey($linkedId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]),
            'instructor' => Instructor::query()
                ->whereKey($linkedId)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]),
            // No standalone Staff model/table currently exists. Staff access is represented by user roles.
            'staff' => null,
            default => null,
        };
    }
}
