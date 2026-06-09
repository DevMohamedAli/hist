<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- الخلفية الشاملة للصفحة باللون الرمادي المائل للأزرق المعتمد في الصورة لمنع تداخل ألوان الوضع الداكن -->
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden bg-[#f4f5f9] p-6 md:p-10 dark:bg-[#f4f5f9]"
        dir="rtl"
    >
        <!-- النقاط الهندسية البنفسجية بالخلفية (تطابق الصورة المرفقة) -->
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

        <!-- تم تكبير وتوسيع الحاوية إلى max-w-md لتتسع للبطاقة البيضاء بدون ضغط طولي -->
        <div class="relative z-10 w-full max-w-md">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium transition-opacity hover:opacity-90"
                    >
                        <!-- تأطير حجم الشعار الدائري الأكاديمي ليكون بارتفاع مثالي وتجنب صغر حجمه -->
                        <div
                            class="mb-1 flex h-35 w-auto items-center justify-center"
                        >
                            <AppLogoIcon
                                class="h-35 w-auto max-w-35 object-contain text-blue-800"
                            />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>

                    <!-- عرض العنوان والوصف المخصصين بشكل متناسق مع النمط الجديد إذا تم تزويدهما -->
                    <div
                        v-if="title || description"
                        class="space-y-2 text-center"
                    >
                        <h1
                            class="text-xl font-bold text-neutral-800 dark:text-neutral-800"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-center text-sm text-neutral-500 dark:text-neutral-500"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>

                <!-- حقن بطاقة تسجيل الدخول الأنيقة مع الحقول برمجياً هنا -->
                <slot />
            </div>
        </div>
    </div>
</template>
