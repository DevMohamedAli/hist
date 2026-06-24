<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/components/public/PublicLayout.vue';

defineOptions({ layout: [] });

defineProps<{
    faqs: Array<{
        id: number;
        question: string;
        answer: string;
    }>;
}>();
</script>

<template>
    <Head>
        <title>الأسئلة الشائعة</title>
        <meta
            name="description"
            content="الأسئلة الشائعة حول خدمات المعهد والقبول والتسجيل."
        />
    </Head>

    <PublicLayout>
        <main>
            <section class="relative overflow-hidden bg-blue-950 text-white">
                <img
                    src="/assets/img/website-news-hero.svg"
                    alt="الأسئلة الشائعة"
                    class="absolute inset-0 h-full w-full object-cover opacity-35"
                />
                <div class="absolute inset-0 bg-gradient-to-l from-blue-950 via-blue-950/90 to-blue-900/50" />
                <div class="relative mx-auto max-w-4xl px-4 py-20">
                    <p class="text-sm font-bold text-orange-300">مركز المساعدة</p>
                    <h1 class="mt-3 text-4xl font-extrabold">
                        الأسئلة الشائعة
                    </h1>
                    <p class="mt-4 max-w-2xl leading-8 text-blue-100">
                        إجابات مختصرة على أكثر الأسئلة تكراراً حول القبول والخدمات الإلكترونية.
                    </p>
                </div>
            </section>

            <section class="mx-auto max-w-4xl px-4 py-12">
                <div class="grid gap-4">
                    <details
                        v-for="faq in faqs"
                        :key="faq.id"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <summary class="cursor-pointer text-lg font-extrabold text-blue-950">
                            {{ faq.question }}
                        </summary>
                        <p class="mt-4 leading-8 text-slate-700">
                            {{ faq.answer }}
                        </p>
                    </details>
                    <p
                        v-if="!faqs.length"
                        class="rounded-2xl bg-white p-8 text-center text-slate-500 shadow-sm"
                    >
                        لا توجد أسئلة شائعة منشورة حالياً.
                    </p>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
