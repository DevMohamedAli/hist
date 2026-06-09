<!-- resources/js/Components/ui/pagination/PaginationPrevious.vue -->
<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next'
import PaginationLink from './PaginationLink.vue'
import { cn } from '@/lib/utils'
import type { HTMLAttributes } from 'vue'

interface Props {
    href?: string
    class?: HTMLAttributes['class']
    preserveScroll?: boolean
}

const props = defineProps<Props>()
</script>

<template>
    <PaginationLink :href="href" :preserve-scroll="preserveScroll" :class="cn('gap-1 pr-2.5', props.class)">
        <ChevronRight class="h-4 w-4" />
        <span>السابق</span>
    </PaginationLink>
</template>
