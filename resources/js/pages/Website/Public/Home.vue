<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpenCheck,
    CalendarDays,
    CheckCircle2,
    ChevronLeft,
    CircleHelp,
    Clock3,
    ExternalLink,
    GraduationCap,
    Landmark,
    LogIn,
    Mail,
    Megaphone,
    Phone,
    ShieldCheck,
    Sparkles,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import PublicLayout from '@/components/public/PublicLayout.vue';

defineOptions({ layout: [] });

const props = defineProps<{
    settings: Record<string, string | null>;
    statistics: {
        year: number;
        students: number;
        graduates: number;
        instructors: number;
        employees: number;
    };
    banners: Array<{
        id: number;
        title: string;
        subtitle?: string;
        image_path?: string;
        image_url?: string;
        link_url?: string;
        link_label?: string;
    }>;
    news: Array<{
        id: number;
        title: string;
        slug: string;
        summary?: string;
        featured_image_path?: string;
        featured_image_url?: string;
    }>;
    announcements: Array<{ id: number; title: string; summary?: string }>;
    departments: Array<{
        id: number;
        name: string;
        description?: string;
        specializations: Array<{ id: number; name: string }>;
    }>;
    registration: {
        is_open: boolean;
        registration_url: string;
        requirements: string[];
        semester?: { code: string; registration_end?: string };
    };
    faqs: Array<{
        id: number;
        question: string;
        answer: string;
    }>;
    portalLoginUrl: string;
}>();

const defaultHeroImage = props.settings.hero_image_url || '/assets/img/website-news-hero.svg';
const instituteName = computed(() => props.settings.site_name || props.settings.institute_name || 'المعهد العالي');
const heroNews = computed(() => props.news[0]);
const heroImage = computed(() => heroNews.value?.featured_image_url || defaultHeroImage);

const sliderNews = computed(() =>
    props.news.length
        ? props.news
        : [
            {
                id: 0,
                title: 'أخبار المعهد تظهر هنا',
                slug: '',
                summary: 'يمكن لموظف إدارة الموقع إضافة أخبار جديدة من لوحة التحكم وستظهر تلقائياً في هذا الشريط.',
                featured_image_url: defaultHeroImage,
            },
        ],
);

const featuredNews = computed(() => sliderNews.value.slice(0, 6));
const topFaqs = computed(() => props.faqs.slice(0, 5));
const topAnnouncements = computed(() => props.announcements.slice(0, 4));
const registrationHref = computed(() => props.registration.registration_url || '#admission');

const formatNumber = (value: number) => new Intl.NumberFormat('ar-LY').format(value || 0);

