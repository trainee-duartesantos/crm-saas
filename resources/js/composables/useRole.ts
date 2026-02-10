import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type Role = 'user' | 'admin' | 'owner';

export function useRole() {
    const page = usePage();

    const role = computed<Role>(() => {
        const user = page.props.auth?.user as { role?: Role } | undefined;
        return user?.role ?? 'user';
    });

    return {
        role,
        isOwner: computed(() => role.value === 'owner'),
        isAdmin: computed(
            () => role.value === 'admin' || role.value === 'owner',
        ),
        isUser: computed(() => role.value === 'user'),
    };
}
