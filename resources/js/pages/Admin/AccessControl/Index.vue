<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import {
    CheckCircle2,
    KeyRound,
    LockKeyhole,
    Plus,
    Save,
    Search,
    ShieldCheck,
    UserCog,
    Users,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

interface AccessUser {
    id: number
    name: string
    email: string
    roles: string[]
    direct_permissions: string[]
}

interface AccessRole {
    id: number
    name: string
    guard_name: string
    permissions: string[]
    is_system: boolean
}

interface LinkableRecord {
    id: number
    label: string
}

interface PageProps {
    flash?: { success?: string }
    [key: string]: unknown
}

const props = defineProps<{
    users: AccessUser[]
    roles: AccessRole[]
    permissions: string[]
    linkableStudents: LinkableRecord[]
    linkableInstructors: LinkableRecord[]
    roleLabels: Record<string, string>
    permissionLabels: Record<string, string>
    permissionDescriptions: Record<string, string>
}>()

const page = usePage<PageProps>()
const search = ref('')
const selectedUserId = ref<number | null>(props.users[0]?.id ?? null)
const selectedRoleId = ref<number | null>(props.roles[0]?.id ?? null)

const selectedUser = computed(() => props.users.find((user) => user.id === selectedUserId.value) ?? null)
const selectedRole = computed(() => props.roles.find((role) => role.id === selectedRoleId.value) ?? null)

const roleLabel = (role: string) => props.roleLabels[role] ?? role
const permissionLabel = (permission: string) => props.permissionLabels[permission] ?? permission
const permissionDescription = (permission: string) => props.permissionDescriptions[permission] ?? permission

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase()

    if (!term) {
        return props.users
    }

    return props.users.filter((user) =>
        [
            user.name,
            user.email,
            ...user.roles,
            ...user.roles.map((role) => roleLabel(role)),
            ...user.direct_permissions,
            ...user.direct_permissions.map((permission) => permissionLabel(permission)),
        ]
            .some((value) => value.toLowerCase().includes(term)),
    )
})

const userForm = useForm({
    roles: [] as string[],
    direct_permissions: [] as string[],
})

const roleForm = useForm({
    permissions: [] as string[],
})

const newRoleForm = useForm({
    name: '',
    permissions: [] as string[],
})

const newUserForm = useForm({
    name: '',
    email: '',
    password: '',
    roles: [] as string[],
    linked_type: '' as '' | 'student' | 'instructor' | 'staff',
    linked_id: '',
})

const linkOptions = computed(() => {
    if (newUserForm.linked_type === 'student') {
        return props.linkableStudents ?? []
    }

    if (newUserForm.linked_type === 'instructor') {
        return props.linkableInstructors ?? []
    }

    return []
})

const requiresLinkedRecord = computed(() => ['student', 'instructor'].includes(newUserForm.linked_type))

const linkedRecordPlaceholder = computed(() => {
    if (newUserForm.linked_type === 'student') {
        return linkOptions.value.length > 0 ? 'اختر الطالب المراد ربطه' : 'لا يوجد طلاب غير مرتبطين بحساب'
    }

    if (newUserForm.linked_type === 'instructor') {
        return linkOptions.value.length > 0 ? 'اختر المحاضر المراد ربطه' : 'لا يوجد محاضرون غير مرتبطين بحساب'
    }

    if (newUserForm.linked_type === 'staff') {
        return 'الموظف يدار عبر الدور فقط'
    }

    return 'اختر نوع الربط أولاً'
})

const selectUser = (user: AccessUser) => {
    selectedUserId.value = user.id
    userForm.roles = [...user.roles]
    userForm.direct_permissions = [...user.direct_permissions]
}

const selectRole = (role: AccessRole) => {
    selectedRoleId.value = role.id
    roleForm.permissions = [...role.permissions]
}

if (selectedUser.value) {
    selectUser(selectedUser.value)
}

if (selectedRole.value) {
    selectRole(selectedRole.value)
}

const toggleIn = (list: string[], value: string) => {
    const index = list.indexOf(value)

    if (index >= 0) {
        list.splice(index, 1)
    } else {
        list.push(value)
    }
}

