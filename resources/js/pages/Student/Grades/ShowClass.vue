<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import {
    AlertCircle,
    ArrowRight,
    BookOpen,
    Calendar,
    CheckCircle2,
    Loader2,
    Save,
    User,
    Users,
    X,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

interface Enrollment {
    id: number
    registration_number: string
    student_name: string
    semester_work_mark: number | null | string
    final_exam_mark: number | null | string
    total_mark: number | null
    grade_evaluation: string | null
    status?: 'Pending' | 'Passed' | 'Failed' | string | null
}

interface CourseClass {
    id: number
    group_name?: string | null
    course?: { id: number; name: string; code?: string | null; units?: number | null } | null
    semester?: { id: number; code?: string | null; name?: string | null } | null
    instructor?: { id: number; name?: string | null; employee_id?: string | null } | null
    study_group?: {
        id: number
        group_name?: string | null
        specialization?: { name?: string | null } | null
    } | null
}

interface PageProps {
    flash?: {
        success?: string
        message?: string
        enrollment_id?: number
        total_mark?: number
        grade_evaluation?: string
        status?: string
    }
    [key: string]: unknown
}

const props = defineProps<{
    courseClass: CourseClass
    enrollments: Enrollment[]
}>()

const page = usePage<PageProps>()

const localEnrollments = ref(
    props.enrollments.map((enrollment) => ({
        ...enrollment,
        semester_work_mark: enrollment.semester_work_mark ?? '',
        final_exam_mark: enrollment.final_exam_mark ?? '',
    })),
)

const savingStates = ref<Record<number, boolean>>({})
const toast = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error',
})

let toastTimeoutId: ReturnType<typeof setTimeout> | null = null

const courseName = computed(() => props.courseClass.course?.name ?? 'مقرر غير محدد')
const courseCode = computed(() => props.courseClass.course?.code ?? '-')
const courseUnits = computed(() => props.courseClass.course?.units ?? 0)
const groupName = computed(() => props.courseClass.study_group?.group_name ?? props.courseClass.group_name ?? '-')
const specializationName = computed(() => props.courseClass.study_group?.specialization?.name ?? '-')
const semesterCode = computed(() => props.courseClass.semester?.code ?? '-')
const instructorName = computed(() => props.courseClass.instructor?.name ?? 'غير محدد')

const gradedCount = computed(() =>
    localEnrollments.value.filter((enrollment) => enrollment.status === 'Passed' || enrollment.status === 'Failed').length,
)
const passedCount = computed(() =>
    localEnrollments.value.filter((enrollment) => computedStatus(enrollment) === 'Passed').length,
)
const failedCount = computed(() =>
    localEnrollments.value.filter((enrollment) => computedStatus(enrollment) === 'Failed').length,
)

const numberValue = (value: number | string | null) => {
    const parsed = Number.parseFloat(String(value || 0))

    return Number.isFinite(parsed) ? parsed : 0
}

const totalFor = (enrollment: Enrollment) => {
    return Math.min(100, numberValue(enrollment.semester_work_mark) + numberValue(enrollment.final_exam_mark))
}

const evaluationFor = (total: number) => {
    if (total >= 85) return 'ممتاز'
    if (total >= 75) return 'جيد جداً'
    if (total >= 65) return 'جيد'
    if (total >= 50) return 'مقبول'
    if (total >= 35) return 'ضعيف'

    return 'ضعيف جداً'
}

const computedStatus = (enrollment: Enrollment) => {
    const total = totalFor(enrollment)
    const finalExam = numberValue(enrollment.final_exam_mark)

    if (total === 0 && finalExam === 0 && numberValue(enrollment.semester_work_mark) === 0 && enrollment.status === 'Pending') {
        return 'Pending'
    }

    return total >= 50 && finalExam >= 30 ? 'Passed' : 'Failed'
}

