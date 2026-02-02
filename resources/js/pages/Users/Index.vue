<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps<{
    users: {
        id: number;
        name: string;
        email: string;
        role: string;
    }[];
    invites: {
        id: number;
        email: string;
        role: string;
        created_at: string;
    }[];
}>();

const resend = (id: number) => {
    router.post(`/invites/${id}/resend`);
};

const removeInvite = (id: number) => {
    if (!confirm('Remove this invite?')) return;
    router.delete(`/invites/${id}`);
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">👥 Users</h1>

                <a
                    href="/users/invite"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    + Invite user
                </a>
            </div>

            <!-- Active users -->
            <div class="rounded-lg border bg-white">
                <div class="border-b px-4 py-3 font-semibold">Active users</div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Role</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-t"
                        >
                            <td class="px-4 py-2 font-medium">
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-2 text-gray-600">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="{
                                        'bg-indigo-100 text-indigo-700':
                                            user.role === 'owner',
                                        'bg-emerald-100 text-emerald-700':
                                            user.role === 'admin',
                                        'bg-gray-200 text-gray-700':
                                            user.role === 'user',
                                    }"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pending invites -->
            <div class="rounded-lg border bg-white">
                <div class="border-b px-4 py-3 font-semibold">
                    Pending invites
                </div>

                <div
                    v-if="invites.length === 0"
                    class="px-4 py-6 text-sm text-gray-500"
                >
                    No pending invites.
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">Role</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="invite in invites"
                            :key="invite.id"
                            class="border-t"
                        >
                            <td class="px-4 py-2">
                                {{ invite.email }}
                            </td>

                            <td class="px-4 py-2">
                                {{ invite.role }}
                            </td>

                            <td class="flex gap-2 px-4 py-2">
                                <button
                                    @click="resend(invite.id)"
                                    class="text-xs text-indigo-600 hover:underline"
                                >
                                    Resend
                                </button>

                                <button
                                    @click="removeInvite(invite.id)"
                                    class="text-xs text-red-600 hover:underline"
                                >
                                    Remove
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
