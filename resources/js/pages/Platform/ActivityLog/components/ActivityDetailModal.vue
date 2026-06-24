<script setup lang="ts">
import {
    ArrowLeftRight,
    CalendarClock,
    Database,
    FileText,
    Globe2,
    LinkIcon,
    User,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { formatDisplayDate, formatDisplayDateTime } from '@/lib/date';

type ActivityValue = unknown;
type ActivityRecord = Record<string, ActivityValue>;

interface SpecializationOption {
    id: number;
    name: string;
}

interface ActivityChanges {
    old?: ActivityRecord | null;
    attributes?: ActivityRecord | null;
}

interface ActivityProperties extends ActivityChanges {
    [key: string]: ActivityValue;
}

interface Activity {
    id: number;
    description: string;
    event: string | null;
    causer: { id: number; name: string; email: string } | null;
    subject_type: string | null;
    subject_id: number | null;
    properties: ActivityProperties | null;
    created_at: string;
    attribute_changes?: ActivityChanges | null;
}

const props = withDefaults(
    defineProps<{
        activity: Activity | null;
        open: boolean;
        specializations?: SpecializationOption[];
    }>(),
    {
        specializations: () => [],
    },
);

const emit = defineEmits<{
    close: [];
}>();

const eventLabels: Record<string, string> = {
    created: 'إنشاء',
    updated: 'تحديث',
    deleted: 'حذف',
    restored: 'استرجاع',
    login: 'تسجيل دخول',
    logout: 'تسجيل خروج',
};

const eventBadgeClasses: Record<string, string> = {
    created: 'border-green-200 bg-green-50 text-green-800',
    updated: 'border-blue-200 bg-blue-50 text-blue-800',
    deleted: 'border-red-200 bg-red-50 text-red-800',
    restored: 'border-amber-200 bg-amber-50 text-amber-800',
    login: 'border-purple-200 bg-purple-50 text-purple-800',
    logout: 'border-gray-200 bg-gray-50 text-gray-700',
};

const subjectLabels: Record<string, string> = {
    Student: 'طالب',
    User: 'مستخدم',
    Department: 'قسم علمي',
    Specialization: 'تخصص',
    Course: 'مقرر',
    CourseClass: 'شعبة دراسية',
    AcademicSemester: 'فصل دراسي',
    StudyGroup: 'مجموعة دراسية',
    Instructor: 'عضو هيئة تدريس',
    Qualification: 'مؤهل علمي',
    ImportJob: 'عملية استيراد',
};

const fieldLabels: Record<string, string> = {
    id: 'المعرف',
    registration_number: 'رقم القيد',
    full_name: 'اسم الطالب',
    name: 'الاسم',
    national_id: 'الرقم الوطني',
    gender: 'الجنس',
    nationality: 'الجنسية',
    birth_date: 'تاريخ الميلاد',
    mobile: 'رقم الهاتف',
    phone: 'رقم الهاتف',
    email: 'البريد الإلكتروني',
    admission_date: 'تاريخ الانتساب',
    qualification: 'المؤهل السابق',
    current_specialization_id: 'التخصص الحالي',
    current_semester_level: 'مستوى الفصل الدراسي',
    status: 'الحالة',
    user_id: 'حساب المستخدم',
    department_id: 'القسم العلمي',
    code: 'الرمز',
    description: 'الوصف',
    semesters_count: 'عدد الفصول',
    created_at: 'تاريخ الإنشاء',
    updated_at: 'آخر تحديث',
};

const valueLabels: Record<string, Record<string, string>> = {
    gender: {
        Male: 'ذكر',
        Female: 'أنثى',
    },
    status: {
        Active: 'نشط',
        Suspended: 'موقوف',
        Transferred_Out: 'منقول خارجيا',
        Withdrawn: 'منسحب',
        Dismissed: 'مفصول',
        Graduated: 'متخرج',
    },
};

const hiddenUnchangedFields = new Set([
    'id',
    'created_at',
    'updated_at',
    'user_id',
]);

const toRecord = (value: ActivityValue): ActivityRecord => {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return {};
    }

    return value as ActivityRecord;
};

