<!-- resources/js/Components/ui/pagination/PaginationNext.vue -->
<script setup lang="ts">
import { ChevronLeft } from 'lucide-vue-next'
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
    <PaginationLink :href="href" :preserve-scroll="preserveScroll" :class="cn('gap-1 pl-2.5', props.class)">
        <span>التالي</span>
        <ChevronLeft class="h-4 w-4" />
    </PaginationLink>
</template>
