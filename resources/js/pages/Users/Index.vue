<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    users: any[];
    invites: any[];
}>();

const page = usePage();
const role = page.props.auth.user.role;

const canInvite = role === 'admin' || role === 'owner';

const resend = (id: number) => {
    router.post(`/users/invite/${id}/resend`);
};

const cancel = (id: number) => {
    router.delete(`/users/invite/${id}`);
};
</script>

<template>
    <div class="space-y-8">
        <!-- USERS -->
        <section>
            <h1 class="mb-2 text-xl font-semibold">Users</h1>

            <ul class="space-y-1">
                <li v-for="user in users" :key="user.id">
                    {{ user.name }} — {{ user.email }} ({{ user.role }})
                </li>
            </ul>
        </section>

        <!-- INVITATIONS -->
        <section>
            <h2 class="mb-2 text-lg font-semibold">Invitations</h2>

            <ul class="space-y-2">
                <li
                    v-for="invite in invites"
                    :key="invite.id"
                    class="flex items-center gap-3"
                >
                    <span> {{ invite.email }} — {{ invite.role }} </span>

                    <!-- STATUS BADGE -->
                    <span
                        v-if="invite.accepted_at"
                        class="text-sm text-green-600"
                    >
                        accepted
                    </span>

                    <span
                        v-else-if="invite.is_expired"
                        class="text-sm text-red-600"
                    >
                        expired
                    </span>

                    <span v-else class="text-sm text-yellow-600">
                        pending
                    </span>

                    <!-- ACTIONS -->
                    <template v-if="canInvite && !invite.accepted_at">
                        <button
                            v-if="!invite.is_expired"
                            @click="resend(invite.id)"
                            class="text-sm text-blue-600"
                        >
                            Resend
                        </button>

                        <button
                            @click="cancel(invite.id)"
                            class="text-sm text-red-600"
                        >
                            Cancel
                        </button>
                    </template>
                </li>
            </ul>

            <!-- INVITE FORM -->
            <div v-if="canInvite" class="mt-6">
                <form method="post" action="/users/invite" class="flex gap-2">
                    <input
                        name="email"
                        type="email"
                        placeholder="Email"
                        required
                        class="border px-2 py-1"
                    />

                    <select name="role" class="border px-2 py-1">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>

                    <button class="bg-black px-3 text-white">Invite</button>
                </form>
            </div>
        </section>
    </div>
</template>
