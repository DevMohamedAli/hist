<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { MonitorSmartphone, Palette, Sparkles } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'خيارات الواجهة',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="خيارات الواجهة" />

    <main class="space-y-6" dir="rtl">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="bg-blue-950 px-6 py-8 text-white md:px-8">
                <p class="flex items-center gap-2 text-sm font-black text-orange-300">
                    <Sparkles class="h-4 w-4" />
                    مزاج الواجهة
                </p>
                <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <h1 class="text-3xl font-black tracking-normal md:text-4xl">
                            اختر شكل النظام المناسب لك
                        </h1>
                        <p class="mt-3 text-sm leading-7 text-blue-100 md:text-base">
                            بدّل بين الوضع الفاتح، الداكن، أو اجعل المنظومة تتبع إعدادات جهازك تلقائياً.
                        </p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                        <div class="flex items-center gap-3">
                            <MonitorSmartphone class="h-8 w-8 text-orange-300" />
                            <div>
                                <p class="text-xs font-bold text-blue-100">تجربة مريحة</p>
                                <p class="font-black">تتغير فوراً بدون إعادة تحميل</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-6 md:p-8">
                <div class="flex items-center gap-3">
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-700">
                        <Palette class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-950">أنماط العرض</h2>
                        <p class="text-sm font-bold text-slate-500">
                            اختر النمط الذي يناسب وقتك وطريقة استخدامك.
                        </p>
                    </div>
                </div>

                <AppearanceTabs />
            </div>
        </section>
    </main>
</template>
