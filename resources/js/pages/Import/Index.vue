<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import ImportWizard from '../../components/ImportWizard.vue'
import ImportPageHeader from './components/ImportPageHeader.vue'
import ImportTypeCards from './components/ImportTypeCards.vue'

interface SupportedType {
    value: string
    label: string
}

interface Props {
    defaultImportType: string
    supportedTypes: SupportedType[]
}

const props = defineProps<Props>()
const selectedImportType = ref(props.defaultImportType)
</script>

<template>
    <Head title="استيراد البيانات" />

    <main class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8" dir="rtl">
        <div class="mx-auto max-w-[1500px] space-y-6">
            <ImportPageHeader />
            <ImportTypeCards v-model="selectedImportType" :supported-types="supportedTypes" />
            <ImportWizard :key="selectedImportType" :import-type="selectedImportType" />
        </div>
    </main>
</template>
