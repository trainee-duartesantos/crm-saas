<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';

import type { CalendarOptions, EventApi } from '@fullcalendar/core';
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

const selectedEvent = ref<EventApi | null>(null);
const popoverPos = ref({ x: 0, y: 0 });

const closePopover = () => {
    selectedEvent.value = null;
};

const calendarOptions = computed<CalendarOptions>(() => ({
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

    events: props.events.map((event) => ({
        ...event,
        end: event.end || undefined,
    })) as any,

    expandRows: true,
    dayMaxEventRows: false,
    eventDisplay: 'block' as const,

    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },

    eventClick(info) {
        info.jsEvent.preventDefault();

        selectedEvent.value = info.event;
        popoverPos.value = {
            x: info.jsEvent.pageX,
            y: info.jsEvent.pageY,
        };
    },

    eventDidMount(info) {
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
            <div
                v-if="selectedEvent"
                class="fixed z-50 w-72 rounded-lg border bg-white p-4 shadow-lg"
                :style="{
                    top: popoverPos.y + 'px',
                    left: popoverPos.x + 'px',
                }"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="font-semibold text-gray-900">
                        {{ selectedEvent.title }}
                    </div>

                    <button
                        class="text-gray-400 hover:text-gray-600"
                        @click="closePopover"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-2 text-sm text-gray-600">
                    {{ selectedEvent.start?.toLocaleString() }}
                    <span v-if="selectedEvent.end">
                        → {{ selectedEvent.end.toLocaleString() }}
                    </span>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button
                        class="rounded border px-3 py-1 text-sm"
                        @click="closePopover"
                    >
                        Close
                    </button>

                    <a
                        :href="`/activities/${selectedEvent.id}`"
                        class="rounded bg-indigo-600 px-3 py-1 text-sm text-white"
                    >
                        Open activity
                    </a>
                </div>
            </div>
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
