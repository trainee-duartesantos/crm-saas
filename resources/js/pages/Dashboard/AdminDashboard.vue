<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold">🛠 Admin Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Gestão operacional do sistema
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <Link
                href="/users"
                class="rounded-lg border bg-white p-5 hover:bg-gray-50"
            >
                <div class="font-semibold">Users</div>
                <div class="text-sm text-gray-500">
                    Gerir utilizadores e convites
                </div>
            </Link>

            <Link
                href="/entities"
                class="rounded-lg border bg-white p-5 hover:bg-gray-50"
            >
                <div class="font-semibold">Entities</div>
                <div class="text-sm text-gray-500">Empresas e organizações</div>
            </Link>

            <Link
                href="/activities"
                class="rounded-lg border bg-white p-5 hover:bg-gray-50"
            >
                <div class="font-semibold">Activities</div>
                <div class="text-sm text-gray-500">Tarefas e follow-ups</div>
            </Link>
        </div>
    </div>
</template>
