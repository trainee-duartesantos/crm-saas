<script setup>
defineProps({
    users: Array,
});
</script>

<template>
    <div>
        <h1 class="mb-4 text-xl font-bold">Users</h1>

        <ul>
            <li v-for="user in users" :key="user.id">
                {{ user.name }} — {{ user.email }} ({{ user.role }})
            </li>
        </ul>
    </div>
</template>
