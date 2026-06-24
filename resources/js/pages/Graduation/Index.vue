<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Eye,
    FileText,
    GraduationCap,
    Printer,
    Search,
    ShieldCheck,
    SlidersHorizontal,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Department {
    id: number;
    name: string;
}

interface Specialization {
    id: number;
    name: string;
    department_id?: number;
    department?: Department | null;
}

interface StudentRow {
    id: number;
    registration_number: string;
    full_name: string;
    status: string;
    current_semester_level: number;
    current_specialization?: Specialization | null;
    eligibility: {
        eligible: boolean;
        cgpa: number;
        total_units: number;
        required_count: number;
        passed_count: number;
        missing_count: number;
        reasons: string[];
    };
    graduation_record?: {
        id: number;
        certificate_number: string;
        graduation_date: string;
    } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    total: number;
    from: number | null;
    to: number | null;
    current_page?: number;
    last_page?: number;
}

interface Counts {
    approved: number;
    eligible: number;
    blocked: number;
    total: number;
}

const props = defineProps<{
    students: Paginated<StudentRow>;
    counts: Counts;
    filters: {
        search?: string | null;
        status?: string | null;
        department_id?: number | string | null;
        specialization_id?: number | string | null;
    };
    departments: Department[];
    specializations: Specialization[];
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const departmentId = ref(
    props.filters.department_id ? String(props.filters.department_id) : '',
);
const specializationId = ref(
    props.filters.specialization_id
        ? String(props.filters.specialization_id)
        : '',
);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
let skipNextAutoApply = false;
const counts = computed(() => props.counts);

const statusTabs = [
    { value: '', label: 'الكل' },
    { value: 'eligible', label: 'مؤهل' },
    { value: 'blocked', label: 'غير مكتمل' },
    { value: 'approved', label: 'معتمد' },
];

const filteredSpecializations = computed(() => {
    if (!departmentId.value) {
        return props.specializations;
    }

    return props.specializations.filter(
        (item) => String(item.department_id) === departmentId.value,
    );
});

const activeFilters = computed(() => {
    const items: { key: string; label: string; clear: () => void }[] = [];
    const department = props.departments.find(
        (item) => String(item.id) === departmentId.value,
    );
    const specialization = props.specializations.find(
        (item) => String(item.id) === specializationId.value,
    );

    if (search.value.trim()) {
        items.push({
            key: 'search',
            label: `بحث: ${search.value}`,
            clear: () => {
                search.value = '';
                applyFiltersImmediately();
            },
        });
    }

    if (status.value) {
        items.push({
            key: 'status',
            label: `الحالة: ${statusTabs.find((tab) => tab.value === status.value)?.label}`,
            clear: () => {
                status.value = '';
                applyFiltersImmediately();
            },
        });
    }

    if (department) {
        items.push({
            key: 'department',
            label: `القسم: ${department.name}`,
            clear: () => {
                departmentId.value = '';
                applyFiltersImmediately();
            },
        });
    }

    if (specialization) {
        items.push({
            key: 'specialization',
            label: `التخصص: ${specialization.name}`,
            clear: () => {
                specializationId.value = '';
                applyFiltersImmediately();
            },
        });
    }

    return items;
});

const applyFilters = (delay = 250) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    if (delay <= 0) {
        router.get(
            '/graduations',
            {
                search: search.value || undefined,
                status: status.value || undefined,
                department_id: departmentId.value || undefined,
                specialization_id: specializationId.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );

        return;
    }

    searchTimeout = setTimeout(() => {
        router.get(
            '/graduations',
            {
                search: search.value || undefined,
                status: status.value || undefined,
                department_id: departmentId.value || undefined,
                specialization_id: specializationId.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, delay);
};

const applyFiltersImmediately = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
    }

    skipNextAutoApply = true;
    applyFilters(0);
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    departmentId.value = '';
    specializationId.value = '';
    applyFiltersImmediately();
};

const setStatus = (value: string) => {
    status.value = value;
    applyFiltersImmediately();
};

const progress = (student: StudentRow) => {
    if (student.eligibility.required_count === 0) {
        return 0;
    }

    return Math.round(
        (student.eligibility.passed_count /
            student.eligibility.required_count) *
            100,
    );
};

const statusBadge = (student: StudentRow) => {
    if (student.graduation_record) {
        return {
            label: 'معتمد',
            classes: 'bg-blue-50 text-blue-700',
            icon: FileText,
        };
    }

    if (student.eligibility.eligible) {
        return {
            label: 'مؤهل',
            classes: 'bg-emerald-50 text-emerald-700',
            icon: CheckCircle2,
        };
    }

    return {
        label: 'غير مكتمل',
        classes: 'bg-amber-50 text-amber-700',
        icon: XCircle,
    };
};

watch(departmentId, () => {
    if (
        specializationId.value &&
        !filteredSpecializations.value.some(
            (item) => String(item.id) === specializationId.value,
        )
    ) {
        specializationId.value = '';
    }
});

watch([search, status, departmentId, specializationId], () => {
    if (skipNextAutoApply) {
        skipNextAutoApply = false;

        return;
    }

    applyFilters();
});
</script>

<template>
    <Head title="إدارة الخريجين" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-7xl space-y-5">
            <section
                class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p class="text-sm font-bold text-orange-600">
                        شؤون الطلاب | التخرج
                    </p>
                    <h1
                        class="mt-1 flex items-center gap-2 text-2xl font-black text-blue-950"
                    >
                        <GraduationCap class="h-7 w-7 text-blue-800" />
                        إدارة الخريجين
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                        مراجعة أهلية الطلاب، اعتماد التخرج، وطباعة الشهادة
                        والتقرير التفصيلي من شاشة واحدة.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div
                        class="rounded-md border border-emerald-100 bg-white px-4 py-3 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500">مؤهل</p>
                        <p class="text-xl font-black text-emerald-700">
                            {{ counts.eligible }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-amber-100 bg-white px-4 py-3 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500">
                            غير مكتمل
                        </p>
                        <p class="text-xl font-black text-amber-700">
                            {{ counts.blocked }}
                        </p>
                    </div>
                    <div
                        class="rounded-md border border-blue-100 bg-white px-4 py-3 shadow-sm"
                    >
                        <p class="text-xs font-bold text-slate-500">معتمد</p>
                        <p class="text-xl font-black text-blue-700">
                            {{ counts.approved }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                class="rounded-md border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="mb-4 flex flex-wrap gap-2">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.value"
                        type="button"
                        class="rounded-md px-4 py-2 text-sm font-bold transition"
                        :class="
                            status === tab.value
                                ? 'bg-blue-900 text-white'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        "
                        @click="setStatus(tab.value)"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold text-slate-600"
                            >بحث بالاسم أو رقم القيد</label
                        >
                        <div class="relative mt-1">
                            <Search
                                class="absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="search"
                                type="text"
                                class="w-full rounded-md border border-slate-300 py-2 pr-9 pl-3 focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20"
                                placeholder="اسم الطالب أو رقم القيد"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600"
                            >القسم</label
                        >
                        <select
                            v-model="departmentId"
                            class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="">كل الأقسام</option>
                            <option
                                v-for="department in departments"
                                :key="department.id"
                                :value="String(department.id)"
                            >
                                {{ department.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-600"
                            >التخصص</label
                        >
                        <select
                            v-model="specializationId"
                            class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="">كل التخصصات</option>
                            <option
                                v-for="specialization in filteredSpecializations"
                                :key="specialization.id"
                                :value="String(specialization.id)"
                            >
                                {{ specialization.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div
                    class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3"
                >
                    <button
                        v-for="filter in activeFilters"
                        :key="filter.key"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800 hover:bg-blue-100"
                        @click="filter.clear"
                    >
                        {{ filter.label }}
                        <X class="h-3 w-3" />
                    </button>
                    <button
                        v-if="activeFilters.length"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                        @click="resetFilters"
                    >
                        <SlidersHorizontal class="h-3 w-3" />
                        مسح الكل
                    </button>
                </div>
            </section>

            <section
                class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] text-sm">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <th class="px-4 py-3 text-start">الطالب</th>
                                <th class="px-4 py-3 text-start">التخصص</th>
                                <th class="px-4 py-3 text-center">المعدل</th>
                                <th class="px-4 py-3 text-center">
                                    إنجاز الخطة
                                </th>
                                <th class="px-4 py-3 text-center">الحالة</th>
                                <th class="px-4 py-3 text-end">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="student in students.data"
                                :key="student.id"
                                class="hover:bg-orange-50/40"
                            >
                                <td class="px-4 py-3">
                                    <p class="font-black text-slate-950">
                                        {{ student.full_name }}
                                    </p>
                                    <p class="font-mono text-xs text-slate-500">
                                        {{ student.registration_number }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-800">
                                        {{
                                            student.current_specialization
                                                ?.name ?? '-'
                                        }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{
                                            student.current_specialization
                                                ?.department?.name ?? '-'
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-black text-blue-900"
                                        >{{
                                            student.eligibility.cgpa.toFixed(2)
                                        }}%</span
                                    >
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ student.eligibility.total_units }}
                                        وحدة
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="mx-auto w-44">
                                        <div
                                            class="flex justify-between text-xs font-bold text-slate-600"
                                        >
                                            <span
                                                >{{
                                                    student.eligibility
                                                        .passed_count
                                                }}
                                                /
                                                {{
                                                    student.eligibility
                                                        .required_count
                                                }}</span
                                            >
                                            <span
                                                >{{ progress(student) }}%</span
                                            >
                                        </div>
                                        <div
                                            class="mt-1 h-2 rounded-full bg-slate-100"
                                        >
                                            <div
                                                class="h-2 rounded-full bg-emerald-500"
                                                :style="{
                                                    width: `${progress(student)}%`,
                                                }"
                                            />
                                        </div>
                                        <p
                                            v-if="
                                                student.eligibility
                                                    .missing_count > 0
                                            "
                                            class="mt-1 text-xs font-bold text-amber-700"
                                        >
                                            متبقي
                                            {{
                                                student.eligibility
                                                    .missing_count
                                            }}
                                            مقرر
                                        </p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-black"
                                        :class="statusBadge(student).classes"
                                    >
                                        <component
                                            :is="statusBadge(student).icon"
                                            class="h-3.5 w-3.5"
                                        />
                                        {{ statusBadge(student).label }}
                                    </span>
                                    <p
                                        v-if="student.graduation_record"
                                        class="mt-1 font-mono text-xs text-slate-500"
                                    >
                                        {{
                                            student.graduation_record
                                                .certificate_number
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/graduations/${student.id}`"
                                            class="inline-flex items-center gap-2 rounded-md bg-blue-900 px-3 py-2 text-xs font-bold text-white hover:bg-blue-800"
                                        >
                                            <Eye class="h-4 w-4" /> عرض
                                        </Link>
                                        <a
                                            v-if="student.graduation_record"
                                            :href="`/graduation-records/${student.graduation_record.id}/certificate`"
                                            class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700"
                                        >
                                            <Printer class="h-4 w-4" /> شهادة
                                        </a>
                                        <a
                                            v-if="student.graduation_record"
                                            :href="`/graduation-records/${student.graduation_record.id}/study-report`"
                                            class="inline-flex items-center gap-2 rounded-md bg-orange-500 px-3 py-2 text-xs font-bold text-white hover:bg-orange-600"
                                        >
                                            <FileText class="h-4 w-4" /> تقرير
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="students.data.length === 0">
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <ShieldCheck
                                        class="mx-auto h-10 w-10 text-slate-300"
                                    />
                                    <p class="mt-3 font-bold text-slate-600">
                                        لا توجد نتائج مطابقة للفلاتر الحالية.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div
                v-if="students.links.length > 3"
                class="flex flex-wrap items-center justify-between gap-3"
            >
                <p class="text-sm font-bold text-slate-500">
                    عرض {{ students.from ?? 0 }} - {{ students.to ?? 0 }} من
                    {{ students.total }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in students.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        preserve-scroll
                        preserve-state
                        class="rounded-md border px-3 py-2 text-sm font-bold"
                        :class="[
                            link.active
                                ? 'border-blue-900 bg-blue-900 text-white'
                                : 'border-slate-200 bg-white text-slate-700',
                            !link.url ? 'pointer-events-none opacity-50' : '',
                        ]"
                    >
                        <ChevronRight
                            v-if="link.label.includes('Previous')"
                            class="h-4 w-4"
                        />
                        <ChevronLeft
                            v-else-if="link.label.includes('Next')"
                            class="h-4 w-4"
                        />
                        <span v-else v-html="link.label" />
                    </Link>
                </div>
            </div>
        </div>
    </main>
</template>