const saveUser = () => {
    if (!selectedUser.value) {
        return
    }

    userForm.put(`/admin/access-control/users/${selectedUser.value.id}`, {
        preserveScroll: true,
    })
}

const saveRole = () => {
    if (!selectedRole.value) {
        return
    }

    roleForm.put(`/admin/access-control/roles/${selectedRole.value.id}`, {
        preserveScroll: true,
    })
}

const createRole = () => {
    newRoleForm.post('/admin/access-control/roles', {
        preserveScroll: true,
        onSuccess: () => {
            newRoleForm.reset()
        },
    })
}

const createUser = () => {
    newUserForm.post('/admin/access-control/users', {
        preserveScroll: true,
        onSuccess: () => {
            newUserForm.reset()
        },
    })
}

const refreshPage = () => {
    router.reload({ only: ['users', 'roles', 'permissions', 'linkableStudents', 'linkableInstructors'] })
}
</script>

<template>
    <Head title="الأدوار والصلاحيات" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-md bg-blue-50 p-3 text-blue-800">
                                <ShieldCheck class="h-7 w-7" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-orange-600">إدارة الوصول</p>
                                <h1 class="mt-1 text-2xl font-extrabold text-blue-950">
                                    الأدوار والصلاحيات
                                </h1>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-gray-600">
                                    يمكن للمدير العام منح أكثر من دور للمستخدم، وتعديل صلاحيات كل دور أو إضافة صلاحيات مباشرة للمستخدم.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3">
                                <p class="text-xs font-bold text-blue-700">المستخدمون</p>
                                <p class="mt-1 text-xl font-extrabold text-blue-950">{{ users.length }}</p>
                            </div>
                            <div class="rounded-md border border-green-100 bg-green-50 px-4 py-3">
                                <p class="text-xs font-bold text-green-700">الأدوار</p>
                                <p class="mt-1 text-xl font-extrabold text-green-950">{{ roles.length }}</p>
                            </div>
                            <div class="rounded-md border border-orange-100 bg-orange-50 px-4 py-3">
                                <p class="text-xs font-bold text-orange-700">الصلاحيات</p>
                                <p class="mt-1 text-xl font-extrabold text-orange-950">{{ permissions.length }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div
                v-if="page.props.flash?.success"
                class="flex items-center gap-2 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-bold text-green-800"
            >
                <CheckCircle2 class="h-5 w-5" />
                {{ page.props.flash.success }}
            </div>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-2">
                    <Plus class="h-5 w-5 text-orange-600" />
                    <h2 class="text-lg font-extrabold text-gray-950">إنشاء مستخدم وربطه بسجل موجود</h2>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-4">
                    <div>
                        <input
                            v-model="newUserForm.name"
                            type="text"
                            placeholder="اسم المستخدم"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        />
                        <p v-if="newUserForm.errors.name" class="mt-1 text-xs font-bold text-red-600">
                            {{ newUserForm.errors.name }}
                        </p>
                    </div>
                    <div>
                        <input
                            v-model="newUserForm.email"
                            type="email"
                            placeholder="البريد الإلكتروني"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        />
                        <p v-if="newUserForm.errors.email" class="mt-1 text-xs font-bold text-red-600">
                            {{ newUserForm.errors.email }}
                        </p>
                    </div>
                    <div>
                        <input
                            v-model="newUserForm.password"
                            type="password"
                            placeholder="كلمة المرور"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        />
                        <p v-if="newUserForm.errors.password" class="mt-1 text-xs font-bold text-red-600">
                            {{ newUserForm.errors.password }}
                        </p>
                    </div>
                    <div>
                        <select
                            v-model="newUserForm.linked_type"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                            @change="newUserForm.linked_id = ''"
                        >
                            <option value="">بدون ربط</option>
                            <option value="student">ربط بطالب</option>
                            <option value="instructor">ربط بمحاضر</option>
                            <option value="staff">موظف - دور فقط</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-start">
                    <div>
                        <select
                            v-model="newUserForm.linked_id"
                            :disabled="!requiresLinkedRecord"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        >
                            <option value="">{{ linkedRecordPlaceholder }}</option>
                            <option v-for="record in linkOptions" :key="record.id" :value="String(record.id)">
                                {{ record.label }}
                            </option>
                        </select>
                        <p v-if="requiresLinkedRecord && linkOptions.length === 0" class="mt-1 text-xs font-bold text-amber-700">
                            لا توجد سجلات متاحة للربط حالياً. السجلات المرتبطة بحساب سابق لا تظهر هنا حتى لا يتم ربط طالب أو محاضر بحسابين.
                        </p>
                        <p v-if="newUserForm.errors.linked_id" class="mt-1 text-xs font-bold text-red-600">
                            {{ newUserForm.errors.linked_id }}
                        </p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            ربط الموظف لا يحتاج جدولاً منفصلاً حالياً، ويدار عبر دور المستخدم.
                        </p>
                    </div>

                    <div class="grid max-h-32 gap-2 overflow-y-auto rounded-md border border-gray-100 p-2 sm:grid-cols-2">
                        <label
                            v-for="role in roles"
                            :key="role.id"
                            class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-blue-50"
                        >
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-700"
                                :checked="newUserForm.roles.includes(role.name)"
                                @change="toggleIn(newUserForm.roles, role.name)"
                            />
                            <span>{{ roleLabel(role.name) }}</span>
                        </label>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600 disabled:opacity-60"
                        :disabled="newUserForm.processing || !newUserForm.name || !newUserForm.email || !newUserForm.password || (requiresLinkedRecord && !newUserForm.linked_id)"
                        @click="createUser"
                    >
                        <Plus class="h-4 w-4" />
                        إنشاء المستخدم
                    </button>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[420px_minmax(0,1fr)]">
                <aside class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-4">
                        <div class="flex items-center gap-2">
                            <Users class="h-5 w-5 text-blue-800" />
                            <h2 class="text-lg font-extrabold text-gray-950">المستخدمون</h2>
                        </div>
                        <div class="relative mt-3">
                            <Search class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="بحث بالاسم أو البريد أو الدور..."
                                class="w-full rounded-md border border-gray-300 py-2 pr-9 pl-3 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                            />
                        </div>
                    </div>

                    <div class="max-h-[640px] overflow-y-auto p-3">
                        <button
                            v-for="user in filteredUsers"
                            :key="user.id"
                            type="button"
                            class="mb-2 w-full rounded-md border p-3 text-right transition hover:border-blue-300 hover:bg-blue-50/50"
                            :class="selectedUserId === user.id ? 'border-blue-700 bg-blue-50' : 'border-gray-200 bg-white'"
                            @click="selectUser(user)"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-gray-950">{{ user.name }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ user.email }}</p>
                                </div>
                                <UserCog class="h-5 w-5 shrink-0 text-blue-700" />
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role"
                                    class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-bold text-blue-800"
                                >
                                    {{ roleLabel(role) }}
                                </span>
                                <span v-if="user.roles.length === 0" class="text-xs text-gray-400">بدون دور</span>
                            </div>
                        </button>
                    </div>
                </aside>

                <div class="space-y-6">
                    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <UserCog class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">
                                    أدوار وصلاحيات المستخدم
                                </h2>
                            </div>
                            <p v-if="selectedUser" class="mt-1 text-sm text-gray-500">
                                {{ selectedUser.name }} - {{ selectedUser.email }}
                            </p>
                        </div>

                        <div v-if="selectedUser" class="grid gap-5 p-4 lg:grid-cols-2">
                            <div>
                                <h3 class="mb-3 text-sm font-extrabold text-gray-900">الأدوار</h3>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="role in roles"
                                        :key="role.id"
                                        class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm font-bold hover:bg-blue-50"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-700"
                                            :checked="userForm.roles.includes(role.name)"
                                            @change="toggleIn(userForm.roles, role.name)"
                                        />
                                        <span>{{ roleLabel(role.name) }}</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <h3 class="mb-3 text-sm font-extrabold text-gray-900">صلاحيات مباشرة</h3>
                                <div class="grid max-h-72 gap-2 overflow-y-auto rounded-md border border-gray-100 p-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in permissions"
                                        :key="permission"
                                        class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-orange-50"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-orange-600"
                                            :checked="userForm.direct_permissions.includes(permission)"
                                            @change="toggleIn(userForm.direct_permissions, permission)"
                                        />
                                        <span class="min-w-0">
                                            <span class="block leading-6 text-gray-900">{{ permissionLabel(permission) }}</span>
                                            <span class="block text-[11px] leading-5 text-gray-500">
                                                {{ permissionDescription(permission) }}
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="lg:col-span-2 flex justify-end">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-md bg-blue-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-900 disabled:opacity-60"
                                    :disabled="userForm.processing"
                                    @click="saveUser"
                                >
                                    <Save class="h-4 w-4" />
                                    حفظ صلاحيات المستخدم
                                </button>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <LockKeyhole class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">صلاحيات الأدوار</h2>
                            </div>
                        </div>

                        <div class="grid gap-4 p-4 lg:grid-cols-[260px_minmax(0,1fr)]">
                            <div class="space-y-2">
                                <button
                                    v-for="role in roles"
                                    :key="role.id"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-md border px-3 py-2 text-sm font-bold"
                                    :class="selectedRoleId === role.id ? 'border-blue-700 bg-blue-50 text-blue-900' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                                    @click="selectRole(role)"
                                >
                                    <span>{{ roleLabel(role.name) }}</span>
                                    <span v-if="role.is_system" class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500">
                                        نظام
                                    </span>
                                </button>
                            </div>

                            <div v-if="selectedRole" class="space-y-4">
                                <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                                    <strong>{{ roleLabel(selectedRole.name) }}</strong>
                                    <span v-if="selectedRole.name === 'super_admin'">
                                        يحصل دائماً على كل الصلاحيات، حتى لو تغيرت القائمة.
                                    </span>
                                </div>

                                <div class="grid max-h-80 gap-2 overflow-y-auto rounded-md border border-gray-100 p-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <label
                                        v-for="permission in permissions"
                                        :key="permission"
                                        class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-blue-50"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-gray-300 text-blue-700"
                                            :checked="selectedRole.name === 'super_admin' || roleForm.permissions.includes(permission)"
                                            :disabled="selectedRole.name === 'super_admin'"
                                            @change="toggleIn(roleForm.permissions, permission)"
                                        />
                                        <span class="min-w-0">
                                            <span class="block leading-6 text-gray-900">{{ permissionLabel(permission) }}</span>
                                            <span class="block text-[11px] leading-5 text-gray-500">
                                                {{ permissionDescription(permission) }}
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-md bg-blue-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-900 disabled:opacity-60"
                                        :disabled="roleForm.processing"
                                        @click="saveRole"
                                    >
                                        <KeyRound class="h-4 w-4" />
                                        حفظ صلاحيات الدور
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2">
                            <Plus class="h-5 w-5 text-orange-600" />
                            <h2 class="text-lg font-extrabold text-gray-950">إنشاء دور جديد</h2>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-[280px_minmax(0,1fr)_auto] lg:items-start">
                            <div>
                                <input
                                    v-model="newRoleForm.name"
                                    type="text"
                                    placeholder="example_role"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                />
                                <p v-if="newRoleForm.errors.name" class="mt-1 text-xs font-bold text-red-600">
                                    {{ newRoleForm.errors.name }}
                                </p>
                            </div>

                            <div class="grid max-h-32 gap-2 overflow-y-auto rounded-md border border-gray-100 p-2 sm:grid-cols-2 lg:grid-cols-3">
                                <label
                                    v-for="permission in permissions"
                                    :key="permission"
                                    class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-orange-50"
                                >
                                    <input
                                        type="checkbox"
                                        class="rounded border-gray-300 text-orange-600"
                                        :checked="newRoleForm.permissions.includes(permission)"
                                        @change="toggleIn(newRoleForm.permissions, permission)"
                                    />
                                    <span class="min-w-0">
                                        <span class="block leading-6 text-gray-900">{{ permissionLabel(permission) }}</span>
                                        <span class="block text-[11px] leading-5 text-gray-500">
                                            {{ permissionDescription(permission) }}
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 rounded-md bg-orange-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-600 disabled:opacity-60"
                                :disabled="newRoleForm.processing || !newRoleForm.name"
                                @click="createRole"
                            >
                                <Plus class="h-4 w-4" />
                                إنشاء
                            </button>
                        </div>
                    </section>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="text-sm font-bold text-blue-800 hover:text-orange-600"
                            @click="refreshPage"
                        >
                            تحديث البيانات
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

