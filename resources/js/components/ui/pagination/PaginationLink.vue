<!-- resources/js/Components/ui/pagination/PaginationLink.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'
import { buttonVariants } from '@/components/ui/button'

interface Props {
    href?: string
    isActive?: boolean
    class?: HTMLAttributes['class']
    preserveScroll?: boolean
    // any other Inertia link options can be added
}

const props = withDefaults(defineProps<Props>(), {
    href: undefined,
    isActive: false,
    preserveScroll: false,
})

const component = computed(() => (props.href ? Link : 'span'))
</script>

<template>
    <component :is="component" :href="href" :preserve-scroll="preserveScroll" :class="cn(
        buttonVariants({ variant: isActive ? 'default' : 'ghost', size: 'sm' }),
        'h-9 w-9 p-0',
        isActive ? 'bg-primary text-primary-foreground shadow pointer-events-none' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
        props.class
    )" v-bind="$attrs">
        <slot />
    </component>
</template>
