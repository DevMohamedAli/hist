<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowRight, GraduationCap, IdCard, KeyRound, LinkIcon, Mail, Save, UserPlus } from 'lucide-vue-next'
import DateInputField from '@/components/DateInputField.vue'

interface Department {
    name: string
}

interface Specialization {
    id: number
    name: string
    department: Department
}

interface Qualification {
    id: number
    degree_name: string
    institution: string
    label?: string
}

interface UserOption {
    id: number
    label: string
}

const props = defineProps<{
    specializations: Specialization[]
    qualifications: Qualification[]
    availableUsers: UserOption[]
}>()

const form = useForm({
    full_name: '',
    national_id: '',
    gender: 'Male',
    nationality: 'ليبي',
    birth_date: '',
    mobile: '',
    admission_date: '',
    qualification_mode: 'none',
    qualification_id: '',
    new_qualification_degree_name: '',
    new_qualification_institution: '',
    current_specialization_id: '',
    account_mode: 'none',
    user_id: '',
    user_email: '',
    user_password: '',
})

const submit = () => {
    form.post('/students', {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="تسجيل طالب جديد" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-md bg-blue-50 p-3 text-blue-800">
                                <UserPlus class="h-7 w-7" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-orange-600">شؤون الطلبة</p>
                                <h1 class="mt-1 text-2xl font-extrabold text-blue-950">تسجيل طالب جديد</h1>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-gray-600">
                                    أدخل البيانات الأساسية للطالب واربطه بالتخصص المناسب. سيصدر النظام رقم القيد تلقائياً حسب تاريخ الانتساب وإعدادات المعهد.
                                </p>
                            </div>
                        </div>

                        <Link
                            href="/students"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
                        >
                            <ArrowRight class="h-4 w-4" />
                            العودة للطلاب
                        </Link>
                    </div>
                </div>
            </section>

            <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" @submit.prevent="submit">
                <section class="space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <IdCard class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">البيانات الشخصية</h2>
                            </div>
                        </div>

                        <div class="grid gap-5 p-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="full_name" class="block text-sm font-bold text-gray-700">
                                    الاسم الرباعي <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="full_name"
                                    v-model="form.full_name"
                                    type="text"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    placeholder="اسم الطالب رباعياً"
                                />
                                <p v-if="form.errors.full_name" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.full_name }}
                                </p>
                            </div>

                            <div>
                                <label for="national_id" class="block text-sm font-bold text-gray-700">
                                    الرقم الوطني <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="national_id"
                                    v-model="form.national_id"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="12"
                                    minlength="12"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    placeholder="12 رقماً"
                                />
                                <p v-if="form.errors.national_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.national_id }}
                                </p>
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-bold text-gray-700">
                                    الجنس <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="gender"
                                    v-model="form.gender"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="Male">ذكر</option>
                                    <option value="Female">أنثى</option>
                                </select>
                                <p v-if="form.errors.gender" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.gender }}
                                </p>
                            </div>

                            <DateInputField
                                id="birth_date"
                                v-model="form.birth_date"
                                label="تاريخ الميلاد"
                                required
                                placeholder="اختر تاريخ الميلاد"
                                :error="form.errors.birth_date"
                            />

                            <div>
                                <label for="nationality" class="block text-sm font-bold text-gray-700">
                                    الجنسية <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="nationality"
                                    v-model="form.nationality"
                                    type="text"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                />
                                <p v-if="form.errors.nationality" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.nationality }}
                                </p>
                            </div>

                            <div>
                                <label for="mobile" class="block text-sm font-bold text-gray-700">رقم الهاتف</label>
                                <input
                                    id="mobile"
                                    v-model="form.mobile"
                                    type="tel"
                                    class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    placeholder="اختياري"
                                />
                                <p v-if="form.errors.mobile" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.mobile }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <div class="flex items-center gap-2">
                                    <GraduationCap class="h-5 w-5 text-blue-800" />
                                    <label class="block text-sm font-bold text-gray-700">المؤهل السابق</label>
                                </div>

                                <div class="mt-3 grid gap-3 rounded-md border border-blue-100 bg-blue-50/40 p-3 md:grid-cols-[220px_minmax(0,1fr)]">
                                    <select
                                        v-model="form.qualification_mode"
                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    >
                                        <option value="none">بدون مؤهل</option>
                                        <option value="existing">اختيار مؤهل موجود</option>
                                        <option value="new">إضافة مؤهل جديد</option>
                                    </select>

                                    <select
                                        v-if="form.qualification_mode === 'existing'"
                                        id="qualification_id"
                                        v-model="form.qualification_id"
                                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    >
                                        <option value="" disabled>اختر المؤهل من الجدول...</option>
                                        <option
                                            v-for="qualification in props.qualifications"
                                            :key="qualification.id"
                                            :value="String(qualification.id)"
                                        >
                                            {{ qualification.label ?? `${qualification.degree_name} - ${qualification.institution}` }}
                                        </option>
                                    </select>

                                    <div v-else-if="form.qualification_mode === 'new'" class="grid gap-3 md:grid-cols-2">
                                        <input
                                            v-model="form.new_qualification_degree_name"
                                            type="text"
                                            class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                            placeholder="اسم المؤهل، مثال: شهادة ثانوية علمية"
                                        />
                                        <input
                                            v-model="form.new_qualification_institution"
                                            type="text"
                                            class="w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                            placeholder="المؤسسة المانحة، مثال: وزارة التعليم"
                                        />
                                    </div>

                                    <p v-else class="rounded-md border border-gray-200 bg-white px-3 py-2.5 text-sm font-bold text-gray-500">
                                        يمكن ترك المؤهل فارغاً أثناء التسجيل الأولي.
                                    </p>
                                </div>

                                <p v-if="form.errors.qualification_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.qualification_id }}
                                </p>
                                <p v-if="form.errors.new_qualification_degree_name" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.new_qualification_degree_name }}
                                </p>
                                <p v-if="form.errors.new_qualification_institution" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.new_qualification_institution }}
                                </p>
                                <p v-if="form.qualification_mode === 'new'" class="mt-2 text-xs leading-5 text-gray-500">
                                    إذا كان المؤهل موجوداً بنفس الاسم والمؤسسة فلن يتم إنشاء نسخة مكررة.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <h2 class="text-lg font-extrabold text-gray-950">بيانات الانتساب</h2>
                        </div>

                        <div class="grid gap-5 p-4 md:grid-cols-2">
                            <DateInputField
                                id="admission_date"
                                v-model="form.admission_date"
                                label="تاريخ الانتساب"
                                required
                                placeholder="اختر تاريخ الانتساب"
                                hint="يستخدم هذا التاريخ في توليد رقم القيد."
                                :error="form.errors.admission_date"
                            />

                            <div>
                                <label for="current_specialization_id" class="block text-sm font-bold text-gray-700">
                                    التخصص المنسب إليه <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="current_specialization_id"
                                    v-model="form.current_specialization_id"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="" disabled>اختر التخصص...</option>
                                    <option
                                        v-for="specialization in props.specializations"
                                        :key="specialization.id"
                                        :value="specialization.id"
                                    >
                                        {{ specialization.department.name }} - {{ specialization.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.current_specialization_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.current_specialization_id }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <LinkIcon class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">حساب الدخول للطالب</h2>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                اربط الطالب بمستخدم موجود أو أنشئ له حساب دخول جديد من نفس النموذج.
                            </p>
                        </div>

                        <div class="grid gap-5 p-4 md:grid-cols-2">
                            <div>
                                <label for="account_mode" class="block text-sm font-bold text-gray-700">طريقة الربط</label>
                                <select
                                    id="account_mode"
                                    v-model="form.account_mode"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="none">لا تنشئ حساب الآن</option>
                                    <option value="existing">ربط مستخدم موجود</option>
                                    <option value="create">إنشاء حساب جديد</option>
                                </select>
                                <p v-if="form.errors.account_mode" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.account_mode }}
                                </p>
                            </div>

                            <div v-if="form.account_mode === 'existing'">
                                <label for="user_id" class="block text-sm font-bold text-gray-700">المستخدم الموجود</label>
                                <select
                                    id="user_id"
                                    v-model="form.user_id"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="" disabled>اختر مستخدماً غير مرتبط بطالب...</option>
                                    <option v-for="user in props.availableUsers" :key="user.id" :value="String(user.id)">
                                        {{ user.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.user_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.user_id }}
                                </p>
                            </div>

                            <template v-if="form.account_mode === 'create'">
                                <div>
                                    <label for="user_email" class="block text-sm font-bold text-gray-700">البريد الإلكتروني</label>
                                    <div class="relative mt-2">
                                        <Mail class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-gray-400" />
                                        <input
                                            id="user_email"
                                            v-model="form.user_email"
                                            type="email"
                                            class="w-full rounded-md border border-gray-300 py-2.5 pr-9 pl-3 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                            placeholder="student@hist.edu.ly"
                                        />
                                    </div>
                                    <p v-if="form.errors.user_email" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.user_email }}
                                    </p>
                                </div>

                                <div>
                                    <label for="user_password" class="block text-sm font-bold text-gray-700">كلمة المرور</label>
                                    <div class="relative mt-2">
                                        <KeyRound class="pointer-events-none absolute right-3 top-3 h-4 w-4 text-gray-400" />
                                        <input
                                            id="user_password"
                                            v-model="form.user_password"
                                            type="password"
                                            class="w-full rounded-md border border-gray-300 py-2.5 pr-9 pl-3 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                            placeholder="8 أحرف على الأقل"
                                        />
                                    </div>
                                    <p v-if="form.errors.user_password" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.user_password }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <aside class="space-y-4">
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="font-extrabold text-blue-950">توليد رقم القيد</p>
                        <p class="mt-2 text-sm leading-7 text-blue-900">
                            عند الحفظ سيولد النظام رقم القيد من كود الإدارة، كود المعهد، سنة القبول، فصل القبول، وتسلسل الطالب.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-blue-800 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'جاري الحفظ...' : 'حفظ بيانات الطالب' }}
                        </button>

                        <Link
                            href="/students"
                            class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50"
                        >
                            إلغاء والعودة
                        </Link>
                    </div>
                </aside>
            </form>
        </div>
    </main>
</template>