const normalizeComparable = (value: ActivityValue) =>
    JSON.stringify(value ?? null);

const formatFieldLabel = (field: string) => {
    return fieldLabels[field] ?? field.replace(/_/g, ' ');
};

const classBasename = (type: string | null) => {
    if (!type) {
        return '';
    }

    return type.split('\\').pop() ?? type;
};

const subjectLabel = computed(() => {
    const basename = classBasename(props.activity?.subject_type ?? null);

    return subjectLabels[basename] ?? (basename || 'غير محدد');
});

const normalizedChanges = computed(() => {
    if (!props.activity) {
        return { old: {}, attributes: {} };
    }

    const source =
        props.activity.attribute_changes ?? props.activity.properties ?? {};

    return {
        old: toRecord(source.old),
        attributes: toRecord(source.attributes),
    };
});

const changeRows = computed(() => {
    const oldValues = normalizedChanges.value.old;
    const newValues = normalizedChanges.value.attributes;
    const keys = Array.from(
        new Set([...Object.keys(oldValues), ...Object.keys(newValues)]),
    );

    return keys.map((field) => {
        const oldValue = oldValues[field];
        const newValue = newValues[field];
        const changed =
            normalizeComparable(oldValue) !== normalizeComparable(newValue);

        return {
            field,
            label: formatFieldLabel(field),
            oldValue,
            newValue,
            changed,
            technical: hiddenUnchangedFields.has(field),
        };
    });
});

const changedRows = computed(() =>
    changeRows.value.filter((row) => row.changed),
);
const visibleChangedRows = computed(() =>
    changedRows.value.filter(
        (row) => !row.technical || row.field === 'updated_at',
    ),
);
const unchangedRowsCount = computed(
    () => changeRows.value.filter((row) => !row.changed).length,
);
const technicalChangedRowsCount = computed(
    () => changedRows.value.length - visibleChangedRows.value.length,
);

const additionalProperties = computed(() => {
    if (!props.activity?.properties) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(props.activity.properties).filter(
            ([key]) => !['old', 'attributes'].includes(key),
        ),
    );
});

const readableProperties = computed(() => {
    const entries: Array<{
        key: string;
        label: string;
        value: ActivityValue;
        icon: 'ip' | 'url' | 'other';
    }> = [];

    for (const [key, value] of Object.entries(additionalProperties.value)) {
        let label = formatFieldLabel(key);
        let displayValue = value;
        let icon: 'ip' | 'url' | 'other' = 'other';

        if (key === 'ip') {
            label = 'عنوان IP';
            icon = 'ip';
        } else if (key === 'url') {
            label = 'الرابط';
            icon = 'url';
        } else if (key === 'student_id') {
            label = 'معرف الطالب';
        } else if (key === 'registration_number') {
            label = 'رقم القيد';
        } else if (
            key === 'from_specialization_id' ||
            key === 'to_specialization_id'
        ) {
            const specialization = props.specializations.find(
                (item) => item.id === Number(value),
            );
            label = key === 'from_specialization_id' ? 'من تخصص' : 'إلى تخصص';
            displayValue = specialization?.name ?? value;
        }

        entries.push({ key, label, value: displayValue, icon });
    }

    return entries;
});

const hasChanges = computed(() => changeRows.value.length > 0);
const hasAdditionalProperties = computed(
    () => readableProperties.value.length > 0,
);

const formatDate = (date: string) => {
    return formatDisplayDateTime(date);
};

