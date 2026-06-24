<script setup lang="ts">
defineProps<{ status: string }>();
</script>

<template>
    <span
        class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-900"
    >
        {{ status }}
    </span>
</template>
