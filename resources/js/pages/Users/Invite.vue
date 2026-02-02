<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = ref({
    email: '',
    role: 'user',
});

const submit = () => {
    router.post('/users/invite', form.value);
};
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-md space-y-6">
            <div>
                <h1 class="text-2xl font-semibold">Invite user</h1>
                <p class="text-sm text-gray-500">
                    Invite someone to join your tenant.
                </p>
            </div>

            <div class="space-y-4 rounded-lg border bg-white p-6">
                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full rounded border px-3 py-2"
                        placeholder="user@email.com"
                    />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Role</label>
                    <select
                        v-model="form.role"
                        class="mt-1 w-full rounded border px-3 py-2"
                    >
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="/users" class="rounded border px-4 py-2 text-sm">
                        Cancel
                    </a>

                    <button
                        @click="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Send invite
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
