<script setup lang="ts">
import {
    CalendarDays,
    ChevronsLeft,
    ChevronsRight,
    ChevronLeft,
    ChevronRight,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        id?: string;
        placeholder?: string;
        required?: boolean;
        disabled?: boolean;
        min?: string;
        max?: string;
        yearRange?: number;
    }>(),
    {
        placeholder: 'اختر التاريخ',
        required: false,
        disabled: false,
        min: '',
        max: '',
        yearRange: 80,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const open = ref(false);
const today = new Date();
const viewYear = ref(today.getFullYear());
const viewMonth = ref(today.getMonth());

const monthNames = [
    'يناير',
    'فبراير',
    'مارس',
    'أبريل',
    'مايو',
    'يونيو',
    'يوليو',
    'أغسطس',
    'سبتمبر',
    'أكتوبر',
    'نوفمبر',
    'ديسمبر',
];

const weekDays = [
    'السبت',
    'الأحد',
    'الاثنين',
    'الثلاثاء',
    'الأربعاء',
    'الخميس',
    'الجمعة',
];

const pad = (value: number) => String(value).padStart(2, '0');
const toIsoDate = (date: Date) =>
    `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

const parseIsoDate = (value: string) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        return null;
    }

    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(year, month - 1, day);

    if (
        date.getFullYear() !== year ||
        date.getMonth() !== month - 1 ||
        date.getDate() !== day
    ) {
        return null;
    }

    return date;
};

const minDate = computed(() => parseIsoDate(props.min));
const maxDate = computed(() => parseIsoDate(props.max));

const selectedDate = computed(() => parseIsoDate(props.modelValue));

watch(
    () => props.modelValue,
    (value) => {
        const parsed = parseIsoDate(value);

        if (parsed) {
            viewYear.value = parsed.getFullYear();
            viewMonth.value = parsed.getMonth();
        }
    },
    { immediate: true },
);

const formattedValue = computed(() => {
    if (!selectedDate.value) {
        return '';
    }

    return new Intl.DateTimeFormat('ar-LY', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(selectedDate.value);
});

const monthLabel = computed(
    () => `${monthNames[viewMonth.value]} ${viewYear.value}`,
);

const yearOptions = computed(() => {
    const minYear =
        minDate.value?.getFullYear() ?? today.getFullYear() - props.yearRange;
    const maxYear = maxDate.value?.getFullYear() ?? today.getFullYear() + 10;
    const years: number[] = [];

    for (let year = maxYear; year >= minYear; year -= 1) {
        years.push(year);
    }

    if (!years.includes(viewYear.value)) {
        years.push(viewYear.value);
        years.sort((a, b) => b - a);
    }

    return years;
});

const calendarDays = computed(() => {
    const firstDay = new Date(viewYear.value, viewMonth.value, 1);
    const startOffset = (firstDay.getDay() + 1) % 7;
    const start = new Date(viewYear.value, viewMonth.value, 1 - startOffset);
    const days: Date[] = [];

    for (let index = 0; index < 42; index += 1) {
        days.push(
            new Date(
                start.getFullYear(),
                start.getMonth(),
                start.getDate() + index,
            ),
        );
    }

    return days;
});

const isDisabledDate = (date: Date) => {
    const value = toIsoDate(date);

    return Boolean(
        (props.min && value < props.min) || (props.max && value > props.max),
    );
};

const isSelected = (date: Date) => props.modelValue === toIsoDate(date);
const isToday = (date: Date) => toIsoDate(date) === toIsoDate(today);
const isCurrentMonth = (date: Date) => date.getMonth() === viewMonth.value;

const clampDayForMonth = (year: number, month: number, day: number) => {
    return Math.min(day, new Date(year, month + 1, 0).getDate());
};

const setView = (year: number, month: number) => {
    const normalized = new Date(year, month, 1);
    viewYear.value = normalized.getFullYear();
    viewMonth.value = normalized.getMonth();
};

const previousMonth = () => setView(viewYear.value, viewMonth.value - 1);
const nextMonth = () => setView(viewYear.value, viewMonth.value + 1);
const previousYear = () => setView(viewYear.value - 1, viewMonth.value);
const nextYear = () => setView(viewYear.value + 1, viewMonth.value);

const changeMonth = (event: Event) => {
    const value = Number((event.target as HTMLSelectElement).value);
    setView(viewYear.value, value);
};

const changeYear = (event: Event) => {
    const value = Number((event.target as HTMLSelectElement).value);
    setView(value, viewMonth.value);
};

const selectDate = (date: Date) => {
    if (isDisabledDate(date)) {
        return;
    }

    emit('update:modelValue', toIsoDate(date));
    open.value = false;
};

const selectToday = () => {
    if (isDisabledDate(today)) {
        return;
    }

    setView(today.getFullYear(), today.getMonth());
    emit('update:modelValue', toIsoDate(today));
    open.value = false;
};

const clearDate = () => {
    emit('update:modelValue', '');
};

const syncSelectedDayIntoView = () => {
    const day = selectedDate.value?.getDate() ?? 1;
    const clampedDay = clampDayForMonth(viewYear.value, viewMonth.value, day);
    const candidate = new Date(viewYear.value, viewMonth.value, clampedDay);

    if (!isDisabledDate(candidate)) {
        emit('update:modelValue', toIsoDate(candidate));
    }
};
</script>

<template>
    <div class="relative">
        <button
            :id="id"
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-md border border-gray-300 bg-white px-3 py-2.5 text-start text-sm shadow-sm transition focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400"
            :disabled="disabled"
            :aria-required="required"
            @click="open = !open"
        >
            <span
                class="min-w-0 truncate"
                :class="
                    formattedValue ? 'font-bold text-gray-950' : 'text-gray-400'
                "
            >
                {{ formattedValue || placeholder }}
            </span>
            <CalendarDays class="h-5 w-5 shrink-0 text-blue-800" />
        </button>

        <input :value="modelValue" type="hidden" :required="required" />

        <div
            v-if="open"
            class="absolute z-40 mt-2 w-full min-w-[340px] rounded-lg border border-gray-200 bg-white p-3 shadow-xl"
        >
            <div class="mb-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-200 p-2 text-gray-700 hover:bg-gray-50"
                        title="السنة السابقة"
                        @click="previousYear"
                    >
                        <ChevronsRight class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-gray-200 p-2 text-gray-700 hover:bg-gray-50"
                        title="الشهر السابق"
                        @click="previousMonth"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>

                <p class="text-sm font-extrabold text-blue-950">
                    {{ monthLabel }}
                </p>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="rounded-md border border-gray-200 p-2 text-gray-700 hover:bg-gray-50"
                        title="الشهر التالي"
                        @click="nextMonth"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-gray-200 p-2 text-gray-700 hover:bg-gray-50"
                        title="السنة التالية"
                        @click="nextYear"
                    >
                        <ChevronsLeft class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div class="mb-3 grid grid-cols-2 gap-2">
                <label class="text-xs font-bold text-gray-600">
                    الشهر
                    <select
                        class="mt-1 w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-sm font-bold text-gray-950 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        :value="viewMonth"
                        @change="changeMonth"
                    >
                        <option
                            v-for="(month, index) in monthNames"
                            :key="month"
                            :value="index"
                        >
                            {{ month }}
                        </option>
                    </select>
                </label>

                <label class="text-xs font-bold text-gray-600">
                    السنة
                    <select
                        class="mt-1 w-full rounded-md border border-gray-300 bg-white px-2 py-2 text-sm font-bold text-gray-950 focus:border-blue-700 focus:ring-2 focus:ring-blue-700/15 focus:outline-none"
                        :value="viewYear"
                        @change="changeYear"
                    >
                        <option
                            v-for="year in yearOptions"
                            :key="year"
                            :value="year"
                        >
                            {{
                                year.toLocaleString('ar-LY-u-nu-latn', {
                                    useGrouping: false,
                                })
                            }}
                        </option>
                    </select>
                </label>
            </div>

            <div class="grid grid-cols-7 gap-1 text-center">
                <div
                    v-for="day in weekDays"
                    :key="day"
                    class="py-1 text-[11px] font-extrabold text-gray-500"
                >
                    {{ day.slice(0, 3) }}
                </div>

                <button
                    v-for="date in calendarDays"
                    :key="toIsoDate(date)"
                    type="button"
                    class="aspect-square rounded-md text-sm font-bold transition"
                    :class="[
                        isSelected(date)
                            ? 'bg-blue-800 text-white shadow-sm'
                            : isToday(date)
                              ? 'bg-orange-50 text-orange-700 ring-1 ring-orange-200'
                              : isCurrentMonth(date)
                                ? 'text-gray-800 hover:bg-blue-50'
                                : 'text-gray-300 hover:bg-gray-50',
                        isDisabledDate(date)
                            ? 'cursor-not-allowed opacity-35 hover:bg-transparent'
                            : '',
                    ]"
                    :disabled="isDisabledDate(date)"
                    @click="selectDate(date)"
                >
                    {{ date.getDate().toLocaleString('ar-LY-u-nu-latn') }}
                </button>
            </div>

            <div
                class="mt-3 flex items-center justify-between gap-2 border-t border-gray-100 pt-3"
            >
                <button
                    type="button"
                    class="rounded-md border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50"
                    :disabled="isDisabledDate(today)"
                    @click="selectToday"
                >
                    اليوم
                </button>

                <button
                    v-if="modelValue"
                    type="button"
                    class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50"
                    @click="clearDate"
                >
                    <X class="h-3 w-3" />
                    مسح التاريخ
                </button>

                <button
                    v-if="selectedDate"
                    type="button"
                    class="rounded-md bg-blue-50 px-3 py-2 text-xs font-bold text-blue-800 hover:bg-blue-100"
                    @click="syncSelectedDayIntoView"
                >
                    تطبيق الشهر والسنة
                </button>
            </div>
        </div>
    </div>
</template>
