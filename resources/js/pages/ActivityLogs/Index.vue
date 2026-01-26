<script setup>
defineProps({
    logs: Object,
});
</script>

<template>
    <div class="p-6">
        <h1 class="mb-4 text-xl font-bold">Activity Logs</h1>

        <div v-if="logs.data.length === 0" class="opacity-70">
            No activity yet.
        </div>

        <ul class="space-y-3">
            <li
                v-for="log in logs.data"
                :key="log.id"
                class="rounded border p-3"
            >
                <div class="font-medium">{{ log.action }}</div>

                <div class="text-sm opacity-70">
                    {{ log.created_at }}
                </div>

                <div class="text-sm">
                    Actor:
                    <span v-if="log.actor">
                        {{ log.actor.name }} ({{ log.actor.email }})
                    </span>
                    <span v-else>System</span>
                </div>

                <pre
                    v-if="log.metadata"
                    class="mt-2 rounded bg-gray-100 p-2 text-xs"
                    >{{ log.metadata }}
        </pre
                >
            </li>
        </ul>
    </div>
</template>