const rowError = (enrollment: Enrollment) => {
    const work = numberValue(enrollment.semester_work_mark)
    const finalExam = numberValue(enrollment.final_exam_mark)

    if (work < 0 || work > 40) {
        return 'أعمال الفصل يجب أن تكون بين 0 و 40.'
    }

    if (finalExam < 0 || finalExam > 60) {
        return 'الامتحان النهائي يجب أن يكون بين 0 و 60.'
    }

    return ''
}

const gradeClass = (evaluation: string | null) => {
    const map: Record<string, string> = {
        ممتاز: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'جيد جداً': 'bg-blue-50 text-blue-700 ring-blue-200',
        جيد: 'bg-sky-50 text-sky-700 ring-sky-200',
        مقبول: 'bg-amber-50 text-amber-700 ring-amber-200',
        ضعيف: 'bg-red-50 text-red-700 ring-red-200',
        'ضعيف جداً': 'bg-red-50 text-red-800 ring-red-200',
    }

    return map[evaluation || ''] ?? 'bg-gray-100 text-gray-600 ring-gray-200'
}

const statusLabel = (status: string | null | undefined) => {
    if (status === 'Passed') return 'ناجح'
    if (status === 'Failed') return 'راسب'

    return 'قيد الرصد'
}

const statusClass = (status: string | null | undefined) => {
    if (status === 'Passed') return 'bg-green-50 text-green-700 ring-green-200'
    if (status === 'Failed') return 'bg-red-50 text-red-700 ring-red-200'

    return 'bg-gray-100 text-gray-600 ring-gray-200'
}

const triggerToast = (message: string, type: 'success' | 'error' = 'success') => {
    toast.value = { show: true, message, type }

    if (toastTimeoutId) {
        clearTimeout(toastTimeoutId)
    }

    toastTimeoutId = setTimeout(() => {
        toast.value.show = false
    }, 3500)
}

const saveGrade = (enrollment: (typeof localEnrollments.value)[0]) => {
    const error = rowError(enrollment)

    if (error) {
        triggerToast(error, 'error')
        return
    }

    savingStates.value[enrollment.id] = true

    router.post(
        '/grades/record',
        {
            enrollment_id: enrollment.id,
            semester_work: numberValue(enrollment.semester_work_mark),
            final_exam: numberValue(enrollment.final_exam_mark),
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                savingStates.value[enrollment.id] = false
                const flash = page.props.flash

                enrollment.total_mark = Number(flash?.total_mark ?? totalFor(enrollment))
                enrollment.grade_evaluation = flash?.grade_evaluation ?? evaluationFor(enrollment.total_mark)
                enrollment.status = flash?.status ?? computedStatus(enrollment)
                triggerToast(flash?.message ?? 'تم حفظ الدرجة بنجاح.')
            },
            onError: () => {
                savingStates.value[enrollment.id] = false
                triggerToast('تعذر حفظ الدرجة. راجع القيم المدخلة.', 'error')
            },
            onFinish: () => {
                savingStates.value[enrollment.id] = false
            },
        },
    )
}
</script>

