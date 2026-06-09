<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- الخلفية الشاملة للصفحة باللون الرمادي المائل للأزرق المعتمد في الصورة -->
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-[#f4f5f9] p-6 md:p-10 dark:bg-[#f4f5f9]"
        dir="rtl"
    >
        <!-- النقاط الهندسية البنفسجية الفخمة بالخلفية (تطابق الصورة تماماً) -->
        <!-- أعلى اليمين -->
        <div
            class="pointer-events-none absolute top-10 right-10 grid grid-cols-6 gap-2.5 opacity-45 sm:grid"
        >
            <div
                v-for="i in 36"
                :key="'tr-' + i"
                class="h-1.5 w-1.5 rounded-full bg-[#818cf8]"
            ></div>
        </div>
        <!-- أسفل اليسار -->
        <div
            class="pointer-events-none absolute bottom-10 left-10 grid grid-cols-6 gap-2.5 opacity-45 sm:grid"
        >
            <div
                v-for="i in 36"
                :key="'bl-' + i"
                class="h-1.5 w-1.5 rounded-full bg-[#818cf8]"
            ></div>
        </div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <!-- حاوية الشعار الرئيسي الأكاديمي الدائري المعتمد للمعهد (محددة القياس لمنع التمدد) -->
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium transition-opacity hover:opacity-90"
            >
                <div class="flex h-14 w-auto items-center justify-center">
                    <AppLogoIcon
                        class="h-14 w-auto max-w-30 object-contain text-blue-800"
                    />
                </div>
            </Link>

            <!-- البطاقة البيضاء الأساسية المحدثة بالخطوط والظل الأنيق -->
            <div class="flex flex-col gap-6">
                <Card
                    class="rounded-2xl border border-gray-100 bg-white text-neutral-900 shadow-xl dark:bg-white dark:text-neutral-900"
                >
                    <!-- الترويسة الفرعية (تظهر فقط عند تمرير عنوان أو وصف) -->
                    <CardHeader
                        v-if="title || description"
                        class="border-b border-gray-50/80 px-10 pt-8 pb-0 text-center"
                    >
                        <CardTitle
                            class="text-xl font-bold text-neutral-800 dark:text-neutral-800"
                            >{{ title }}
                        </CardTitle>
                        <CardDescription
                            class="mt-1 text-neutral-500 dark:text-neutral-500"
                        >
                            {{ description }}
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="px-8 py-8 sm:px-10">
                        <!-- حقن حقول تسجيل الدخول هنا -->
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
