<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import FullCalendar from '@fullcalendar/vue3';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    events: Array<{
        id: number | string;
        title: string;
        start: string;
        end?: string | null;
        allDay?: boolean;
        extendedProps?: Record<string, any>;
    }>;
}>();

const calendarRef = ref<any>(null);

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',

    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },

    nowIndicator: true,
    navLinks: true,
    selectable: true,
    editable: false,

    events: props.events,

    expandRows: true,
    dayMaxEventRows: false,
    eventDisplay: 'block',

    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },

    eventClick: (info: any) => {
        if (info.event.id) {
            window.location.href = `/activities/${info.event.id}`;
        }
    },

    eventDidMount: (info: any) => {
        info.el.title = `${info.event.title}\n${info.timeText ?? ''}`;
    },

    eventClassNames: (arg: any) => {
        return arg.event.extendedProps?.completed_at ? ['opacity-60'] : [];
    },
}));
</script>

<template>
    <div class="mx-auto max-w-screen-2xl space-y-6 px-4 sm:px-6 lg:px-10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold">📅 Calendar</h1>
                <p class="text-sm text-gray-500">
                    View activities by month, week or day.
                </p>
            </div>

            <div class="flex gap-2">
                <a
                    href="/activities"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                >
                    Activities
                </a>
                <a
                    href="/timeline"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                >
                    Timeline
                </a>
            </div>
        </div>

        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <FullCalendar ref="calendarRef" :options="calendarOptions" />
        </div>

        <div
            v-if="!events?.length"
            class="rounded border border-dashed p-6 text-sm text-gray-500"
        >
            No activities to display yet. Create an activity with a due date to
            see it here.
        </div>
    </div>
</template>

<style>
.fc {
    font-size: 0.95rem;
}

.fc .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
}

.fc-daygrid-event {
    white-space: normal;
    line-height: 1.35;
    padding: 2px 4px;
}
</style>