<template>
    <Head :title="`Ø±ØµØ¯ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª | ${courseName}`" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="rounded-lg border border-blue-100 bg-white shadow-sm">
                <div class="border-t-4 border-blue-800 p-5 sm:p-6">
                    <Link
                        href="/grades"
                        class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-blue-800 hover:text-orange-600"
                    >
                        <ArrowRight class="h-4 w-4" />
                        Ø§Ù„Ø¹ÙˆØ¯Ø© Ø¥Ù„Ù‰ Ø§Ù„Ø´Ø¹Ø¨
                    </Link>

                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-orange-600">ÙƒØ´Ù Ø±ØµØ¯ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª</p>
                            <h1 class="mt-1 text-2xl font-extrabold text-blue-950 sm:text-3xl">
                                {{ courseName }}
                            </h1>
                            <p class="mt-2 text-sm text-gray-600">
                                Ø£Ø¹Ù…Ø§Ù„ Ø§Ù„ÙØµÙ„ Ù…Ù† 40ØŒ Ø§Ù„Ø§Ù…ØªØ­Ø§Ù† Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ Ù…Ù† 60ØŒ ÙˆÙ„Ø§ ÙŠØ¹Ø¯ Ø§Ù„Ø·Ø§Ù„Ø¨ Ù†Ø§Ø¬Ø­Ø§ Ø¥Ù„Ø§ Ø¥Ø°Ø§ Ø­ØµÙ„ Ø¹Ù„Ù‰ 30 ÙØ£ÙƒØ«Ø± ÙÙŠ Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ.
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3">
                                <p class="text-xs font-bold text-blue-700">Ø§Ù„Ø·Ù„Ø§Ø¨</p>
                                <p class="mt-1 text-xl font-extrabold text-blue-950">{{ localEnrollments.length }}</p>
                            </div>
                            <div class="rounded-md border border-green-100 bg-green-50 px-4 py-3">
                                <p class="text-xs font-bold text-green-700">Ù†Ø§Ø¬Ø­</p>
                                <p class="mt-1 text-xl font-extrabold text-green-950">{{ passedCount }}</p>
                            </div>
                            <div class="rounded-md border border-red-100 bg-red-50 px-4 py-3">
                                <p class="text-xs font-bold text-red-700">Ø±Ø§Ø³Ø¨</p>
                                <p class="mt-1 text-xl font-extrabold text-red-950">{{ failedCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-5">
                        <div class="rounded-md bg-slate-50 px-3 py-2">
                            <span class="block text-xs font-bold text-gray-500">Ø±Ù…Ø² Ø§Ù„Ù…Ù‚Ø±Ø±</span>
                            <strong class="text-blue-900">{{ courseCode }}</strong>
                        </div>
                        <div class="rounded-md bg-slate-50 px-3 py-2">
                            <span class="block text-xs font-bold text-gray-500">Ø§Ù„Ø´Ø¹Ø¨Ø©</span>
                            <strong class="text-blue-900">{{ groupName }}</strong>
                        </div>
                        <div class="rounded-md bg-slate-50 px-3 py-2">
                            <span class="block text-xs font-bold text-gray-500">Ø§Ù„ØªØ®ØµØµ</span>
                            <strong class="text-blue-900">{{ specializationName }}</strong>
                        </div>
                        <div class="rounded-md bg-slate-50 px-3 py-2">
                            <span class="block text-xs font-bold text-gray-500">Ø§Ù„ÙØµÙ„</span>
                            <strong class="text-blue-900">{{ semesterCode }}</strong>
                        </div>
                        <div class="rounded-md bg-slate-50 px-3 py-2">
                            <span class="block text-xs font-bold text-gray-500">Ø§Ù„ÙˆØ­Ø¯Ø§Øª</span>
                            <strong class="text-blue-900">{{ courseUnits }}</strong>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-gray-600">
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-blue-800">
                            <BookOpen class="h-3.5 w-3.5" />
                            {{ courseName }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-3 py-1 text-orange-700">
                            <Calendar class="h-3.5 w-3.5" />
                            {{ semesterCode }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                            <User class="h-3.5 w-3.5" />
                            {{ instructorName }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-gray-700">
                            <Users class="h-3.5 w-3.5" />
                            Ù…Ø±ØµÙˆØ¯ {{ gradedCount }} Ù…Ù† {{ localEnrollments.length }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-[980px] w-full text-right text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-4 font-bold">Ø±Ù‚Ù… Ø§Ù„Ù‚ÙŠØ¯</th>
                                <th class="px-4 py-4 font-bold">Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨</th>
                                <th class="px-4 py-4 text-center font-bold">Ø£Ø¹Ù…Ø§Ù„ Ø§Ù„ÙØµÙ„ / 40</th>
                                <th class="px-4 py-4 text-center font-bold">Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ / 60</th>
                                <th class="px-4 py-4 text-center font-bold">Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹</th>
                                <th class="px-4 py-4 text-center font-bold">Ø§Ù„ØªÙ‚Ø¯ÙŠØ±</th>
                                <th class="px-4 py-4 text-center font-bold">Ø§Ù„Ø­Ø§Ù„Ø©</th>
                                <th class="px-4 py-4 text-center font-bold">Ø§Ù„Ø¥Ø¬Ø±Ø§Ø¡</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="enrollment in localEnrollments"
                                :key="enrollment.id"
                                class="hover:bg-orange-50/40"
                            >
                                <td class="px-4 py-4 font-mono font-bold text-blue-800">
                                    {{ enrollment.registration_number }}
                                </td>
                                <td class="px-4 py-4 font-bold text-gray-900">
                                    {{ enrollment.student_name }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input
                                        v-model="enrollment.semester_work_mark"
                                        type="number"
                                        min="0"
                                        max="40"
                                        step="0.5"
                                        class="w-24 rounded-md border border-gray-300 px-3 py-2 text-center font-bold focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                                    />
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input
                                        v-model="enrollment.final_exam_mark"
                                        type="number"
                                        min="0"
                                        max="60"
                                        step="0.5"
                                        class="w-24 rounded-md border border-gray-300 px-3 py-2 text-center font-bold focus:border-orange-500 focus:ring-2 focus:ring-orange-500/15 focus:outline-none"
                                    />
                                    <p
                                        v-if="numberValue(enrollment.final_exam_mark) > 0 && numberValue(enrollment.final_exam_mark) < 30"
                                        class="mt-1 text-xs font-bold text-red-600"
                                    >
                                        Ø£Ù‚Ù„ Ù…Ù† 50% Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ
                                    </p>
                                </td>
                                <td class="px-4 py-4 text-center text-lg font-extrabold text-blue-900">
                                    {{ totalFor(enrollment) }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1"
                                        :class="gradeClass(evaluationFor(totalFor(enrollment)))"
                                    >
                                        {{ evaluationFor(totalFor(enrollment)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1"
                                        :class="statusClass(computedStatus(enrollment))"
                                    >
                                        {{ statusLabel(computedStatus(enrollment)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex min-w-24 items-center justify-center gap-2 rounded-md bg-blue-800 px-4 py-2 text-xs font-bold text-white hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="!!savingStates[enrollment.id] || !!rowError(enrollment)"
                                        @click="saveGrade(enrollment)"
                                    >
                                        <Loader2 v-if="savingStates[enrollment.id]" class="h-4 w-4 animate-spin" />
                                        <Save v-else class="h-4 w-4" />
                                        {{ savingStates[enrollment.id] ? 'Ø¬Ø§Ø±ÙŠ...' : 'Ø­ÙØ¸' }}
                                    </button>
                                    <p v-if="rowError(enrollment)" class="mt-1 text-xs font-bold text-red-600">
                                        {{ rowError(enrollment) }}
                                    </p>
                                </td>
                            </tr>

                            <tr v-if="localEnrollments.length === 0">
                                <td colspan="8" class="px-4 py-16 text-center text-gray-500">
                                    <AlertCircle class="mx-auto mb-2 h-10 w-10 text-gray-300" />
                                    Ù„Ø§ ÙŠÙˆØ¬Ø¯ Ø·Ù„Ø§Ø¨ Ù…Ø³Ø¬Ù„ÙˆÙ† ÙÙŠ Ù‡Ø°Ù‡ Ø§Ù„Ø´Ø¹Ø¨Ø© Ø­Ø§Ù„ÙŠØ§.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="fixed bottom-6 left-6 z-50 w-full max-w-sm pointer-events-none">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-3 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="toast.show"
                    class="pointer-events-auto rounded-lg border bg-white p-4 shadow-xl"
                    :class="toast.type === 'success' ? 'border-green-200' : 'border-red-200'"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircle2 v-if="toast.type === 'success'" class="h-5 w-5 shrink-0 text-green-600" />
                        <AlertCircle v-else class="h-5 w-5 shrink-0 text-red-600" />
                        <p class="flex-1 text-sm font-bold text-gray-900">{{ toast.message }}</p>
                        <button type="button" class="text-gray-400 hover:text-gray-600" @click="toast.show = false">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </main>
</template>