const formatDate = (value?: string | null) => {
    if (!value) {
        return 'غير محدد';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('ar-LY', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(date);
};

const newsHref = (post: { slug?: string }) => (post.slug ? `/news/${post.slug}` : '#news');
const isExternalUrl = (url?: string | null) => Boolean(url && /^https?:\/\//i.test(url));
const linkTarget = (url?: string | null) => (isExternalUrl(url) ? '_blank' : undefined);

const statisticCards = computed(() => [
    {
        icon: UsersRound,
        value: props.statistics.students,
        label: `الطلاب لسنة ${props.statistics.year}`,
        helper: 'إحصائية محدثة من لوحة الإدارة',
    },
    {
        icon: GraduationCap,
        value: props.statistics.graduates,
        label: `الخريجون لسنة ${props.statistics.year}`,
        helper: 'مخرجات تعليمية معتمدة',
    },
    {
        icon: BookOpenCheck,
        value: props.statistics.instructors,
        label: `أعضاء هيئة التدريس لسنة ${props.statistics.year}`,
        helper: 'كوادر أكاديمية متخصصة',
    },
    {
        icon: ShieldCheck,
        value: props.statistics.employees,
        label: 'الموظفون',
        helper: 'خدمات إدارية مساندة',
    },
]);

const heroStats = computed(() => [
    {
        icon: Landmark,
        value: props.departments.length,
        label: 'قسم علمي',
    },
    {
        icon: Megaphone,
        value: sliderNews.value.length,
        label: 'خبر منشور',
    },
    {
        icon: CalendarDays,
        value: props.registration.is_open ? 1 : 0,
        label: props.registration.is_open ? 'تسجيل مفتوح' : 'تسجيل مغلق',
    },
]);

const contactInfo = computed(() => [
    {
        icon: Phone,
        label: 'الهاتف',
        value: props.settings.phone || props.settings.contact_phone || 'غير مضاف',
    },
    {
        icon: Mail,
        label: 'البريد الإلكتروني',
        value: props.settings.email || props.settings.contact_email || 'غير مضاف',
    },
]);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>

    <Head>
        <title>{{ instituteName }}</title>
        <meta name="description"
            content="الموقع الرسمي للمعهد: الأخبار، الإعلانات، الأقسام العلمية، ومعلومات التسجيل." />
        <meta property="og:title" :content="instituteName" />
    </Head>

    <PublicLayout>
        <main class="bg-slate-50 text-slate-900">
            <!-- Hero -->
            <section class="hero-modern relative isolate overflow-hidden text-white" aria-labelledby="home-hero-title">
                <div class="hero-grid" aria-hidden="true" />
                <div class="hero-orb hero-orb--orange" aria-hidden="true" />
                <div class="hero-orb hero-orb--blue" aria-hidden="true" />
                <div class="hero-floating hero-floating--book" aria-hidden="true">
                    <GraduationCap class="h-7 w-7" />
                </div>
                <div class="hero-floating hero-floating--spark" aria-hidden="true">
                    <Sparkles class="h-6 w-6" />
                </div>

                <nav class="hero-utility relative mx-auto max-w-7xl px-4 pt-6 sm:px-6" aria-label="روابط سريعة">
                    <Link href="/news">الأخبار</Link>
                    <Link href="/departments">الأقسام العلمية</Link>
                    <a href="#admission">القبول والتسجيل</a>
                    <Link href="/faqs">الأسئلة الشائعة</Link>
                </nav>

                <div
                    class="relative mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 md:py-14 lg:grid-cols-[1fr_0.95fr] lg:items-center lg:py-16">
                    <div class="hero-copy">
                        <span class="hero-badge">
                            <Landmark class="h-4 w-4" />
                            {{ instituteName }}
                        </span>

                        <h1 id="home-hero-title"
                            class="mt-5 max-w-4xl text-4xl font-black leading-tight tracking-tight md:text-6xl">
                            {{ props.settings.hero_title || 'بوابة المعهد للأخبار والخدمات الأكاديمية' }}
                        </h1>

                        <p class="hero-subtitle">
                            تابع آخر الأخبار والفعاليات، استكشف الأقسام العلمية، وانتقل بسهولة إلى خدمات الطالب
                            والموظف وعضو هيئة التدريس.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <component :is="isExternalUrl(registrationHref) ? 'a' : Link" :href="registrationHref"
                                :target="linkTarget(registrationHref)"
                                :rel="isExternalUrl(registrationHref) ? 'noopener noreferrer' : undefined"
                                class="hero-button hero-button--primary">
                                {{ props.registration.is_open ? 'ابدأ التسجيل الآن' : 'عرض شروط القبول' }}
                                <ArrowLeft class="h-5 w-5" />
                            </component>

                            <component :is="isExternalUrl(props.portalLoginUrl) ? 'a' : Link"
                                :href="props.portalLoginUrl" :target="linkTarget(props.portalLoginUrl)"
                                :rel="isExternalUrl(props.portalLoginUrl) ? 'noopener noreferrer' : undefined"
                                class="hero-button hero-button--secondary">
                                دخول البوابة
                                <LogIn class="h-5 w-5" />
                            </component>
                        </div>

                        <div class="mt-8 grid max-w-2xl gap-3 sm:grid-cols-3">
                            <div v-for="stat in heroStats" :key="stat.label" class="hero-mini-stat">
                                <component :is="stat.icon" class="h-5 w-5 text-orange-200" />
                                <p class="hero-counter">{{ formatNumber(stat.value) }}</p>
                                <p class="text-xs font-bold text-blue-50/80">{{ stat.label }}</p>
                            </div>
                        </div>

                        <div class="edu-quick-links" aria-label="خدمات المعهد">
                            <a href="#news">آخر الأخبار</a>
                            <a href="#admission">متطلبات القبول</a>
                            <a href="#contact">تواصل معنا</a>
                        </div>
                    </div>

                    <aside class="hero-visual" aria-label="مختصر أخبار وحالة التسجيل">
                        <div class="hero-image-card">
                            <img :src="heroImage" :alt="heroNews?.title || 'أخبار المعهد'"
                                class="h-full w-full object-cover" />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-blue-950/75 via-blue-950/10 to-transparent" />

                            <div
                                class="absolute bottom-4 right-4 left-4 rounded-3xl border border-white/15 bg-white/15 p-4 backdrop-blur-xl">
                                <p class="flex items-center gap-2 text-xs font-extrabold text-orange-200">
                                    <Clock3 class="h-4 w-4" />
                                    مختصر اليوم
                                </p>
                                <h2 class="mt-1 line-clamp-2 text-lg font-black">
                                    {{ heroNews?.title || 'أخبار المعهد تظهر هنا' }}
                                </h2>
                                <Link v-if="heroNews?.slug" :href="newsHref(heroNews)"
                                    class="mt-3 inline-flex items-center gap-2 text-sm font-extrabold text-white/90 hover:text-orange-200">
                                    قراءة الخبر
                                    <ChevronLeft class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>

                        <div class="hero-status-card">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-extrabold text-orange-200">حالة التسجيل</p>
                                    <p class="mt-2 text-3xl font-black">
                                        {{ props.registration.is_open ? 'مفتوح حالياً' : 'مغلق حالياً' }}
                                    </p>
                                </div>
                                <span class="hero-pulse"
                                    :class="props.registration.is_open ? 'bg-emerald-400' : 'bg-slate-300'"
                                    aria-hidden="true" />
                            </div>

                            <p class="mt-4 text-sm leading-7 text-blue-50/85">
                                <span v-if="props.registration.semester">
                                    الفصل: {{ props.registration.semester.code }} · نهاية التسجيل:
                                    {{ formatDate(props.registration.semester.registration_end) }}
                                </span>
                                <span v-else>
                                    يتم تحديث بيانات التسجيل من لوحة التحكم.
                                </span>
                            </p>
                        </div>
                    </aside>
                </div>
            </section>

            <!-- News -->
            <section id="news" class="mx-auto -mt-8 max-w-7xl px-4 pb-10">
                <div class="section-card p-4 md:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow">شريط الأخبار</p>
                            <h2 class="section-title">آخر المستجدات</h2>
                        </div>
                        <Link href="/news" class="section-link">
                            عرض كل الأخبار
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <article v-for="post in featuredNews" :key="post.id"
                            class="group overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <Link :href="newsHref(post)" class="block">
                                <div class="relative h-48 overflow-hidden">
                                    <img :src="post.featured_image_url || defaultHeroImage" :alt="post.title"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                                    <span
                                        class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-black text-blue-950 shadow">
                                        خبر
                                    </span>
                                </div>

                                <div class="p-5">
                                    <h3
                                        class="line-clamp-2 text-lg font-black leading-7 text-blue-950 group-hover:text-orange-600">
                                        {{ post.title }}
                                    </h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-7 text-slate-600">
                                        {{ post.summary || 'تفاصيل الخبر ستظهر هنا بعد إضافتها من لوحة الإدارة.' }}
                                    </p>
                                    <span
                                        class="mt-4 inline-flex items-center gap-2 text-sm font-black text-orange-600">
                                        قراءة المزيد
                                        <ChevronLeft class="h-4 w-4 transition group-hover:-translate-x-1" />
                                    </span>
                                </div>
                            </Link>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Statistics -->
            <section class="relative overflow-hidden bg-blue-950 py-16 text-white" aria-label="إحصائيات المعهد">
                <img src="/assets/img/website-news-hero.svg" alt=""
                    class="absolute inset-0 h-full w-full object-cover opacity-15" />
                <div class="absolute inset-0 bg-blue-950/85" />

                <div class="relative mx-auto max-w-7xl px-4">
                    <div class="mx-auto mb-10 max-w-2xl text-center">
                        <p class="eyebrow text-orange-300">أرقام تهمك</p>
                        <h2 class="mt-2 text-3xl font-black md:text-4xl">المعهد في لمحة</h2>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="statistic in statisticCards" :key="statistic.label"
                            class="rounded-3xl border border-white/10 bg-white/10 p-6 text-center backdrop-blur transition hover:-translate-y-1 hover:bg-white/15">
                            <component :is="statistic.icon" class="mx-auto h-9 w-9 text-orange-300" />
                            <p class="mt-5 text-5xl font-black text-orange-300">{{ formatNumber(statistic.value) }}</p>
                            <p class="mt-3 text-lg font-black">{{ statistic.label }}</p>
                            <p class="mt-2 text-sm leading-6 text-blue-50/70">{{ statistic.helper }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Banners -->
            <section v-if="props.banners.length" class="mx-auto max-w-7xl px-4 py-12" aria-label="أبرز الروابط">
                <div class="grid gap-4 md:grid-cols-2">
                    <article v-for="banner in props.banners" :key="banner.id"
                        class="group relative min-h-64 overflow-hidden rounded-[2rem] bg-blue-950 p-6 text-white shadow-lg">
                        <img v-if="banner.image_url" :src="banner.image_url" :alt="banner.title"
                            class="absolute inset-0 h-full w-full object-cover opacity-35 transition duration-500 group-hover:scale-105" />
                        <div
                            class="absolute inset-0 bg-gradient-to-l from-blue-950/90 via-blue-950/55 to-transparent" />

                        <div class="relative flex h-full flex-col justify-end">
                            <h2 class="text-2xl font-black md:text-3xl">{{ banner.title }}</h2>
                            <p v-if="banner.subtitle" class="mt-3 max-w-xl leading-8 text-blue-50/85">{{ banner.subtitle
                            }}</p>

                            <component :is="isExternalUrl(banner.link_url) ? 'a' : Link" v-if="banner.link_url"
                                :href="banner.link_url" :target="linkTarget(banner.link_url)"
                                :rel="isExternalUrl(banner.link_url) ? 'noopener noreferrer' : undefined"
                                class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-black text-blue-950 transition hover:bg-orange-400 hover:text-white">
                                {{ banner.link_label || 'المزيد' }}
                                <ExternalLink v-if="isExternalUrl(banner.link_url)" class="h-4 w-4" />
                                <ChevronLeft v-else class="h-4 w-4" />
                            </component>
                        </div>
                    </article>
                </div>
            </section>

            <!-- Departments -->
            <section class="mx-auto max-w-7xl px-4 py-12">
                <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="eyebrow">البرامج الأكاديمية</p>
                        <h2 class="section-title">الأقسام والتخصصات</h2>
                    </div>
                    <Link href="/departments" class="section-link">
                        استكشف البرامج
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </div>

                <div v-if="props.departments.length" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <article v-for="department in props.departments" :key="department.id"
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-orange-200 hover:shadow-xl">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-950">
                            <BookOpenCheck class="h-6 w-6" />
                        </div>
                        <h3 class="mt-5 text-xl font-black text-blue-950">{{ department.name }}</h3>
                        <p class="mt-3 min-h-20 text-sm leading-7 text-slate-600">
                            {{ department.description || 'قسم علمي معتمد ضمن برامج المعهد.' }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <span v-for="specialization in department.specializations" :key="specialization.id"
                                class="rounded-full bg-orange-50 px-3 py-1 text-xs font-black text-orange-700">
                                {{ specialization.name }}
                            </span>
                            <span v-if="!department.specializations.length"
                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                                لا توجد تخصصات منشورة حالياً
                            </span>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-state">
                    <Landmark class="h-10 w-10 text-orange-500" />
                    <h3 class="mt-3 text-xl font-black text-blue-950">لم تتم إضافة أقسام بعد</h3>
                    <p class="mt-2 text-sm text-slate-500">ستظهر الأقسام والتخصصات هنا عند إضافتها من لوحة الإدارة.</p>
                </div>
            </section>

            <!-- Announcements and FAQ -->
            <section class="mx-auto grid max-w-7xl gap-6 px-4 py-12 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="section-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="eyebrow">تنبيهات مهمة</p>
                            <h2 class="section-title text-2xl">الإعلانات</h2>
                        </div>
                        <Megaphone class="h-8 w-8 text-orange-500" />
                    </div>

                    <div v-if="topAnnouncements.length" class="mt-6 space-y-3">
                        <article v-for="post in topAnnouncements" :key="post.id"
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-orange-200 hover:bg-orange-50/40">
                            <h3 class="font-black text-blue-950">{{ post.title }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                {{ post.summary || 'تفاصيل الإعلان ستظهر هنا.' }}
                            </p>
                        </article>
                    </div>

                    <p v-else class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">لا توجد إعلانات منشورة
                        حالياً.</p>
                </div>

                <div id="faq" class="section-card p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="eyebrow">مساعدة سريعة</p>
                            <h2 class="section-title text-2xl">الأسئلة الشائعة</h2>
                        </div>
                        <Link href="/faqs" class="section-link">عرض الكل</Link>
                    </div>

                    <div v-if="topFaqs.length" class="mt-6 space-y-3">
                        <details v-for="faq in topFaqs" :key="faq.id"
                            class="group rounded-2xl border border-slate-100 bg-slate-50 p-4 open:bg-white open:shadow-sm">
                            <summary
                                class="flex cursor-pointer list-none items-center justify-between gap-3 font-black text-blue-950">
                                <span class="flex items-center gap-3">
                                    <CircleHelp class="h-5 w-5 text-orange-500" />
                                    {{ faq.question }}
                                </span>
                                <ChevronLeft class="h-4 w-4 transition group-open:-rotate-90" />
                            </summary>
                            <p class="mt-3 border-t border-slate-100 pt-3 text-sm leading-8 text-slate-600">{{
                                faq.answer }}</p>
                        </details>
                    </div>

                    <p v-else class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">
                        يمكن إضافة الأسئلة الشائعة من لوحة إدارة الموقع.
                    </p>
                </div>
            </section>

            <!-- Admission -->
            <section id="admission" class="mx-auto max-w-7xl px-4 py-12">
                <div class="relative overflow-hidden rounded-[2rem] bg-blue-950 p-6 text-white shadow-xl md:p-8">
                    <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-orange-500/20 blur-3xl" />
                    <div class="absolute -bottom-24 right-10 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl" />

                    <div class="relative grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                        <div>
                            <p class="text-sm font-black text-orange-300">القبول والتسجيل</p>
                            <h2 class="mt-2 text-3xl font-black md:text-4xl">متطلبات القبول</h2>
                            <p class="mt-4 leading-8 text-blue-50/80">
                                جهّز المستندات المطلوبة قبل بدء التسجيل لتسهيل عملية القبول وتقليل وقت المراجعة.
                            </p>

                            <component :is="isExternalUrl(registrationHref) ? 'a' : Link" :href="registrationHref"
                                :target="linkTarget(registrationHref)"
                                :rel="isExternalUrl(registrationHref) ? 'noopener noreferrer' : undefined"
                                class="mt-6 inline-flex items-center gap-2 rounded-full bg-orange-500 px-6 py-3 font-black text-white transition hover:bg-orange-400">
                                {{ props.registration.is_open ? 'التسجيل متاح الآن' : 'متابعة صفحة التسجيل' }}
                                <ArrowLeft class="h-5 w-5" />
                            </component>
                        </div>

                        <ul v-if="props.registration.requirements.length" class="relative grid gap-3 md:grid-cols-2">
                            <li v-for="requirement in props.registration.requirements" :key="requirement"
                                class="flex gap-3 rounded-2xl border border-white/10 bg-white/10 p-4 leading-7 backdrop-blur">
                                <CheckCircle2 class="mt-1 h-5 w-5 shrink-0 text-orange-300" />
                                <span>{{ requirement }}</span>
                            </li>
                        </ul>

                        <div v-else class="relative rounded-2xl border border-white/10 bg-white/10 p-5 text-blue-50/85">
                            لم يتم نشر المتطلبات بعد.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact -->
            <section id="contact" class="mx-auto max-w-7xl px-4 py-12">
                <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                    <aside class="section-card p-6">
                        <p class="eyebrow">تواصل معنا</p>
                        <h2 class="section-title">نحن هنا لمساعدتك</h2>
                        <p class="mt-4 leading-8 text-slate-600">
                            ارسل استفسارك وسيتم التواصل معك من الإدارة المختصة في أقرب وقت.
                        </p>

                        <div class="mt-6 space-y-3">
                            <div v-for="item in contactInfo" :key="item.label"
                                class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4">
                                <span
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-950 text-white">
                                    <component :is="item.icon" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-xs font-black text-slate-500">{{ item.label }}</p>
                                    <p class="mt-1 font-black text-blue-950">{{ item.value }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div class="section-card p-6">
                        <form class="grid gap-4" @submit.prevent="submit">
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="field">
                                    <span>الاسم الكامل</span>
                                    <input v-model="form.name" required placeholder="اكتب الاسم" />
                                    <small v-if="form.errors.name">{{ form.errors.name }}</small>
                                </label>

                                <label class="field">
                                    <span>البريد الإلكتروني</span>
                                    <input v-model="form.email" type="email" placeholder="example@email.com" />
                                    <small v-if="form.errors.email">{{ form.errors.email }}</small>
                                </label>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="field">
                                    <span>رقم الهاتف</span>
                                    <input v-model="form.phone" placeholder="09xxxxxxxx" />
                                    <small v-if="form.errors.phone">{{ form.errors.phone }}</small>
                                </label>

                                <label class="field">
                                    <span>الموضوع</span>
                                    <input v-model="form.subject" placeholder="موضوع الرسالة" />
                                    <small v-if="form.errors.subject">{{ form.errors.subject }}</small>
                                </label>
                            </div>

                            <label class="field">
                                <span>الرسالة</span>
                                <textarea v-model="form.message" required placeholder="اكتب رسالتك هنا..." />
                                <small v-if="form.errors.message">{{ form.errors.message }}</small>
                            </label>

                            <button class="submit-button" :disabled="form.processing" type="submit">
                                <span>{{ form.processing ? 'جاري الإرسال...' : 'إرسال الرسالة' }}</span>
                                <ArrowLeft class="h-5 w-5" />
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
