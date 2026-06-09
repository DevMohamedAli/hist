<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, LogIn } from 'lucide-vue-next';

const form = useForm({
    registration_number: '',
    password: '',
});

const submit = () => form.post('/student/login');
</script>

<template>
    <Head title="دخول الطالب" />
    <main class="min-h-screen bg-gray-50 p-4 font-['Cairo']" dir="rtl">
        <section
            class="mx-auto mt-16 max-w-md rounded-xl border-t-4 border-blue-800 bg-white p-6 shadow-md"
        >
            <div class="mb-6 text-center">
                <GraduationCap class="mx-auto h-12 w-12 text-orange-500" />
                <h1 class="mt-3 text-2xl font-extrabold text-blue-800">
                    بوابة الطالب
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    الدخول برقم القيد وكلمة المرور.
                </p>
            </div>
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label
                        class="text-sm font-bold text-gray-700"
                        for="registration_number"
                        >رقم القيد</label
                    >
                    <input
                        id="registration_number"
                        v-model="form.registration_number"
                        required
                        type="text"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                    />
                    <p
                        v-if="form.errors.registration_number"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.registration_number }}
                    </p>
                </div>
                <div>
                    <label
                        class="text-sm font-bold text-gray-700"
                        for="password"
                        >كلمة المرور</label
                    >
                    <input
                        id="password"
                        v-model="form.password"
                        required
                        type="password"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-blue-800 focus:ring-2 focus:ring-blue-800/20 focus:outline-none"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-3 font-bold text-white hover:bg-orange-600 disabled:opacity-60"
                >
                    <LogIn class="h-5 w-5" />
                    دخول
                </button>
            </form>
            <Link
                href="/"
                class="mt-5 block text-center text-sm font-bold text-blue-800 hover:text-orange-500"
                >العودة للرئيسية</Link
            >
        </section>
    </main>
</template>
