<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    ArrowRight,
    Building2,
    FileText,
    Hash,
    Library,
    Save,
    ShieldAlert,
    TimerReset,
} from 'lucide-vue-next'

interface Department {
    id: number
    name: string
}

interface Specialization {
    id: number
    department_id: number | string | null
    name: string | null
    code: string | null
    description: string | null
    semesters_count: number | string | null
}

const props = defineProps<{
    specialization: Specialization
    departments: Department[]
}>()

const form = useForm({
    department_id: String(props.specialization.department_id ?? ''),
    name: String(props.specialization.name ?? ''),
    code: String(props.specialization.code ?? ''),
    semesters_count: String(props.specialization.semesters_count ?? ''),
    description: String(props.specialization.description ?? ''),
})

const selectedDepartmentName = () => {
    return props.departments.find((department) => String(department.id) === form.department_id)?.name ?? 'غير محدد'
}

const submit = () => {
    form.patch(`/academic/specializations/${props.specialization.id}`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`تعديل تخصص - ${form.name || 'بدون اسم'}`" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-md bg-blue-50 p-3 text-blue-800">
                                <Library class="h-7 w-7" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-orange-600">الشؤون الأكاديمية</p>
                                <h1 class="mt-1 text-2xl font-extrabold text-blue-950">
                                    تعديل بيانات التخصص
                                </h1>
                                <p class="mt-2 max-w-3xl text-sm leading-7 text-gray-600">
                                    عدّل بيانات التخصص وربطه بالقسم العلمي وعدد الفصول المطلوبة للتخرج. تؤثر هذه البيانات في التسجيل، التقارير، وخطط الدراسة.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                href="/academic/specializations"
                                class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50"
                            >
                                <ArrowRight class="h-4 w-4" />
                                العودة للتخصصات
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

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-blue-50 p-2 text-blue-800">
                            <Library class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">اسم التخصص</p>
                            <p class="truncate text-lg font-extrabold text-gray-950">{{ form.name || 'غير محدد' }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-orange-50 p-2 text-orange-600">
                            <Hash class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">الرمز الأكاديمي</p>
                            <p class="truncate text-lg font-extrabold text-gray-950" dir="ltr">
                                {{ form.code || '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="rounded-md bg-green-50 p-2 text-green-700">
                            <TimerReset class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-500">الفصول المطلوبة</p>
                            <p class="truncate text-lg font-extrabold text-gray-950">
                                {{ form.semesters_count || 'غير محدد' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]" @submit.prevent="submit">
                <section class="rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-4">
                        <div class="flex items-center gap-2">
                            <FileText class="h-5 w-5 text-blue-800" />
                            <h2 class="text-lg font-extrabold text-gray-950">بيانات التخصص</h2>
                        </div>
                    </div>

                    <div class="grid gap-5 p-4 md:grid-cols-2">
                        <div>
                            <label for="department_id" class="block text-sm font-bold text-gray-700">
                                القسم العلمي <span class="text-red-600">*</span>
                            </label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                required
                                class="mt-2 w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                            >
                                <option value="" disabled>اختر القسم...</option>
                                <option
                                    v-for="department in departments"
                                    :key="department.id"
                                    :value="String(department.id)"
                                >
                                    {{ department.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.department_id" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.department_id }}
                            </p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700">
                                اسم التخصص / الشعبة <span class="text-red-600">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                placeholder="مثال: صيدلة"
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label for="code" class="block text-sm font-bold text-gray-700">الرمز الأكاديمي</label>
                            <input
                                id="code"
                                v-model="form.code"
                                type="text"
                                maxlength="20"
                                dir="ltr"
                                class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-left font-mono text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                placeholder="PHARM-01"
                            />
                            <p v-if="form.errors.code" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.code }}
                            </p>
                            <p class="mt-2 text-xs leading-5 text-gray-500">
                                يستخدم في الجداول والتقارير، ويجب ألا يتكرر مع تخصص آخر.
                            </p>
                        </div>

                        <div>
                            <label for="semesters_count" class="block text-sm font-bold text-gray-700">
                                عدد الفصول المطلوبة للتخرج <span class="text-red-600">*</span>
                            </label>
                            <input
                                id="semesters_count"
                                v-model="form.semesters_count"
                                type="number"
                                min="1"
                                max="12"
                                required
                                class="mt-2 w-full rounded-md border border-gray-300 px-3 py-2.5 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                            />
                            <p v-if="form.errors.semesters_count" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.semesters_count }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-bold text-gray-700">وصف التخصص</label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                maxlength="500"
                                class="mt-2 w-full resize-none rounded-md border border-gray-300 px-3 py-3 text-gray-950 shadow-sm focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                placeholder="نبذة مختصرة عن التخصص وأهدافه..."
                            />
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs text-gray-500">
                                <p v-if="form.errors.description" class="font-bold text-red-600">
                                    {{ form.errors.description }}
                                </p>
                                <p class="mr-auto">{{ form.description.length }} / 500</p>
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-2">
                            <Building2 class="h-5 w-5 text-blue-800" />
                            <h2 class="text-base font-extrabold text-gray-950">القسم المرتبط</h2>
                        </div>
                        <p class="mt-3 rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-sm font-extrabold text-blue-950">
                            {{ selectedDepartmentName() }}
                        </p>
                        <p class="mt-3 text-sm leading-7 text-gray-600">
                            تأكد من ربط التخصص بالقسم الصحيح حتى تظهر الشعب والمقررات في القوائم الأكاديمية المناسبة.
                        </p>
                    </div>

                    <div class="rounded-lg border border-orange-100 bg-orange-50 p-4">
                        <div class="flex items-center gap-2 text-orange-800">
                            <ShieldAlert class="h-5 w-5" />
                            <h2 class="text-base font-extrabold">تنبيه</h2>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-orange-900">
                            تعديل عدد الفصول قد يؤثر على أهلية التخرج وخطة انتقال الطلبة بين الفصول. راجع القرار الأكاديمي قبل الحفظ.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-blue-800 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'جاري الحفظ...' : 'حفظ بيانات التخصص' }}
                        </button>

                        <Link
                            href="/academic/specializations"
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
