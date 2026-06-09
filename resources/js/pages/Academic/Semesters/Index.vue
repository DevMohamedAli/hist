<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    CalendarCheck2,
    CalendarDays,
    PlusCircle,
    Info,
    Clock,
    BookOpen,
    Award,
    FileText,
} from 'lucide-vue-next';
import { watch, computed } from 'vue';
import { formatDisplayDate } from '@/lib/date';

interface Semester {
    id: number;
    code: string;
    season: 'Spring' | 'Fall' | 'Summer' | 'Winter';
    year: number;
    start_date?: string | null;
    end_date?: string | null;
    registration_start?: string | null;
    final_exams_start?: string | null;
}

interface Props {
    semesters: Semester[];
}

const props = defineProps<Props>();

const seasonLabels: Record<Semester['season'], string> = {
    Spring: 'الربيع (رسمي)',
    Fall: 'الخريف (رسمي)',
    Summer: 'الصيف (استثنائي)',
    Winter: 'الشتاء (استثنائي)',
};

// جلب السنة الحالية لتثبيتها كقيمة افتراضية ذكية
const currentYear = new Date().getFullYear();

const form = useForm({
    code: `Spring-${currentYear}`, // تعبئة القيمة الافتراضية فوراً لتجنب الـ null
    season: 'Spring' as Semester['season'],
    year: currentYear,
    start_date: '',
    end_date: '',
    registration_start: '',
    final_exams_start: '',
});

const today = new Date().toISOString().slice(0, 10);

const isActiveSemester = (semester: Semester) => {
    if (!semester.start_date || !semester.end_date) {
        return false;
    }

    return semester.start_date <= today && today <= semester.end_date;
};

// دالة مساعدة لإضافة أيام إلى تاريخ معين
const addDays = (dateStr: string, days: number): string => {
    if (!dateStr) {
        return '';
    }

    const date = new Date(dateStr);
    date.setDate(date.getDate() + days);

    return date.toISOString().slice(0, 10);
};

// المادة 18: الحساب التلقائي الذكي للتواريخ بناءً على تاريخ بداية الفصل
watch(
    () => form.start_date,
    (newStartDate) => {
        if (newStartDate) {
            // 1. التسجيل يبدأ مع بداية الفصل (الأسبوع الأول والثاني)
            form.registration_start = newStartDate;

            // 2. الامتحانات النهائية النظرية تبدأ في الأسبوع الـ 19 (أي بعد 18 أسبوعاً كاملة = 126 يوماً)
            form.final_exams_start = addDays(newStartDate, 126);

            // 3. نهاية الدراسة (نهاية الأسبوع الـ 20 = 140 يوماً كحد أقصى للائحة)
            form.end_date = addDays(newStartDate, 139);
        }
    },
);

// الحساب التلقائي لرمز الفصل بناءً على الموسم والسنة المختارين لتسهيل الإدخال
// تم إضافة { immediate: true } ليعمل الكود فور فتح الصفحة وتوليد الرمز الافتراضي تلقائياً
watch(
    [() => form.season, () => form.year],
    ([newSeason, newYear]) => {
        if (newSeason && newYear) {
            form.code = `${newSeason}-${newYear}`;
        }
    },
    { immediate: true },
); // هذه الإضافة هي التي تحل المشكلة تماماً وتمنع إرسال رمز فارغ
// حساب المخطط الزمني التحليلي لعرضه للمستخدم للمراجعة قبل الحفظ
const timelinePreview = computed(() => {
    if (!form.start_date) {
        return null;
    }

    return {
        registrationPeriod: {
            label: 'فترة القبول والتسجيل (الأسبوع 1 - 2)',
            start: form.registration_start,
            end: addDays(form.start_date, 13),
        },
        lecturesPeriod: {
            label: 'فترة المحاضرات والدراسة الفعالة (الأسبوع 3 - 15)',
            start: addDays(form.start_date, 14),
            end: addDays(form.start_date, 104),
        },
        practicalExamsPeriod: {
            label: 'الامتحانات النهائية العملية (الأسبوع 16)',
            start: addDays(form.start_date, 105),
            end: addDays(form.start_date, 111),
        },
        revisionPeriod: {
            label: 'فترة المراجعة والاستعداد (الأسبوع 17 - 18)',
            start: addDays(form.start_date, 112),
            end: addDays(form.start_date, 125),
        },
        theoreticalExamsPeriod: {
            label: 'الامتحانات النهائية النظرية (الأسبوع 19 - 20)',
            start: form.final_exams_start,
            end: form.end_date,
        },
    };
});

