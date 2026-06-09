<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { FileSpreadsheet, FileText, UploadCloud } from 'lucide-vue-next'
</script>

<template>
    <section class="overflow-hidden rounded-lg border border-blue-100 bg-white shadow-sm">
        <div class="border-t-4 border-blue-800 p-5 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-orange-600">وحدة الاستيراد</p>
                    <h1 class="mt-1 text-2xl font-extrabold text-blue-900 sm:text-3xl">
                        استيراد البيانات
                    </h1>
                    <p class="mt-2 max-w-4xl text-sm leading-7 text-gray-600">
                        ارفع ملفات CSV أو Excel أو PDF، راجع البيانات، ثم نفذ الاستيراد بثقة.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link
                        href="/imports/courses/pdf"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-orange-600"
                    >
                        <FileText class="h-5 w-5" />
                        استيراد مقررات PDF
                    </Link>

                    <div class="flex items-center gap-3 rounded-lg bg-blue-50 px-4 py-3 text-blue-900">
                        <FileSpreadsheet class="h-7 w-7" />
                        <div>
                            <p class="text-xs font-bold text-gray-500">أنواع الملفات</p>
                            <p class="text-sm font-extrabold">CSV / XLS / XLSX / PDF</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2 rounded-md border border-orange-100 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-800">
                <UploadCloud class="h-5 w-5 shrink-0" />
                <span>ابدأ باختيار نوع البيانات، ثم ارفع الملف. تظهر المعاينة والنتائج داخل صفحة الاستيراد.</span>
            </div>
        </div>
    </section>
</template>
