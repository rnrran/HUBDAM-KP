<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();

</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-6">
                    <Link :href="route('home')" class="flex flex-col items-center gap-3">
                        <div class="flex h-35 w-35 items-center justify-center rounded-xl p-2">
                            <AppLogoIcon class="h-12 w-auto" />
                        </div>
                        <div class="text-center">
                            <h1 class="text-2xl font-bold text-primary">HUBDAM HASANUDDIN</h1>
                            <p class="text-sm text-muted-foreground">Sistem Manajemen Gaji</p>
                        </div>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h2 class="text-lg font-semibold">{{ title }}</h2>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
