<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CorrespondenceStatusBadge from '@/components/correspondence/CorrespondenceStatusBadge.vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

defineOptions({ layout: [] });

const props = defineProps<{
    correspondences: {
        data: Array<{
            id: number;
            reference_number?: string;
            subject: string;
            status: string;
        }>;
    };
    filters: {
        search?: string;
        status?: string;
        priority?: string;
        classification?: string;
    };
}>();

const applyFilters = (event: Event) => {
    const data = new FormData(event.target as HTMLFormElement);

    router.get(
        '/correspondence/sent',
        {
            search: data.get('search') || undefined,
            status: data.get('status') || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head title="Sent Correspondence" />
    <AuthenticatedLayout>
        <main class="min-h-screen bg-gray-50 p-6" dir="rtl">
            <div class="mx-auto max-w-6xl rounded-lg bg-white p-6 shadow">
                <h1 class="text-2xl font-bold">Sent correspondence</h1>

                <form
                    class="mt-5 grid gap-3 md:grid-cols-[1fr_220px_auto]"
                    @submit.prevent="applyFilters"
                >
                    <input
                        name="search"
                        :default-value="props.filters.search"
                        placeholder="Search reference or subject"
                        class="rounded-md border px-3 py-2"
                    />
                    <select
                        name="status"
                        :default-value="props.filters.status"
                        class="rounded-md border px-3 py-2"
                    >
                        <option value="">All statuses</option>
                        <option value="draft">draft</option>
                        <option value="submitted">submitted</option>
                        <option value="pending_approval">
                            pending_approval
                        </option>
                        <option value="dispatched">dispatched</option>
                        <option value="completed">completed</option>
                        <option value="archived">archived</option>
                    </select>
                    <button
                        class="rounded-md bg-slate-900 px-4 py-2 font-bold text-white"
                    >
                        Filter
                    </button>
                </form>

                <table class="mt-6 w-full text-start text-sm">
                    <tbody>
                        <tr
                            v-for="item in correspondences.data"
                            :key="item.id"
                            class="border-t"
                        >
                            <td class="py-3">
                                {{ item.reference_number || 'Draft' }}
                            </td>
                            <td class="py-3">
                                <Link
                                    :href="`/correspondence/${item.id}`"
                                    class="font-semibold text-blue-900"
                                >
                                    {{ item.subject }}
                                </Link>
                            </td>
                            <td class="py-3">
                                <CorrespondenceStatusBadge
                                    :status="item.status"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