const submit = () => {
    form.post('/academic/semesters', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.year = new Date().getFullYear();
        },
    });
};
</script>

<template>
    <Head title="إدارة الفصول والتقويم الدراسي" />

    <main class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-6">
            <!-- ترويسة الصفحة والتعريف باللوائح -->
            <section
                class="rounded-lg border-t-4 border-blue-800 bg-white p-6 shadow-md"
            >
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <p class="text-sm font-semibold text-orange-500">
                            الوحدة الأكاديمية
                        </p>
                        <h1 class="mt-1 text-2xl font-bold text-blue-800">
                            إدارة الفصول والتقويم الدراسي
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            تخطيط الفصول الدراسية وتطبيق الضوابط الزمنية تماشياً
                            مع
                            <span class="font-semibold text-blue-800"
                                >المادة (18)</span
                            >
                            من اللائحة التنظيمية للمعهد.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-800"
                    >
                        <CalendarDays class="h-6 w-6" />
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                إجمالي الفصول
                            </p>
                            <p class="text-xl font-bold">
                                {{ props.semesters.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- صندوق معلومات توضيحي للمادة 18 -->
                <div
                    class="mt-6 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800"
                >
                    <Info class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                    <div class="text-xs leading-relaxed sm:text-sm">
                        <p class="mb-1 font-bold text-amber-900">
                            ملخص ضوابط التقويم الدراسي (المادة 18):
                        </p>
                        <ul class="list-inside list-disc space-y-1">
                            <li>
                                تنقسم السنة إلى فصلين رئيسيين:
                                <span class="font-semibold">الخريف</span> (يبدأ
                                سبتمبر) و<span class="font-semibold"
                                    >الربيع</span
                                >
                                (يبدأ فبراير).
                            </li>
                            <li>
                                الحد الأقصى لكل فصل دراسي هو
                                <span class="font-semibold">20 أسبوعاً</span>
                                تشمل فترات التسجيل والامتحانات.
                            </li>
                            <li>
                                الأسبوع الأول والثاني مخصصان للتسجيل، الأسبوع 16
                                للامتحانات العملية، والأسبوعين 19-20 للامتحانات
                                النظرية.
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- نموذج الإضافة الذكي -->
            <section class="rounded-lg bg-white p-6 shadow-md">
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        إعداد فصل دراسي وتقويم جديد
                    </h2>
                </div>

                <form
                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                    @submit.prevent="submit"
                >
                    <div>
                        <label
                            for="season"
                            class="block text-sm font-semibold text-gray-700"
                            >الموسم الدراسي</label
                        >
                        <select
                            id="season"
                            v-model="form.season"
                            class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                            required
                        >
                            <option value="Fall">الخريف (رسمي)</option>
                            <option value="Spring">الربيع (رسمي)</option>
                            <option value="Summer">الصيف (استثنائي)</option>
                            <option value="Winter">الشتاء (استثنائي)</option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="year"
                            class="block text-sm font-semibold text-gray-700"
                            >السنة الأكاديمية</label
                        >
                        <input
                            id="year"
                            v-model.number="form.year"
                            type="number"
                            min="2020"
                            max="2100"
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label
                            for="start_date"
                            class="block text-sm font-semibold text-blue-800"
                        >
                            تاريخ بداية الفصل الدراسي (الفعلي) *
                        </label>
                        <input
                            id="start_date"
                            v-model="form.start_date"
                            type="date"
                            class="mt-2 block w-full rounded-lg border-2 border-blue-300 bg-blue-50/30 px-3 py-2 text-gray-900 shadow-sm focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label
                            for="code"
                            class="block text-sm font-semibold text-gray-700"
                            >رمز الفصل (توليد تلقائي)</label
                        >
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            class="mt-2 block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-gray-500 shadow-sm focus:outline-none"
                            readonly
                        />
                    </div>

                    <!-- حقول مخفية يتم تعبئتها آلياً لمنع الخطأ البشري ولتلبية قيود قاعدة البيانات والمادة 18 -->
                    <input type="hidden" v-model="form.registration_start" />
                    <input type="hidden" v-model="form.end_date" />
                    <input type="hidden" v-model="form.final_exams_start" />

                    <!-- المعاينة الفورية والتحليل الزمني التلقائي للمادة 18 -->
                    <div
                        v-if="timelinePreview"
                        class="space-y-4 rounded-lg border border-blue-100 bg-blue-50/20 p-5 md:col-span-2"
                    >
                        <h4
                            class="flex items-center gap-2 text-sm font-bold text-blue-900"
                        >
                            <Clock class="h-5 w-5 text-blue-700" />
                            مخطط توزيع الأسابيع للفصل الدراسي الجديد (تطبيقاً
                            للمادة 18):
                        </h4>

                        <div
                            class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 lg:grid-cols-5"
                        >
                            <div
                                class="rounded border border-blue-50 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="flex items-center gap-1 font-semibold text-gray-500"
                                >
                                    <BookOpen
                                        class="h-3.5 w-3.5 text-blue-500"
                                    />
                                    تسجيل وقبول
                                </p>
                                <p class="mt-1.5 font-bold text-gray-800">
                                    من:
                                    {{
                                        timelinePreview.registrationPeriod.start
                                    }}
                                </p>
                                <p class="font-bold text-gray-800">
                                    إلى:
                                    {{ timelinePreview.registrationPeriod.end }}
                                </p>
                            </div>

                            <div
                                class="rounded border border-blue-50 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="flex items-center gap-1 font-semibold text-gray-500"
                                >
                                    <FileText
                                        class="h-3.5 w-3.5 text-green-500"
                                    />
                                    دراسة ومحاضرات
                                </p>
                                <p class="mt-1.5 font-bold text-gray-800">
                                    من:
                                    {{ timelinePreview.lecturesPeriod.start }}
                                </p>
                                <p class="font-bold text-gray-800">
                                    إلى:
                                    {{ timelinePreview.lecturesPeriod.end }}
                                </p>
                            </div>

                            <div
                                class="rounded border border-blue-50 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="flex items-center gap-1 font-semibold text-gray-500"
                                >
                                    <Award
                                        class="h-3.5 w-3.5 text-orange-500"
                                    />
                                    امتحانات عملية
                                </p>
                                <p class="mt-1.5 font-bold text-gray-800">
                                    من:
                                    {{
                                        timelinePreview.practicalExamsPeriod
                                            .start
                                    }}
                                </p>
                                <p class="font-bold text-gray-800">
                                    إلى:
                                    {{
                                        timelinePreview.practicalExamsPeriod.end
                                    }}
                                </p>
                            </div>

                            <div
                                class="rounded border border-blue-50 bg-white p-3 shadow-sm"
                            >
                                <p
                                    class="flex items-center gap-1 font-semibold text-gray-500"
                                >
                                    <Clock
                                        class="h-3.5 w-3.5 text-indigo-500"
                                    />
                                    مراجعة واستعداد
                                </p>
                                <p class="mt-1.5 font-bold text-gray-800">
                                    من:
                                    {{ timelinePreview.revisionPeriod.start }}
                                </p>
                                <p class="font-bold text-gray-800">
                                    إلى:
                                    {{ timelinePreview.revisionPeriod.end }}
                                </p>
                            </div>

                            <div
                                class="rounded border border-orange-100 bg-orange-50 p-3 shadow-sm"
                            >
                                <p
                                    class="flex items-center gap-1 font-semibold text-orange-700"
                                >
                                    <Award
                                        class="h-3.5 w-3.5 text-orange-600"
                                    />
                                    امتحانات نظرية
                                </p>
                                <p class="mt-1.5 font-bold text-orange-800">
                                    من:
                                    {{
                                        timelinePreview.theoreticalExamsPeriod
                                            .start
                                    }}
                                </p>
                                <p class="font-bold text-orange-800">
                                    إلى:
                                    {{
                                        timelinePreview.theoreticalExamsPeriod
                                            .end
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end justify-end md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-orange-600 focus:ring-2 focus:ring-orange-500/30 focus:outline-none disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <PlusCircle class="h-5 w-5" />
                            <span>{{
                                form.processing
                                    ? 'جاري الحفظ والجدولة...'
                                    : 'حفظ وجدولة التقويم الدراسي'
                            }}</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- عرض الفصول المقيدة حالياً -->
            <section>
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        الفصول الدراسية الحالية المقيدة بالمنظومة
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        عرض التقاويم والخطط الأكاديمية المعتمدة.
                    </p>
                </div>

                <div
                    v-if="props.semesters.length > 0"
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="semester in props.semesters"
                        :key="semester.id"
                        class="flex flex-col justify-between rounded-lg border-r-4 bg-white p-5 shadow-md"
                        :class="
                            isActiveSemester(semester)
                                ? 'border-r-green-500'
                                : 'border-r-gray-300'
                        "
                    >
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p
                                        class="text-sm font-semibold text-orange-500"
                                    >
                                        {{ seasonLabels[semester.season] }}
                                    </p>
                                    <h3
                                        class="mt-1 text-xl font-bold text-blue-800"
                                    >
                                        {{ semester.code }}
                                    </h3>
                                </div>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold"
                                    :class="
                                        isActiveSemester(semester)
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-600'
                                    "
                                >
                                    {{
                                        isActiveSemester(semester)
                                            ? 'الفصل الحالي'
                                            : 'غير نشط'
                                    }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-gray-500">السنة</p>
                                    <p class="mt-1 font-bold text-gray-900">
                                        {{ semester.year }}
                                    </p>
                                </div>

                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-gray-500">بداية الدراسة</p>
                                    <p class="mt-1 font-bold text-gray-900">
                                        {{ formatDisplayDate(semester.start_date, 'غير محدد') }}
                                    </p>
                                </div>

                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-gray-500">بداية التسجيل</p>
                                    <p class="mt-1 font-bold text-gray-900">
                                        {{
                                            formatDisplayDate(semester.registration_start, 'غير محدد')
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-lg border border-orange-100 bg-orange-50 p-3"
                                >
                                    <p class="text-orange-700">
                                        الامتحانات النظرية
                                    </p>
                                    <p class="mt-1 font-bold text-orange-800">
                                        {{
                                            formatDisplayDate(semester.final_exams_start, 'غير محدد')
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="isActiveSemester(semester)"
                            class="mt-5 flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm font-semibold text-green-700"
                        >
                            <CalendarCheck2 class="h-5 w-5 shrink-0" />
                            <span
                                >تاريخ اليوم يقع ضمن نطاق الـ 20 أسبوعاً المخصصة
                                لهذا الفصل.</span
                            >
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-lg bg-white p-10 text-center text-gray-500 shadow-md"
                >
                    لا توجد فصول دراسية مسجلة حتى الآن بالمنظومة.
                </div>
            </section>
        </div>
    </main>
</template>
