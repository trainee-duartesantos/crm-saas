<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t bg-white px-6 py-3 text-sm text-muted-foreground">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
            <span> © {{ new Date().getFullYear() }} CRM SaaS </span>

            <div class="flex gap-4">
                <Link href="/contact" class="hover:text-primary">
                    Contact
                </Link>

                <Link href="/privacy" class="hover:text-primary">
                    Privacy
                </Link>
            </div>
        </div>
    </footer>
</template>
