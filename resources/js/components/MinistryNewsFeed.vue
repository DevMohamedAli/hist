<script setup lang="ts">
import {
    Building2,
    CalendarDays,
    ExternalLink,
    Newspaper,
} from 'lucide-vue-next';

export interface MinistryNewsItem {
    title: string;
    link: string;
    author?: string | null;
    published_at?: string | null;
    image_url?: string | null;
}

const props = defineProps<{
    items?: MinistryNewsItem[];
}>();

const latestItem = () => props.items?.[0] ?? null;
</script>

<template>
    <section class="overflow-hidden rounded-xl bg-white shadow-sm" dir="rtl">
        <div class="border-b border-gray-100 p-4">
            <div
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-800"
                    >
                        <Newspaper class="h-6 w-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-orange-500">
                            آخر الأخبار
                        </p>
                        <h2
                            class="truncate text-lg font-extrabold text-blue-800"
                        >
                            أخبار وزارة التعليم التقني والفني
                        </h2>
                    </div>
                </div>

                <a
                    href="https://tve.gov.ly/Home/Index3"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex w-fit items-center gap-2 rounded-lg border border-blue-100 px-3 py-2 text-sm font-bold text-blue-800 transition hover:border-orange-200 hover:bg-orange-50"
                >
                    المصدر الرسمي
                    <ExternalLink class="h-4 w-4" />
                </a>
            </div>

            <a
                v-if="latestItem()"
                :href="latestItem()?.link"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-3 flex min-w-0 items-center gap-2 rounded-xl bg-orange-50 px-3 py-2 text-sm font-extrabold text-orange-800 transition hover:bg-orange-100"
            >
                <span
                    class="shrink-0 rounded-full bg-orange-500 px-2 py-0.5 text-[11px] text-white"
                >
                    الأحدث
                </span>
                <span class="truncate">{{ latestItem()?.title }}</span>
            </a>
        </div>

        <div v-if="items?.length" class="overflow-x-auto overscroll-x-contain">
            <div class="flex w-max gap-4 p-4">
                <article
                    v-for="(item, index) in items"
                    :key="`${item.link}-${index}`"
                    class="group w-[300px] shrink-0 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-orange-200 hover:shadow-lg sm:w-[340px]"
                >
                    <a
                        :href="item.link"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="grid grid-cols-[96px_minmax(0,1fr)] gap-3 p-3"
                    >
                        <div
                            class="h-24 overflow-hidden rounded-xl bg-gray-100"
                        >
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.title"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center text-blue-800"
                            >
                                <Newspaper class="h-7 w-7" />
                            </div>
                        </div>

                        <div class="min-w-0">
                            <h3
                                class="line-clamp-2 text-sm leading-6 font-extrabold text-gray-950 group-hover:text-orange-600"
                            >
                                {{ item.title }}
                            </h3>

                            <div
                                class="mt-2 space-y-1 text-[11px] font-bold text-gray-500"
                            >
                                <span
                                    v-if="item.author"
                                    class="flex min-w-0 items-center gap-1"
                                >
                                    <Building2
                                        class="h-3.5 w-3.5 shrink-0 text-blue-800"
                                    />
                                    <span class="truncate">{{
                                        item.author
                                    }}</span>
                                </span>
                                <span
                                    v-if="item.published_at"
                                    class="flex items-center gap-1"
                                >
                                    <CalendarDays
                                        class="h-3.5 w-3.5 text-orange-500"
                                    />
                                    {{ item.published_at }}
                                </span>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
        </div>

        <div v-else class="p-6 text-center">
            <div
                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400"
            >
                <Newspaper class="h-6 w-6" />
            </div>
            <p class="font-extrabold text-gray-900">تعذر تحميل الأخبار الآن</p>
            <p class="mt-1 text-sm font-semibold text-gray-500">
                سيتم عرض الأخبار تلقائياً عند توفر الاتصال بالموقع الرسمي.
            </p>
        </div>
    </section>
</template>
