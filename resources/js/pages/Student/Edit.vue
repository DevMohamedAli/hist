<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    ArrowRight,
    BadgeCheck,
    CalendarDays,
    GraduationCap,
    Hash,
    IdCard,
    Save,
    ShieldAlert,
    UserRound,
} from 'lucide-vue-next'
import DateInputField from '@/components/DateInputField.vue'
import { formatDisplayDate } from '@/lib/date'

interface Student {
    id: number
    full_name: string
    registration_number: string
    national_id: string
    gender: string
    nationality: string
    birth_date: string
    mobile: string | null
    admission_date: string
    qualification: string | null
    qualification_id: number | null
    current_specialization_id: number | null
    current_semester_level: number | null
    status: string
}

interface Specialization {
    id: number
    name: string
    department?: {
        name: string
    } | null
}

interface Qualification {
    id: number
    degree_name: string
    institution: string
    label?: string
}

const props = defineProps<{
    student: Student
    specializations: Specialization[]
    qualifications: Qualification[]
    calculatedSemesterLevel: number
}>()

const statusLabels: Record<string, string> = {
    Active: 'نشط',
    Suspended: 'موقوف',
    Transferred_Out: 'منقول خارجيا',
    Withdrawn: 'منسحب',
    Dismissed: 'مفصول',
    Graduated: 'متخرج',
}

const statusTone: Record<string, string> = {
    Active: 'border-green-200 bg-green-50 text-green-800',
    Suspended: 'border-amber-200 bg-amber-50 text-amber-800',
    Transferred_Out: 'border-sky-200 bg-sky-50 text-sky-800',
    Withdrawn: 'border-gray-200 bg-gray-50 text-gray-700',
    Dismissed: 'border-red-200 bg-red-50 text-red-800',
    Graduated: 'border-blue-200 bg-blue-50 text-blue-800',
}

const form = useForm({
    full_name: props.student.full_name ?? '',
    gender: props.student.gender ?? 'Male',
    nationality: props.student.nationality ?? 'ليبي',
    birth_date: props.student.birth_date ?? '',
    mobile: props.student.mobile ?? '',
    qualification: props.student.qualification ?? '',
    qualification_id: props.student.qualification_id ? String(props.student.qualification_id) : '',
    current_specialization_id: props.student.current_specialization_id ? String(props.student.current_specialization_id) : '',
    status: props.student.status ?? 'Active',
})

