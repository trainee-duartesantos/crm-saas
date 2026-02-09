<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <div class="mx-auto max-w-7xl space-y-10 px-6 py-8 lg:px-8">
        <!-- HERO -->
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-semibold">
                🛠 Admin Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Operational management and system administration
            </p>
        </div>

        <!-- ADMIN OVERVIEW -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-semibold text-gray-700">
                Administration areas
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <Link
                    href="/users"
                    class="rounded-lg border bg-linear-to-br from-indigo-50 to-white p-5 hover:shadow-sm"
                >
                    <div class="text-lg font-semibold">Users</div>
                    <div class="mt-1 text-sm text-gray-500">
                        Manage users and invitations
                    </div>
                </Link>

                <Link
                    href="/entities"
                    class="rounded-lg border bg-linear-to-br from-emerald-50 to-white p-5 hover:shadow-sm"
                >
                    <div class="text-lg font-semibold">Entities</div>
                    <div class="mt-1 text-sm text-gray-500">
                        Companies and organizations
                    </div>
                </Link>

                <Link
                    href="/activities"
                    class="rounded-lg border bg-linear-to-br from-amber-50 to-white p-5 hover:shadow-sm"
                >
                    <div class="text-lg font-semibold">Activities</div>
                    <div class="mt-1 text-sm text-gray-500">
                        Tasks, follow-ups and reminders
                    </div>
                </Link>
            </div>
        </div>

        <!-- ADMIN NOTE -->
        <div
            class="rounded-xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600"
        >
            🔐 Admin users manage operational data and system-level workflows.
        </div>
    </div>
</template>
