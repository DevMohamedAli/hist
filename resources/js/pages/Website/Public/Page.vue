<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeCheck,
    BookOpenCheck,
    GraduationCap,
    Sparkles,
    UsersRound,
} from 'lucide-vue-next';
import PublicLayout from '@/components/public/PublicLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: [] });

const props = defineProps<{
    page: {
        title: string;
        summary?: string;
        content?: string;
        featured_image_path?: string;
        featured_image_url?: string;
        seo_title?: string;
        seo_description?: string;
    };
}>();

const fallbackImage = '/assets/img/website-news-hero.svg';
const pageImage = computed(
    () =>
        props.page.featured_image_url ||
        props.page.featured_image_path ||
        fallbackImage,
);
const cleanSummary = computed(
    () =>
        props.page.summary ||
        'مساحة تعريفية حديثة تعرض هوية المعهد ورسالته وخدماته للطلاب والزوار.',
);

const highlights = [
    {
        title: 'تعليم تطبيقي',
        description: 'برامج مرتبطة بسوق العمل وخبرة عملية أقرب للواقع.',
        icon: GraduationCap,
    },
    {
        title: 'خدمات رقمية',
        description: 'أخبار وتسجيل وتواصل من مكان واحد وبواجهة واضحة.',
        icon: Sparkles,
    },
    {
        title: 'مجتمع طلابي',
        description: 'بيئة مساندة للطالب من أول يوم وحتى التخرج.',
        icon: UsersRound,
    },
];
</script>

<template>
    <Head>
        <title>{{ page.seo_title || page.title }}</title>
        <meta
            v-if="page.seo_description"
            name="description"
            :content="page.seo_description"
        />
        <meta property="og:title" :content="page.seo_title || page.title" />
        <meta property="og:image" :content="pageImage" />
    </Head>

    <PublicLayout>
        <main class="overflow-hidden bg-slate-50" dir="rtl">
            <section
                class="relative isolate overflow-hidden bg-blue-950 text-white"
            >
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(249,115,22,0.25),transparent_28%),radial-gradient(circle_at_80%_10%,rgba(59,130,246,0.35),transparent_30%)]"
                />
                <div
                    class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-slate-50 to-transparent"
                />

                <div
                    class="relative mx-auto grid max-w-7xl gap-10 px-4 py-14 md:px-6 lg:grid-cols-[1fr_520px] lg:items-center lg:py-20"
                >
                    <div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-extrabold text-orange-200 backdrop-blur"
                        >
                            <BadgeCheck class="h-4 w-4" />
                            صفحة تعريفية محدثة
                        </span>
                        <h1
                            class="mt-6 text-4xl leading-tight font-black md:text-6xl"
                        >
                            {{ page.title }}
                        </h1>
                        <p
                            class="mt-5 max-w-2xl text-lg leading-9 text-blue-100"
                        >
                            {{ cleanSummary }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link
                                href="/#news"
                                class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-6 py-3 font-black text-white shadow-lg shadow-orange-500/25 transition hover:-translate-y-0.5 hover:bg-orange-600"
                            >
                                شاهد الأخبار
                                <ArrowLeft class="h-5 w-5" />
                            </Link>
                            <Link
                                href="/#contact"
                                class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 py-3 font-black text-white backdrop-blur transition hover:bg-white/20"
                            >
                                تواصل معنا
                            </Link>
                        </div>
                    </div>

                    <div class="relative">
                        <div
                            class="absolute -inset-4 rotate-2 rounded-[2rem] bg-orange-400/25 blur-2xl"
                        />
                        <figure
                            class="relative overflow-hidden rounded-[2rem] border border-white/15 bg-white/10 p-3 shadow-2xl backdrop-blur"
                        >
                            <img
                                :src="pageImage"
                                :alt="page.title"
                                class="aspect-[4/3] w-full rounded-[1.5rem] object-cover"
                            />
                            <figcaption
                                class="absolute right-7 bottom-7 rounded-2xl bg-blue-950/85 px-4 py-3 text-sm font-extrabold text-white shadow-lg backdrop-blur"
                            >
                                صورة صفحة عن المعهد
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </section>

            <section class="relative mx-auto -mt-8 max-w-7xl px-4 md:px-6">
                <div class="grid gap-4 md:grid-cols-3">
                    <article
                        v-for="item in highlights"
                        :key="item.title"
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 text-orange-500"
                        >
                            <component :is="item.icon" class="h-6 w-6" />
                        </div>
                        <h2 class="mt-4 text-xl font-black text-blue-950">
                            {{ item.title }}
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            {{ item.description }}
                        </p>
                    </article>
                </div>
            </section>

            <section
                class="mx-auto grid max-w-7xl gap-8 px-4 py-14 md:px-6 lg:grid-cols-[1fr_360px]"
            >
                <article
                    class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm md:p-10"
                >
                    <div class="mb-6 flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-950 text-orange-300"
                        >
                            <BookOpenCheck class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-extrabold text-orange-500">
                                نبذة سريعة
                            </p>
                            <h2 class="text-2xl font-black text-blue-950">
                                القصة، الرسالة، والخدمات
                            </h2>
                        </div>
                    </div>
                    <div
                        class="prose prose-slate prose-headings:text-blue-950 prose-a:text-orange-600 prose-strong:text-blue-950 max-w-none leading-8"
                        v-html="page.content"
                    />
                </article>

                <aside class="space-y-4">
                    <div
                        class="rounded-[2rem] bg-blue-950 p-6 text-white shadow-xl"
                    >
                        <p class="text-sm font-extrabold text-orange-300">
                            جاهز تبدأ؟
                        </p>
                        <h3 class="mt-2 text-2xl font-black">
                            كل خدمات الموقع قريبة منك
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-blue-100">
                            تابع الأخبار، تحقق من التسجيل، أو أرسل رسالة للإدارة
                            من الصفحة الرئيسية.
                        </p>
                        <Link
                            href="/"
                            class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-black text-blue-950 transition hover:bg-orange-100"
                        >
                            الرجوع للرئيسية
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </div>
                    <div
                        class="rounded-[2rem] border border-orange-100 bg-orange-50 p-6 text-orange-900"
                    >
                        <p class="text-sm font-black">ملاحظة</p>
                        <p class="mt-2 text-sm leading-7">
                            يمكن لمسؤول الموقع تغيير هذه الصورة والنص من لوحة
                            التحكم في صفحة "عن المعهد".
                        </p>
                    </div>
                </aside>
            </section>
        </main>
    </PublicLayout>
</template>
