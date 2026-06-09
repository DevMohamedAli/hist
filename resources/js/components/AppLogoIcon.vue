<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/histlogopng.png"
        :class="className"
        alt="شعار المعهد العالي للعلوم والتقنية"
        v-bind="$attrs"
    />
</template>
