<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

// تعريف النموذج (Form) باستخدام Inertia
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// متغير محلي للتحكم في إظهار وإخفاء كلمة المرور
const showPassword = ref(false);

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="تسجيل الدخول | المعهد العالي للعلوم والتقنية" />

    <!-- خلفية الصفحة الناعمة (Light Grayish-Blue) -->
    <div
        class="flex min-h-screen flex-col items-center justify-center gap-6 bg-[#f4f5f9] p-6 md:p-10"
        dir="rtl"
    >
        <!-- بطاقة تسجيل الدخول الأنيقة -->
        <div
            class="relative z-10 w-full max-w-md rounded-2xl border border-gray-100 bg-white px-8 py-10 shadow-xl"
        >
            <!-- نقاط الديكور الخلفية (تطابق الشعار الهندسي في الصورة) -->
            <!-- أعلى اليمين -->
            <div
                class="pointer-events-none absolute -top-12 -right-12 grid grid-cols-6 gap-2.5 opacity-40 sm:grid"
            >
                <div
                    v-for="i in 36"
                    :key="'tr-' + i"
                    class="h-1.5 w-1.5 rounded-full bg-[#818cf8]"
                ></div>
            </div>
            <!-- أسفل اليسار -->
            <div
                class="pointer-events-none absolute -bottom-12 -left-12 grid grid-cols-6 gap-2.5 opacity-40 sm:grid"
            >
                <div
                    v-for="i in 36"
                    :key="'bl-' + i"
                    class="h-1.5 w-1.5 rounded-full bg-[#818cf8]"
                ></div>
            </div>

            <!-- الترويسة والشعار اللطيف -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-2">
                    <!-- شعار أكاديمي مبسط -->
                    <div
                        class="flex h-8 w-8 items-center justify-center text-blue-600"
                    >
                        <svg
                            class="h-7 w-7 text-[#5850ec]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5z"
                            ></path>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
                            ></path>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-[#4a5568]">
                        المعهد العالي للعلوم والتقنية العجيلات
                    </h1>
                </div>

                <h2 class="mt-6 text-2xl font-bold text-[#2d3748]">
                    مرحباً بك من جديد! 👋
                </h2>
                <p class="mt-2 text-sm text-[#718096]">
                    يرجى تسجيل الدخول للمتابعة
                </p>
            </div>

            <!-- نموذج تسجيل الدخول الفعلي -->
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <!-- عرض رسائل الخطأ برمجياً بشكل متوافق مع الاستايل الجديد -->
                <div
                    v-if="form.errors.email"
                    class="rounded-lg border-r-4 border-red-500 bg-red-50 p-3"
                >
                    <p class="text-xs leading-relaxed font-bold text-red-700">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-5">
                    <!-- حقل البريد الإلكتروني أو اسم المستخدم -->
                    <div>
                        <label
                            for="email"
                            class="mb-2 block text-xs font-semibold text-[#a0aec0]"
                            >اسم المستخدم / البريد الإلكتروني</label
                        >
                        <input
                            id="email"
                            v-model="form.email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="block w-full appearance-none rounded-lg border border-[#e2e8f0] px-4 py-3 text-gray-800 placeholder-[#cbd5e0] transition duration-150 focus:border-[#5850ec] focus:ring-4 focus:ring-indigo-100 focus:outline-none sm:text-sm"
                            placeholder="يرجى ادخال اسم المستخدم"
                        />
                    </div>

                    <!-- حقل كلمة المرور مع أيقونة الإظهار التفاعلية على اليسار -->
                    <div>
                        <label
                            for="password"
                            class="mb-2 block text-xs font-semibold text-[#a0aec0]"
                            >كلمة المرور</label
                        >
                        <div class="relative rounded-lg shadow-sm">
                            <input
                                id="password"
                                v-model="form.password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                class="block w-full appearance-none rounded-lg border border-[#e2e8f0] px-4 py-3 text-gray-800 placeholder-[#cbd5e0] transition duration-150 focus:border-[#5850ec] focus:ring-4 focus:ring-indigo-100 focus:outline-none sm:text-sm"
                                placeholder="••••••••••••"
                            />

                            <!-- أيقونة التبديل التفاعلية (Show/Hide) في نفس مكان الصورة -->
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 hover:text-[#5850ec]"
                            >
                                <component
                                    :is="showPassword ? EyeOff : Eye"
                                    class="h-5 w-5 text-gray-400"
                                />
                            </button>
                        </div>
                        <div
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.password }}
                        </div>
                    </div>
                </div>

                <!-- تذكرني -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            v-model="form.remember"
                            name="remember-me"
                            type="checkbox"
                            class="h-4 w-4 cursor-pointer rounded border-[#cbd5e0] text-[#5850ec] transition duration-150 focus:ring-[#5850ec]"
                        />
                        <label
                            for="remember-me"
                            class="mr-2 block cursor-pointer text-sm text-[#718096]"
                        >
                            تذكرني
                        </label>
                    </div>
                </div>

                <!-- زر تسجيل الدخول المطابق للون الصورة تماماً -->
                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full justify-center rounded-lg border border-transparent bg-[#5850ec] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-100 transition duration-150 ease-in-out hover:bg-[#4f46e5] focus:ring-4 focus:ring-indigo-100 focus:outline-none"
                        :class="{
                            'cursor-not-allowed opacity-75': form.processing,
                        }"
                    >
                        <span v-if="form.processing">جاري التحقق...</span>
                        <span v-else>تسجيل الدخول</span>
                    </button>
                </div>

                <!-- رابط المساعدة بالأسفل -->
                <div class="mt-4 text-center">
                    <a
                        href="#"
                        class="text-sm font-semibold text-[#5850ec] transition duration-150 hover:text-[#4f46e5]"
                    >
                        نسيت كلمة المرور؟
                    </a>
                </div>
            </form>
        </div>

        <!-- حقوق النشر في الأسفل -->
        <div
            class="absolute bottom-4 w-full text-center text-xs font-medium text-gray-400"
        >
            &copy; {{ new Date().getFullYear() }} المعهد العالي للعلوم والتقنية
            العجيلات. جميع الحقوق محفوظة.
        </div>
    </div>
</template>
