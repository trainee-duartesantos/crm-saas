<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const sending = ref(false);
const currentProposal = ref<any>(null);

const emailForm = ref({
    subject: 'Envio de proposta',
    body: 'Olá,\n\nSegue em anexo a proposta conforme combinado.\n\nObrigado.',
});

const openSendModal = (proposal: any) => {
    currentProposal.value = proposal;
};

const sendProposal = () => {
    sending.value = true;

    if (!currentProposal.value) return;

    router.post(
        `/proposals/${currentProposal.value.id}/send`,
        emailForm.value,
        {
            onFinish: () => {
                sending.value = false;
                currentProposal.value = null;
            },
        },
    );
};

const closeModal = () => {
    currentProposal.value = null;
    emailForm.value = {
        subject: 'Envio de proposta',
        body: 'Olá,\n\nSegue em anexo a proposta conforme combinado.\n\nObrigado.',
    };
};

defineOptions({
    layout: AppLayout,
});

const props = defineProps<{
    deal: any;
}>();

const statusColor = computed(() => {
    switch (props.deal.status) {
        case 'won':
            return 'bg-emerald-100 text-emerald-700';
        case 'lost':
            return 'bg-red-100 text-red-700';
        case 'proposal':
            return 'bg-indigo-100 text-indigo-700';
        default:
            return 'bg-gray-100 text-gray-700';
    }
});

const move = (status: string) => {
    router.post(`/deals/${props.deal.id}/move`, { status });
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-8">
        <!-- Header -->
        <div class="flex items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ deal.title }}
                </h1>

                <div class="mt-2 flex items-center gap-3 text-sm text-gray-600">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="statusColor"
                    >
                        {{ deal.status.toUpperCase() }}
                    </span>

                    <span v-if="deal.value"> € {{ deal.value }} </span>
                </div>
            </div>

            <a
                href="/deals"
                class="rounded border px-4 py-2 text-sm hover:bg-gray-50"
            >
                ← Back to pipeline
            </a>
        </div>

        <!-- Main grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Deal info -->
            <div class="space-y-6 md:col-span-2">
                <div class="rounded-lg border bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700">
                        Deal information
                    </h2>

                    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                        <div>
                            <div class="text-gray-500">Status</div>
                            <div class="font-medium">{{ deal.status }}</div>
                        </div>

                        <div>
                            <div class="text-gray-500">Value</div>
                            <div class="font-medium">
                                {{ deal.value ? `€ ${deal.value}` : '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">Last activity</div>
                            <div class="font-medium">
                                {{ deal.last_activity_at ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proposals -->
                <div class="rounded-lg border bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700">
                        Propostas
                    </h2>

                    <div
                        v-if="deal.proposals.length === 0"
                        class="text-sm text-gray-500"
                    >
                        Nenhuma proposta adicionada.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="p in deal.proposals"
                            :key="p.id"
                            class="flex items-center justify-between rounded border p-3"
                        >
                            <div>
                                <div class="font-medium">
                                    📄 {{ p.original_name }}
                                </div>

                                <div class="text-xs text-gray-500">
                                    Upload:
                                    {{
                                        new Date(p.created_at).toLocaleString()
                                    }}
                                    <span v-if="p.sent_at">
                                        • Enviada {{ p.sent_at }}
                                    </span>
                                </div>
                            </div>

                            <button
                                v-if="!p.sent_at"
                                @click="openSendModal(p)"
                                class="rounded bg-indigo-600 px-3 py-2 text-sm text-white"
                            >
                                Enviar
                            </button>

                            <span
                                v-else
                                class="text-xs font-semibold text-emerald-700"
                            >
                                ✔ Enviada
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Activities -->
                <div class="rounded-lg border bg-white p-5">
                    <h2 class="mb-3 text-sm font-semibold text-gray-700">
                        Activities
                    </h2>

                    <div
                        v-if="deal.activities.length === 0"
                        class="text-sm text-gray-500"
                    >
                        No activities linked to this deal.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="a in deal.activities"
                            :key="a.id"
                            class="rounded border p-3"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded bg-gray-100 px-2 py-0.5 text-xs"
                                >
                                    {{ a.type }}
                                </span>
                                <div class="font-medium">
                                    {{ a.title }}
                                </div>
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Due: {{ a.due_at ?? '—' }}
                            </div>

                            <div
                                v-if="a.notes"
                                class="mt-2 text-sm text-gray-700"
                            >
                                {{ a.notes }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Person -->
                <div class="rounded-lg border bg-white p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700">
                        Person
                    </h3>

                    <div v-if="!deal.person" class="text-sm text-gray-500">
                        No person linked.
                    </div>

                    <div v-else class="text-sm">
                        <div class="font-medium">
                            {{ deal.person.first_name }}
                            {{ deal.person.last_name }}
                        </div>

                        <div class="text-gray-500">
                            {{ deal.person.email ?? '' }}
                        </div>

                        <a
                            :href="`/people/${deal.person.id}`"
                            class="mt-2 inline-block text-indigo-600 hover:underline"
                        >
                            View person →
                        </a>
                    </div>
                </div>

                <!-- Entity -->
                <div class="rounded-lg border bg-white p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700">
                        Entity
                    </h3>

                    <div v-if="!deal.entity" class="text-sm text-gray-500">
                        No entity linked.
                    </div>

                    <div v-else class="text-sm">
                        <div class="font-medium">
                            {{ deal.entity.name }}
                        </div>

                        <div class="text-gray-500">
                            {{ deal.entity.email ?? '' }}
                        </div>

                        <a
                            :href="`/entities/${deal.entity.id}`"
                            class="mt-2 inline-block text-indigo-600 hover:underline"
                        >
                            View entity →
                        </a>
                    </div>
                </div>

                <!-- Actions -->
                <div class="rounded-lg border bg-white p-5">
                    <h3 class="mb-2 text-sm font-semibold text-gray-700">
                        Actions
                    </h3>

                    <div class="space-y-2">
                        <button
                            v-if="deal.status !== 'won'"
                            @click="move('won')"
                            class="w-full rounded bg-emerald-600 px-4 py-2 text-sm text-white"
                        >
                            Mark as won
                        </button>

                        <button
                            v-if="deal.status !== 'lost'"
                            @click="move('lost')"
                            class="w-full rounded bg-red-600 px-4 py-2 text-sm text-white"
                        >
                            Mark as lost
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="currentProposal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div class="w-full max-w-lg space-y-4 rounded bg-white p-6">
                <h3 class="text-lg font-semibold">Enviar proposta</h3>

                <input
                    v-model="emailForm.subject"
                    class="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Assunto"
                />

                <textarea
                    v-model="emailForm.body"
                    rows="6"
                    class="w-full rounded border px-3 py-2 text-sm"
                />

                <div class="flex justify-end gap-2">
                    <button
                        @click="closeModal"
                        class="rounded border px-4 py-2 text-sm"
                    >
                        Cancelar
                    </button>

                    <button
                        @click="sendProposal"
                        :disabled="sending"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
                    >
                        Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