const formatValue = (field: string, value: ActivityValue) => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (field === 'current_specialization_id') {
        const specialization = props.specializations.find(
            (item) => item.id === Number(value),
        );

        return specialization?.name ?? String(value);
    }

    if (field.endsWith('_at') || field.endsWith('_date')) {
        const date = new Date(String(value));

        if (!Number.isNaN(date.getTime())) {
            return field.endsWith('_at')
                ? formatDate(String(value))
                : formatDisplayDate(String(value));
        }
    }

    const valueKey = String(value);

    if (valueLabels[field]?.[valueKey]) {
        return valueLabels[field][valueKey];
    }

    if (typeof value === 'object') {
        return JSON.stringify(value, null, 2);
    }

    return String(value);
};

const eventLabel = computed(() => {
    const event = props.activity?.event ?? '';

    return eventLabels[event] ?? (event || 'غير معروف');
});
</script>

<template>
    <Dialog :open="open" @update:open="(value) => !value && emit('close')">
        <DialogContent
            class="max-h-[88vh] overflow-y-auto p-0 sm:max-w-5xl"
            dir="rtl"
        >
            <DialogHeader
                class="border-b border-gray-100 bg-slate-50 px-5 py-4 text-right"
            >
                <DialogTitle
                    class="flex items-center gap-2 text-xl font-extrabold text-blue-950"
                >
                    <FileText class="h-5 w-5 text-orange-500" />
                    تفاصيل النشاط
                </DialogTitle>
                <DialogDescription
                    class="text-right text-sm leading-6 text-gray-600"
                >
                    ملخص واضح للتغيير الفعلي والبيانات المرتبطة به.
                </DialogDescription>
            </DialogHeader>

            <div v-if="activity" class="space-y-5 p-5">
                <section class="grid gap-3 md:grid-cols-4">
                    <div
                        class="rounded-lg border border-blue-100 bg-blue-50 p-3"
                    >
                        <p class="text-xs font-bold text-blue-700">الحدث</p>
                        <Badge
                            class="mt-2 border px-3 py-1 text-sm font-extrabold"
                            :class="
                                eventBadgeClasses[activity.event ?? ''] ??
                                'border-orange-200 bg-orange-50 text-orange-800'
                            "
                        >
                            {{ eventLabel }}
                        </Badge>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-xs font-bold text-gray-500">المستخدم</p>
                        <div class="mt-2 flex min-w-0 items-center gap-2">
                            <User class="h-4 w-4 shrink-0 text-orange-500" />
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-extrabold text-gray-950"
                                >
                                    {{ activity.causer?.name || 'النظام' }}
                                </p>
                                <p
                                    v-if="activity.causer?.email"
                                    class="truncate text-xs text-gray-500"
                                >
                                    {{ activity.causer.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-xs font-bold text-gray-500">الموضوع</p>
                        <p class="mt-2 text-sm font-extrabold text-gray-950">
                            {{ subjectLabel }}
                        </p>
                        <p
                            v-if="activity.subject_type"
                            class="mt-1 truncate text-[11px] text-gray-400"
                            dir="ltr"
                        >
                            {{ classBasename(activity.subject_type) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-3">
                        <p class="text-xs font-bold text-gray-500">
                            التاريخ والوقت
                        </p>
                        <div
                            class="mt-2 flex items-center gap-2 text-sm font-extrabold text-gray-950"
                        >
                            <CalendarClock class="h-4 w-4 text-blue-700" />
                            {{ formatDate(activity.created_at) }}
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-xs font-bold text-gray-500">الوصف</p>
                    <p class="mt-2 text-base leading-7 font-bold text-gray-950">
                        {{ activity.description }}
                    </p>
                    <p
                        v-if="activity.subject_id"
                        class="mt-2 text-sm text-gray-500"
                    >
                        معرف السجل:
                        <span class="font-mono font-bold text-gray-800">{{
                            activity.subject_id
                        }}</span>
                    </p>
                </section>

                <section
                    v-if="hasChanges"
                    class="overflow-hidden rounded-lg border border-blue-100 bg-white"
                >
                    <div
                        class="flex flex-col gap-2 border-b border-blue-100 bg-blue-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-2">
                            <ArrowLeftRight class="h-4 w-4 text-orange-500" />
                            <h3 class="font-extrabold text-blue-950">
                                التغييرات الفعلية
                            </h3>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <span
                                class="rounded-full bg-white px-2.5 py-1 text-blue-800"
                            >
                                {{ visibleChangedRows.length }} حقل تغير
                            </span>
                            <span
                                v-if="unchangedRowsCount"
                                class="rounded-full bg-white px-2.5 py-1 text-gray-500"
                            >
                                {{ unchangedRowsCount }} بدون تغيير
                            </span>
                            <span
                                v-if="technicalChangedRowsCount"
                                class="rounded-full bg-white px-2.5 py-1 text-gray-500"
                            >
                                {{ technicalChangedRowsCount }} تقني مخفي
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="visibleChangedRows.length"
                        class="divide-y divide-gray-100"
                    >
                        <div
                            v-for="row in visibleChangedRows"
                            :key="row.field"
                            class="grid gap-3 p-4 lg:grid-cols-[180px_minmax(0,1fr)_minmax(0,1fr)]"
                        >
                            <div>
                                <p class="text-sm font-extrabold text-blue-950">
                                    {{ row.label }}
                                </p>
                                <p
                                    class="mt-1 text-[11px] text-gray-400"
                                    dir="ltr"
                                >
                                    {{ row.field }}
                                </p>
                            </div>

                            <div
                                class="rounded-md border border-red-100 bg-red-50 p-3"
                            >
                                <p class="text-xs font-extrabold text-red-700">
                                    القيمة السابقة
                                </p>
                                <pre
                                    class="mt-2 text-sm leading-6 break-words whitespace-pre-wrap text-red-950"
                                    >{{
                                        formatValue(row.field, row.oldValue)
                                    }}</pre
                                >
                            </div>

                            <div
                                class="rounded-md border border-green-100 bg-green-50 p-3"
                            >
                                <p
                                    class="text-xs font-extrabold text-green-700"
                                >
                                    القيمة الجديدة
                                </p>
                                <pre
                                    class="mt-2 text-sm leading-6 break-words whitespace-pre-wrap text-green-950"
                                    >{{
                                        formatValue(row.field, row.newValue)
                                    }}</pre
                                >
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-4 text-sm leading-7 text-gray-600">
                        لا توجد تغييرات عملية ظاهرة. التغيير المسجل تقني فقط أو
                        أن القيم لم تختلف.
                    </div>
                </section>

                <section
                    v-if="hasAdditionalProperties"
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white"
                >
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                        <h3 class="font-extrabold text-blue-950">
                            خصائص إضافية
                        </h3>
                    </div>

                    <div class="grid gap-3 p-4 md:grid-cols-2">
                        <div
                            v-for="entry in readableProperties"
                            :key="entry.key"
                            class="rounded-md border border-gray-100 bg-slate-50 p-3"
                        >
                            <div class="flex items-center gap-2">
                                <Globe2
                                    v-if="entry.icon === 'ip'"
                                    class="h-4 w-4 text-blue-700"
                                />
                                <LinkIcon
                                    v-else-if="entry.icon === 'url'"
                                    class="h-4 w-4 text-blue-700"
                                />
                                <Database
                                    v-else
                                    class="h-4 w-4 text-blue-700"
                                />
                                <p class="text-xs font-extrabold text-gray-500">
                                    {{ entry.label }}
                                </p>
                            </div>
                            <p
                                class="mt-2 text-sm font-bold break-words text-gray-950"
                                :dir="entry.icon === 'url' ? 'ltr' : 'rtl'"
                            >
                                {{ formatValue(entry.key, entry.value) }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <DialogFooter
                class="border-t border-gray-100 bg-slate-50 px-5 py-4"
            >
                <Button
                    variant="outline"
                    class="font-bold"
                    @click="emit('close')"
                    >إغلاق</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
