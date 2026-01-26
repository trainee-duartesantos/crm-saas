<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    name: '',
    password: '',
    password_confirmation: '',
    token: props.token,
});
</script>

<template>
    <Head title="Accept invitation" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50">
        <div class="w-full max-w-md rounded bg-white p-6 shadow">
            <h1 class="mb-2 text-xl font-semibold">🎉 Accept invitation</h1>

            <p class="mb-6 text-sm text-gray-600">
                You were invited as <strong>{{ email }}</strong>
            </p>

            <form
                @submit.prevent="form.post('/invites/accept')"
                class="space-y-4"
            >
                <div>
                    <label class="block text-sm font-medium">Name</label>
                    <input
                        v-model="form.name"
                        class="w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium">
                        Confirm password
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <div v-if="form.errors" class="text-sm text-red-600">
                    <div v-for="(error, key) in form.errors" :key="key">
                        {{ error }}
                    </div>
                </div>

                <button
                    class="w-full rounded bg-black py-2 text-white"
                    :disabled="form.processing"
                >
                    Accept invitation
                </button>
            </form>
        </div>
    </div>
</template>
