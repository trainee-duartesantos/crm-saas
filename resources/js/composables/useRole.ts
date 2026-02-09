import { usePage } from '@inertiajs/vue3';

export function useRole() {
    const user = usePage().props.auth.user;

    return {
        role: user.role,
        isOwner: user.role === 'owner',
        isAdmin: ['admin', 'owner'].includes(user.role),
        isUser: user.role === 'user',
    };
}