const submit = () => {
    form.patch(`/students/${props.student.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`تعديل بيانات ${student.full_name}`" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-md bg-blue-50 p-3 text-blue-800">
                                <UserRound class="h-7 w-7" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-orange-600">شؤون الطلبة</p>
                                <h1 class="mt-1 text-2xl font-extrabold text-blue-950">
                                    تعديل بيانات الطالب
                                </h1>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-gray-600">
                                    حدّث بيانات الطالب الأكاديمية والشخصية. رقم القيد والرقم الوطني وتاريخ الانتساب محفوظة كسجل تعريفي ولا تعدل من هذه الشاشة.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="`/students/${student.id}`"
                                class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
                            >
                                <ArrowRight class="h-4 w-4" />
                                رجوع إلى ملف الطالب
                            </Link>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-md bg-blue-800 px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing"
                                @click="submit"
                            >
                                <Save class="h-4 w-4" />
                                {{ form.processing ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-blue-50 p-2 text-blue-800">
                            <Hash class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">رقم القيد</p>
                            <p class="truncate text-lg font-extrabold text-gray-950">{{ student.registration_number }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-orange-50 p-2 text-orange-600">
                            <IdCard class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">الرقم الوطني</p>
                            <p class="truncate text-lg font-extrabold text-gray-950">{{ student.national_id }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-green-50 p-2 text-green-700">
                            <BadgeCheck class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">الحالة الحالية</p>
                            <span
                                class="mt-1 inline-flex rounded-full border px-3 py-1 text-sm font-extrabold"
                                :class="statusTone[form.status] ?? 'border-gray-200 bg-gray-50 text-gray-700'"
                            >
                                {{ statusLabels[form.status] ?? form.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-purple-50 p-2 text-purple-700">
                            <CalendarDays class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">تاريخ الانتساب</p>
                            <p class="truncate text-lg font-extrabold text-gray-950">{{ formatDisplayDate(student.admission_date) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" @submit.prevent="submit">
                <section class="space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <UserRound class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">البيانات الشخصية</h2>
                            </div>
                        </div>

                        <div class="grid gap-5 p-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="full_name" class="block text-sm font-bold text-gray-700">الاسم الرباعي</label>
                                <input
                                    id="full_name"
                                    v-model="form.full_name"
                                    type="text"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                />
                                <p v-if="form.errors.full_name" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.full_name }}
                                </p>
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-bold text-gray-700">الجنس</label>
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
                                <label for="nationality" class="block text-sm font-bold text-gray-700">الجنسية</label>
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
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-100 p-4">
                            <div class="flex items-center gap-2">
                                <GraduationCap class="h-5 w-5 text-blue-800" />
                                <h2 class="text-lg font-extrabold text-gray-950">البيانات الأكاديمية</h2>
                            </div>
                        </div>

                        <div class="grid gap-5 p-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label for="current_specialization_id" class="block text-sm font-bold text-gray-700">التخصص الحالي</label>
                                <select
                                    id="current_specialization_id"
                                    v-model="form.current_specialization_id"
                                    required
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="" disabled>اختر التخصص...</option>
                                    <option
                                        v-for="specialization in specializations"
                                        :key="specialization.id"
                                        :value="String(specialization.id)"
                                    >
                                        {{ specialization.department?.name ? `${specialization.department.name} - ` : '' }}{{ specialization.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.current_specialization_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.current_specialization_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">مستوى الفصل الدراسي</label>
                                <div class="mt-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-2.5 text-blue-950">
                                    <p class="text-lg font-extrabold">الفصل {{ calculatedSemesterLevel }}</p>
                                    <p class="mt-1 text-xs leading-5 text-blue-800">
                                        يتم احتساب المستوى تلقائياً من سجل المقررات الناجحة، ولا يتم تعديله يدوياً.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-bold text-gray-700">حالة الطالب</label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="Active">نشط</option>
                                    <option value="Suspended">موقوف</option>
                                    <option value="Transferred_Out">منقول خارجيا</option>
                                    <option value="Withdrawn">منسحب</option>
                                    <option value="Dismissed">مفصول</option>
                                    <option value="Graduated">متخرج</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label for="qualification_id" class="block text-sm font-bold text-gray-700">المؤهل السابق</label>
                                <select
                                    id="qualification_id"
                                    v-model="form.qualification_id"
                                    class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                >
                                    <option value="">بدون مؤهل محدد</option>
                                    <option
                                        v-for="qualification in qualifications"
                                        :key="qualification.id"
                                        :value="String(qualification.id)"
                                    >
                                        {{ qualification.label ?? `${qualification.degree_name} - ${qualification.institution}` }}
                                    </option>
                                </select>
                                <p v-if="form.errors.qualification_id" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.qualification_id }}
                                </p>
                                <p v-else class="mt-2 text-xs leading-5 text-gray-500">
                                    يتم تحميل هذه القائمة مباشرة من جدول المؤهلات.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2">
                            <ShieldAlert class="h-5 w-5 text-orange-600" />
                            <h2 class="text-base font-extrabold text-gray-950">تنبيه قبل الحفظ</h2>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-gray-600">
                            تغيير التخصص أو الحالة قد يؤثر على التسجيلات والتقارير الأكاديمية. استخدم النقل أو الإيقاف من صفحة ملف الطالب عندما تحتاج إلى إجراء رسمي موثق.
                        </p>
                    </div>

                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm leading-7 text-blue-950">
                        <p class="font-extrabold">حقول محفوظة</p>
                        <p class="mt-2">
                            رقم القيد، الرقم الوطني، وتاريخ الانتساب لا تتغير من هذه الشاشة حتى لا تتعارض مع سجل القبول.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-blue-800 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'جاري الحفظ...' : 'حفظ بيانات الطالب' }}
                        </button>

                        <Link
                            :href="`/students/${student.id}`"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50"
                        >
                            إلغاء والعودة
                        </Link>
                    </div>
                </aside>
            </form>
        </div>
    </main>
</template>
