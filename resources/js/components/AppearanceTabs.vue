<script setup lang="ts">
import { CheckCircle2, Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    {
        value: 'light',
        Icon: Sun,
        label: 'الوضع الفاتح',
        description: 'واجهة واضحة ومريحة للاستخدام الصباحي.',
        preview: 'from-white via-sky-50 to-orange-50',
    },
    {
        value: 'dark',
        Icon: Moon,
        label: 'الوضع الداكن',
        description: 'ألوان هادئة ومناسبة للعمل الليلي.',
        preview: 'from-slate-950 via-blue-950 to-slate-800',
    },
    {
        value: 'system',
        Icon: Monitor,
        label: 'حسب الجهاز',
        description: 'يتبع إعدادات النظام تلقائياً.',
        preview: 'from-white via-slate-100 to-slate-900',
    },
] as const;
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3" dir="rtl">
        <button
            v-for="{ value, Icon, label, description, preview } in tabs"
            :key="value"
            type="button"
            @click="updateAppearance(value)"
            :class="[
                'group overflow-hidden rounded-3xl border bg-white text-right shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl',
                appearance === value
                    ? 'border-blue-300 ring-4 ring-blue-100'
                    : 'border-slate-200 hover:border-blue-200',
            ]"
        >
            <div :class="['h-28 bg-gradient-to-br p-4', preview]">
                <div class="flex h-full items-start justify-between">
                    <div
                        class="rounded-2xl bg-white/85 p-3 shadow-sm backdrop-blur dark:bg-white/10"
                    >
                        <component
                            :is="Icon"
                            :class="[
                                'h-6 w-6',
                                value === 'dark'
                                    ? 'text-orange-300'
                                    : 'text-blue-800',
                            ]"
                        />
                    </div>
                    <CheckCircle2
                        v-if="appearance === value"
                        class="h-6 w-6 text-emerald-500"
                    />
                </div>
            </div>

            <div class="space-y-2 p-5">
                <h3 class="text-lg font-black text-slate-950">{{ label }}</h3>
                <p class="text-sm leading-6 font-bold text-slate-500">
                    {{ description }}
                </p>
                <div
                    :class="[
                        'mt-4 rounded-2xl px-4 py-3 text-center text-sm font-black transition',
                        appearance === value
                            ? 'bg-blue-600 text-white'
                            : 'bg-slate-100 text-slate-600 group-hover:bg-blue-50 group-hover:text-blue-900',
                    ]"
                >
                    {{
                        appearance === value
                            ? 'مفعّل حالياً'
                            : 'اختيار هذا النمط'
                    }}
                </div>
            </div>
        </button>
    </div>
</template>
