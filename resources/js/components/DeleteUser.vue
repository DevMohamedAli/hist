<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/Modules/User/Http/Controllers/ProfileController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <section class="space-y-4" dir="rtl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="flex items-center gap-2 text-sm font-black text-red-700">
                    <AlertTriangle class="h-4 w-4" />
                    منطقة حساسة
                </p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">حذف الحساب</h2>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">
                    حذف الحساب سيزيل بيانات الدخول والموارد المرتبطة به نهائيا. استخدم هذا الإجراء فقط عند التأكد.
                </p>
            </div>

            <Dialog>
                <DialogTrigger as-child>
                    <Button
                        variant="destructive"
                        data-test="delete-user-button"
                        class="h-11 gap-2 rounded-xl px-5 font-black"
                    >
                        <Trash2 class="h-4 w-4" />
                        حذف الحساب
                    </Button>
                </DialogTrigger>
                <DialogContent dir="rtl" class="sm:max-w-lg">
                    <Form
                        v-bind="ProfileController.destroy.form()"
                        reset-on-success
                        @error="() => passwordInput?.focus()"
                        :options="{
                            preserveScroll: true,
                        }"
                        class="space-y-6"
                        v-slot="{ errors, processing, reset, clearErrors }"
                    >
                        <DialogHeader class="space-y-3 text-right">
                            <DialogTitle class="text-xl font-black">
                                هل تريد حذف الحساب نهائيا؟
                            </DialogTitle>
                            <DialogDescription class="leading-7">
                                بعد حذف الحساب لن تتمكن من استعادته من الواجهة. أدخل كلمة المرور لتأكيد العملية.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password">كلمة المرور</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                ref="passwordInput"
                                placeholder="أدخل كلمة المرور"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <DialogFooter class="gap-2 sm:justify-start">
                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="processing"
                                data-test="confirm-delete-user-button"
                                class="font-black"
                            >
                                {{ processing ? 'جار الحذف...' : 'تأكيد الحذف' }}
                            </Button>

                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="
                                        () => {
                                            clearErrors();
                                            reset();
                                        }
                                    "
                                >
                                    إلغاء
                                </Button>
                            </DialogClose>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">
            هذا الإجراء نهائي ولا يمكن التراجع عنه من داخل النظام.
        </div>
    </section>
</template>
