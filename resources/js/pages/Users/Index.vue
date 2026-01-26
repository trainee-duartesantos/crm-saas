<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import invites from '@/routes/invites';
import invite from '@/routes/users/invite';

type User = {
    id: number;
    name: string;
    email: string;
    role: string;
};

type Invite = {
    id: number;
    email: string;
    role: string;
    accepted_at: string | null;
    is_expired: boolean;
};

const props = defineProps<{
    users: User[];
    invites: Invite[];
}>();

const page = usePage();
const role = page.props.auth.user.role as 'user' | 'admin' | 'owner';

const canInvite = role === 'admin' || role === 'owner';

const email = ref('');
const inviteRole = ref<'user' | 'admin'>('user');

const submitInvite = () => {
    router.post(invite.store().url, {
        email: email.value,
        role: inviteRole.value,
    });

    // opcional: limpar inputs
    email.value = '';
    inviteRole.value = 'user';
};

const resendInvite = (id: number) => {
    router.post(invites.resend(id).url);
};

const cancelInvite = (id: number) => {
    router.delete(invites.destroy(id).url);
};
</script>

<template>
    <div class="space-y-8">
        <!-- USERS -->
        <section>
            <h1 class="mb-2 text-xl font-semibold">Users</h1>

            <ul class="space-y-1">
                <li v-for="user in props.users" :key="user.id">
                    {{ user.name }} — {{ user.email }} ({{ user.role }})
                </li>
            </ul>
        </section>

        <!-- INVITATIONS -->
        <section>
            <h2 class="mb-2 text-lg font-semibold">Invitations</h2>

            <ul class="space-y-2">
                <li
                    v-for="inv in props.invites"
                    :key="inv.id"
                    class="flex items-center gap-3"
                >
                    <span>{{ inv.email }} — {{ inv.role }}</span>

                    <!-- STATUS BADGE -->
                    <span
                        v-if="inv.accepted_at"
                        class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700"
                    >
                        accepted
                    </span>

                    <span
                        v-else-if="inv.is_expired"
                        class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700"
                    >
                        expired
                    </span>

                    <span
                        v-else
                        class="rounded bg-yellow-100 px-2 py-0.5 text-xs text-yellow-700"
                    >
                        pending
                    </span>

                    <!-- ACTIONS (Admin / Owner only) -->
                    <template v-if="canInvite && !inv.accepted_at">
                        <button
                            v-if="!inv.is_expired"
                            @click="resendInvite(inv.id)"
                            class="text-sm text-blue-600 hover:underline"
                        >
                            Resend
                        </button>

                        <button
                            @click="cancelInvite(inv.id)"
                            class="text-sm text-red-600 hover:underline"
                        >
                            Cancel
                        </button>
                    </template>
                </li>
            </ul>

            <!-- INVITE FORM -->
            <div v-if="canInvite" class="mt-6">
                <form @submit.prevent="submitInvite" class="flex gap-2">
                    <input
                        v-model="email"
                        type="email"
                        placeholder="Email"
                        required
                        class="border px-2 py-1"
                    />

                    <select v-model="inviteRole" class="border px-2 py-1">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>

                    <button class="bg-black px-3 py-1 text-white">
                        Invite
                    </button>
                </form>
            </div>
        </section>
    </div>
</template>
