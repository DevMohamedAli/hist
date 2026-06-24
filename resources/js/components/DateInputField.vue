<script setup lang="ts">
import ArabicDatePicker from '@/components/ArabicDatePicker.vue';

withDefaults(
    defineProps<{
        modelValue: string;
        id: string;
        label: string;
        error?: string;
        hint?: string;
        placeholder?: string;
        required?: boolean;
        disabled?: boolean;
        min?: string;
        max?: string;
    }>(),
    {
        error: '',
        hint: '',
        placeholder: 'اختر التاريخ',
        required: false,
        disabled: false,
        min: '',
        max: '',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div>
        <label :for="id" class="block text-sm font-bold text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-600">*</span>
        </label>

        <ArabicDatePicker
            :id="id"
            class="mt-2"
            :model-value="modelValue"
            :placeholder="placeholder"
            :required="required"
            :disabled="disabled"
            :min="min"
            :max="max"
            @update:model-value="emit('update:modelValue', $event)"
        />

        <p v-if="error" class="mt-2 text-sm font-bold text-red-600">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-2 text-xs leading-5 text-gray-500">
            {{ hint }}
        </p>
    </div>
</template>
