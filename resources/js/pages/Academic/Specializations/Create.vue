<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Save, Library, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

// تعريف الأقسام المطلوبة لربط التخصص بها
interface Department {
    id: number;
    name: string;
}

interface Props {
    departments: Department[];
}

const props = defineProps<Props>();

// بناء النموذج ليتوافق مع دالة store تماماً
const form = useForm({
    department_id: '',
    name: '',
    code: '',
    description: '',
    semesters_count: '',
});

const submit = () => {
    form.post('/academic/specializations', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="إضافة تخصص جديد" />

    <!-- تطبيق خط Cairo ودعم RTL -->
    <div
        class="font-cairo min-h-screen bg-[#f4f5f9] p-4 transition-colors duration-200 sm:p-6 lg:p-8 dark:bg-slate-950"
        dir="rtl"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <!-- زر العودة والترويسة -->
            <div>
                <Link
                    href="/academic/specializations"
                    class="mb-4 inline-flex items-center text-sm font-semibold text-slate-500 transition-colors hover:text-blue-800 dark:text-slate-400 dark:hover:text-blue-400"
                >
                    <ChevronRight class="me-1 h-4 w-4" />
                    العودة لقائمة التخصصات
                </Link>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div
                                class="h-8 w-1 rounded-full bg-orange-500"
                            ></div>
                            <p
                                class="text-sm font-bold text-orange-600 dark:text-orange-400"
                            >
                                الشؤون الأكاديمية
                            </p>
                        </div>
                        <h1
                            class="mt-2 bg-linear-to-l from-blue-900 to-blue-700 bg-clip-text text-3xl font-extrabold text-transparent dark:from-blue-400 dark:to-blue-300"
                        >
                            إضافة تخصص / شعبة جديدة
                        </h1>
                        <p
                            class="mt-2 text-sm font-medium text-slate-500 dark:text-slate-400"
                        >
                            أدخل بيانات التخصص واربطه بالقسم العلمي المناسب.
                        </p>
                    </div>
                    <div
                        class="hidden h-14 w-14 items-center justify-center rounded-full border border-blue-200 bg-blue-100 text-blue-800 shadow-sm sm:flex dark:border-blue-800/50 dark:bg-blue-900/30 dark:text-blue-400"
                    >
                        <Library class="h-7 w-7" />
                    </div>
                </div>
            </div>

            <!-- بطاقة النموذج (Form Card) -->
            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg transition-all dark:border-slate-800 dark:bg-slate-900"
            >
                <form @submit.prevent="submit" class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- اختيار القسم (Department) -->
                        <div class="space-y-2">
                            <Label
                                for="department"
                                class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"
                            >
                                القسم العلمي <span class="text-red-500">*</span>
                            </Label>
                            <Select v-model="form.department_id">
                                <SelectTrigger
                                    class="h-11 border-slate-200 bg-slate-50 font-medium focus:ring-orange-500/30 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                >
                                    <SelectValue
                                        placeholder="اختر القسم الذي يتبعه التخصص..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="font-cairo dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <SelectItem
                                        v-for="dept in props.departments"
                                        :key="dept.id"
                                        :value="String(dept.id)"
                                    >
                                        {{ dept.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.department_id"
                                class="text-sm font-bold text-red-500 dark:text-red-400"
                            >
                                {{ form.errors.department_id }}
                            </p>
                        </div>

                        <!-- اسم التخصص -->
                        <div class="space-y-2">
                            <Label
                                for="name"
                                class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"
                            >
                                اسم التخصص / الشعبة
                                <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="مثال: التقنية الطبية - صيدلة"
                                class="h-11 border-slate-200 bg-slate-50 font-medium focus:border-blue-600 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm font-bold text-red-500 dark:text-red-400"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- كود التخصص -->
                        <div class="space-y-2 md:col-span-2">
                            <Label
                                for="code"
                                class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"
                            >
                                الرمز الأكاديمي (اختياري)
                            </Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                type="text"
                                placeholder="مثال: PHARM-01"
                                class="h-11 border-slate-200 bg-slate-50 text-start font-mono font-medium focus:border-blue-600 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                dir="ltr"
                            />
                            <p
                                v-if="form.errors.code"
                                class="text-sm font-bold text-red-500 dark:text-red-400"
                            >
                                {{ form.errors.code }}
                            </p>
                            <p
                                class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                يستخدم لترميز التخصص في الجداول والتقارير.
                            </p>
                        </div>

                        <!-- عدد السمسترات المطلوبة للتخرج -->
                        <div class="space-y-2 md:col-span-2">
                            <Label
                                for="semesters_count"
                                class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"
                            >
                                عدد السمسترات المطلوبة للتخرج
                                <span class="text-red-500">*</span>
                            </Label>
                            <Input
                                id="semesters_count"
                                v-model="form.semesters_count"
                                type="number"
                                min="1"
                                max="12"
                                class="h-11 border-slate-200 bg-slate-50 font-medium focus:border-blue-600 focus:ring-blue-600/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                required
                            />
                            <p
                                v-if="form.errors.semesters_count"
                                class="text-sm font-bold text-red-500 dark:text-red-400"
                            >
                                {{ form.errors.semesters_count }}
                            </p>
                        </div>

                        <!-- الوصف -->
                        <div class="space-y-2 md:col-span-2">
                            <Label
                                for="description"
                                class="flex items-center gap-1 font-bold text-slate-700 dark:text-slate-200"
                            >
                                وصف التخصص (اختياري)
                            </Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="اكتب نبذة مختصرة عن التخصص وأهدافه..."
                                class="flex w-full resize-none rounded-md border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-medium transition-colors placeholder:text-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-500"
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="text-sm font-bold text-red-500 dark:text-red-400"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- أزرار الحفظ -->
                        <div
                            class="mt-2 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row md:col-span-2 dark:border-slate-800"
                        >
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 gap-2 bg-orange-500 text-base font-bold text-white shadow-md transition-all hover:bg-orange-600 hover:shadow-lg sm:w-1/3"
                            >
                                <Save class="h-5 w-5" />
                                {{
                                    form.processing
                                        ? 'جاري الحفظ...'
                                        : 'حفظ التخصص'
                                }}
                            </Button>

                            <Button
                                type="button"
                                variant="outline"
                                @click="form.reset()"
                                class="h-12 border-slate-300 text-base font-bold text-slate-700 hover:bg-slate-100 sm:w-1/4 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                :disabled="form.processing"
                            >
                                إفراغ الحقول
                            </Button>
                        </div>
                    </div>
                </form>
            </div>

            <div
                class="text-center text-sm font-semibold text-slate-400 dark:text-slate-500"
            >
                جميع الحقول المزودة بنجمة (<span class="text-red-500">*</span>)
                إلزامية.
            </div>
        </div>
    </div>
</template>
