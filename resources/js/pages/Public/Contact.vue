<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    company: '',
    message: '',
});

const submit = () => {
    form.post('/contact');
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-lg rounded-lg bg-white p-8 shadow">
            <h1 class="mb-2 text-2xl font-semibold">Request a demo</h1>

            <p class="mb-6 text-sm text-gray-600">
                Leave your details and we’ll get back to you shortly.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="mt-1 w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <div>
                    <label class="text-sm font-medium">Company</label>
                    <input
                        v-model="form.company"
                        type="text"
                        class="mt-1 w-full rounded border px-3 py-2"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium">Message</label>
                    <textarea
                        v-model="form.message"
                        rows="4"
                        class="mt-1 w-full rounded border px-3 py-2"
                        required
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
                    :disabled="form.processing"
                >
                    Send request
                </button>

                <div
                    v-if="form.recentlySuccessful"
                    class="rounded bg-green-100 p-3 text-sm text-green-800"
                >
                    ✅ Thanks! We’ll contact you shortly.
                </div>
            </form>
        </div>
    </div>
</template>
