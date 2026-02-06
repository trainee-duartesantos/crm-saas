<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

/* -----------------------
   Layout
----------------------- */
defineOptions({ layout: AppLayout });

const humanizeLogTitle = (action: string) => {
    const map: Record<string, string> = {
        'deal.status.changed': 'Status changed',
        'deal.follow_up.started': 'Follow-up started',
        'deal.follow_up.cancelled': 'Follow-up cancelled',
        'proposal.sent': 'Proposal sent',
        'proposal.uploaded': 'Proposal uploaded',
        'deal.created': 'Deal created',
    };

    return map[action] ?? action.replaceAll('.', ' ');
};

/* -----------------------
   Props
----------------------- */
const props = defineProps<{
    deal: any;
    timeline: any[];
    timeline_counts: Record<string, number>;
    followUp?: any;
}>();

/* -----------------------
   Local reactive state
----------------------- */
const timelineItems = ref([...props.timeline]);
const timelineCounts = ref({ ...props.timeline_counts });
const followUpLocal = ref(props.followUp ?? null);

/* -----------------------
   Deal actions
----------------------- */
const move = (status: string) => {
    router.post(`/deals/${props.deal.id}/move`, { status });
};

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

const humanizeKey = (key: string) => {
    return key.replaceAll('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const logIconMap: Record<string, string> = {
    'deal.status.changed': '🔁',
    'deal.follow_up.started': '📧',
    'deal.follow_up.cancelled': '🛑',
    'proposal.sent': '📄',
    'proposal.uploaded': '⬆️',
    'deal.created': '✨',
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

/* -----------------------
   Timeline filters
----------------------- */
const filters = ref<string[]>([]);
const search = ref('');

const toggleFilter = (type: string) => {
    filters.value.includes(type)
        ? (filters.value = filters.value.filter((t) => t !== type))
        : filters.value.push(type);
};

const filteredTimeline = computed(() =>
    timelineItems.value.filter((item) => {
        const matchType =
            filters.value.length === 0 || filters.value.includes(item.type);

        const matchSearch =
            !search.value ||
            item.title?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.description
                ?.toLowerCase()
                .includes(search.value.toLowerCase());

        return matchType && matchSearch;
    }),
);

/* -----------------------
   Modals
----------------------- */
const activeItem = ref<any | null>(null);
const activeModal = ref<'activity' | 'proposal' | 'log' | null>(null);

const openItem = (item: any) => {
    if (!['activity', 'proposal', 'log'].includes(item.type)) return;
    activeItem.value = item;
    activeModal.value = item.type;
};

const closeItem = () => {
    activeItem.value = null;
    activeModal.value = null;
};

/* -----------------------
   Proposal send modal
----------------------- */
const sending = ref(false);
const currentProposal = ref<any>(null);

const emailForm = ref({
    subject: 'Envio de proposta',
    body: 'Olá,\n\nSegue em anexo a proposta conforme combinado.\n\nObrigado.',
});

const openSendModal = (proposal: any) => {
    currentProposal.value = proposal;
};

const closeSendModal = () => {
    currentProposal.value = null;
    emailForm.value = {
        subject: 'Envio de proposta',
        body: 'Olá,\n\nSegue em anexo a proposta conforme combinado.\n\nObrigado.',
    };
};

const sendProposal = () => {
    if (!currentProposal.value) return;

    sending.value = true;
    router.post(
        `/proposals/${currentProposal.value.id}/send`,
        emailForm.value,
        {
            onFinish: () => {
                sending.value = false;
                closeSendModal();
            },
        },
    );
};

/* -----------------------
   UX: lock scroll on modal
----------------------- */
watch(activeModal, (v) => {
    document.body.style.overflow = v ? 'hidden' : '';
});

/* -----------------------
   Polling (real-time ready)
----------------------- */
let pollId: number | null = null;

const fetchTimeline = async () => {
    if (activeModal.value || currentProposal.value) return;
    if (document.visibilityState !== 'visible') return;

    const params = new URLSearchParams();
    filters.value.forEach((t) => params.append('types[]', t));
    if (search.value) params.set('q', search.value);

    const res = await fetch(
        `/deals/${props.deal.id}/timeline?${params.toString()}`,
        { headers: { Accept: 'application/json' } },
    );

    if (!res.ok) return;

    const data = await res.json();
    timelineItems.value = data.items;
    timelineCounts.value = data.counts;
    followUpLocal.value = data.followUp;
};

onMounted(() => {
    fetchTimeline();
    pollId = window.setInterval(fetchTimeline, 5000);
});

onBeforeUnmount(() => {
    if (pollId) clearInterval(pollId);
});
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-8">
        <!-- Header -->
        <div class="flex items-start justify-between gap-6">
            <div>
                <h1 class="text-2xl font-semibold">{{ deal.title }}</h1>

                <div class="mt-2 flex items-center gap-3 text-sm text-gray-600">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="statusColor"
                    >
                        {{ deal.status.toUpperCase() }}
                    </span>

                    <span v-if="deal.value">€ {{ deal.value }}</span>
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
            <!-- Left -->
            <div class="space-y-6 md:col-span-2">
                <!-- Deal info -->
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

                <!-- Follow-up -->
                <div v-if="followUp" class="space-y-1">
                    <div class="text-sm text-indigo-600">
                        📧 Next follow-up:
                        {{ new Date(followUp.next_run_at).toLocaleString() }}
                    </div>

                    <button
                        @click="
                            router.post(`/deals/${deal.id}/follow-ups/cancel`)
                        "
                        class="text-sm text-red-600 hover:underline"
                    >
                        Cancel automatic follow-ups
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-for="(count, type) in timeline_counts"
                        :key="type"
                        @click="toggleFilter(type)"
                        class="rounded-full border px-3 py-1 text-xs font-semibold"
                        :class="
                            filters.includes(type)
                                ? 'bg-indigo-600 text-white'
                                : 'bg-white text-gray-600'
                        "
                    >
                        {{ type }} ({{ count }})
                    </button>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search timeline…"
                        class="ml-auto rounded border px-3 py-1 text-sm"
                    />
                </div>

                <!-- Timeline -->
                <div class="rounded-lg border bg-white p-5">
                    <h2 class="mb-4 text-sm font-semibold text-gray-700">
                        Timeline
                    </h2>

                    <div
                        v-if="filteredTimeline.length === 0"
                        class="text-sm text-gray-500"
                    >
                        No activity yet.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="(item, index) in filteredTimeline"
                            :key="index"
                            class="flex gap-4"
                        >
                            <div class="text-xl">
                                {{
                                    item.type === 'ai'
                                        ? '🤖'
                                        : item.type === 'log'
                                          ? (logIconMap[item.meta.action] ??
                                            '🕒')
                                          : item.icon
                                }}
                            </div>

                            <div
                                class="flex-1 cursor-pointer rounded border p-3 hover:bg-gray-50"
                                @click="openItem(item)"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="font-medium">
                                            {{ item.title }}
                                        </div>

                                        <span
                                            v-if="
                                                item.type === 'ai' &&
                                                item.meta?.confidence
                                            "
                                            class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700"
                                        >
                                            {{
                                                Math.round(
                                                    item.meta.confidence * 100,
                                                )
                                            }}% confidence
                                        </span>

                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="{
                                                'bg-purple-100 text-purple-700':
                                                    item.type === 'ai',
                                                'bg-red-100 text-red-700':
                                                    item.meta?.risk,
                                                'bg-gray-100 text-gray-700':
                                                    item.type === 'log',
                                            }"
                                        >
                                            {{
                                                item.meta?.risk
                                                    ? 'risk'
                                                    : item.type
                                            }}
                                        </span>
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ formatDate(item.date) }}
                                    </div>
                                </div>

                                <div
                                    v-if="item.type === 'ai'"
                                    class="mt-1 text-sm text-gray-700"
                                >
                                    <strong>Recommended:</strong>
                                    {{ item.description }}

                                    <div
                                        v-if="item.meta?.reason"
                                        class="mt-1 text-xs text-gray-500 italic"
                                    >
                                        {{ item.meta.reason }}
                                    </div>
                                </div>

                                <div
                                    v-else-if="item.description"
                                    class="mt-1 text-sm text-gray-700"
                                >
                                    {{ item.description }}
                                </div>
                                <button
                                    v-if="
                                        item.type === 'ai' &&
                                        item.meta?.action === 'upload_proposal'
                                    "
                                    class="mt-2 inline-flex items-center gap-2 rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                    @click="$inertia.visit(`/deals/${deal.id}`)"
                                >
                                    📤 Upload proposal
                                </button>
                                <div
                                    v-if="item.meta?.risk"
                                    class="mt-2 rounded bg-red-50 p-2 text-sm text-red-700"
                                >
                                    <strong>⚠️ Risk:</strong>
                                    {{ item.meta.reason }}
                                </div>
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
                            {{ deal.person.email }}
                        </div>
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
    </div>

    <!-- ======================
         ACTIVITY MODAL
    ====================== -->
    <div
        v-if="activeModal === 'activity' && activeItem"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-lg space-y-4 rounded bg-white p-6">
            <h3 class="text-lg font-semibold">{{ activeItem.title }}</h3>

            <div class="text-sm text-gray-500">
                {{ new Date(activeItem.date).toLocaleString() }}
            </div>

            <div class="rounded bg-gray-50 p-3 text-sm">
                {{ activeItem.description ?? 'No details.' }}
            </div>

            <div class="flex justify-end gap-2">
                <button
                    @click="closeItem"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Close
                </button>

                <button
                    v-if="!activeItem.meta?.completed_at"
                    @click="
                        router.post(
                            `/activities/${activeItem.meta.activity_id}/complete`,
                            {},
                            { onSuccess: closeItem },
                        )
                    "
                    class="rounded bg-emerald-600 px-4 py-2 text-sm text-white"
                >
                    Mark as done
                </button>

                <span
                    v-else
                    class="rounded bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700"
                >
                    ✓ Completed
                </span>
            </div>
        </div>
    </div>

    <!-- PROPOSAL MODAL -->
    <div
        v-if="activeModal === 'proposal' && activeItem"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-lg space-y-4 rounded bg-white p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">
                    {{ activeItem.meta.original_name }}
                </h3>

                <button
                    @click="closeItem"
                    class="text-gray-400 hover:text-gray-600"
                >
                    ✕
                </button>
            </div>

            <div class="text-xs text-gray-400 uppercase">Proposal</div>

            <div class="text-sm text-gray-500">
                {{ new Date(activeItem.date).toLocaleString() }}
            </div>

            <div class="rounded bg-gray-50 p-3 text-sm">
                <span
                    v-if="activeItem.meta.sent_at"
                    class="font-semibold text-emerald-600"
                >
                    ✓ Sent
                </span>
                <span v-else class="font-semibold text-yellow-600">
                    ⏳ Uploaded (not sent)
                </span>
            </div>

            <div class="flex justify-between gap-2">
                <a
                    :href="`/proposals/${activeItem.meta.proposal_id}/download`"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Download
                </a>

                <button
                    @click="openSendModal({ id: activeItem.meta.proposal_id })"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
                >
                    Resend email
                </button>
            </div>

            <!-- Footer -->
            <div class="flex justify-end">
                <button
                    @click="closeItem"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- ======================
         LOG MODAL
    ====================== -->
    <div
        v-if="activeModal === 'log' && activeItem"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-lg space-y-4 rounded bg-white p-6">
            <h3 class="text-lg font-semibold">
                {{ humanizeLogTitle(activeItem.meta.action) }}
            </h3>

            <div class="text-sm text-gray-500">
                {{ new Date(activeItem.date).toLocaleString() }}
            </div>

            <div v-if="activeItem.meta?.actor" class="text-sm text-gray-600">
                Actor: <strong>{{ activeItem.meta.actor }}</strong>
            </div>

            <div class="space-y-2 text-sm">
                <div
                    v-for="(value, key) in activeItem.meta.metadata"
                    :key="key"
                    class="flex gap-2"
                >
                    <span class="font-medium text-gray-600">
                        {{ humanizeKey(String(key)) }}:
                    </span>

                    <span class="text-gray-800">{{ value }}</span>
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    @click="closeItem"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- ======================
         SEND PROPOSAL MODAL
    ====================== -->
    <div
        v-if="currentProposal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    >
        <div class="w-full max-w-lg space-y-4 rounded bg-white p-6">
            <h3 class="text-lg font-semibold">Enviar proposta</h3>

            <input
                v-model="emailForm.subject"
                class="w-full rounded border px-3 py-2 text-sm"
            />

            <textarea
                v-model="emailForm.body"
                rows="6"
                class="w-full rounded border px-3 py-2 text-sm"
            />

            <div class="flex justify-end gap-2">
                <button
                    @click="closeSendModal"
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
</template>
