<script setup lang="ts">
import { useNotifications } from '@/composables/useNotifications';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const { notifications, removeNotification } = useNotifications();

const getIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'error': return AlertCircle;
        case 'warning': return AlertTriangle;
        case 'info': return Info;
        default: return Info;
    }
};

const getColorClasses = (type: string) => {
    switch (type) {
        case 'success': 
            return 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-100';
        case 'error': 
            return 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-100';
        case 'warning': 
            return 'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100';
        case 'info': 
            return 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-100';
        default: 
            return 'bg-gray-50 border-gray-200 text-gray-800 dark:bg-gray-900/20 dark:border-gray-800 dark:text-gray-100';
    }
};

const getIconColorClass = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-600 dark:text-green-400';
        case 'error': return 'text-red-600 dark:text-red-400';
        case 'warning': return 'text-yellow-600 dark:text-yellow-400';
        case 'info': return 'text-blue-600 dark:text-blue-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="notification in notifications"
            :key="notification.id"
            :class="[
                'max-w-md w-full rounded-lg border p-4 shadow-lg transition-all duration-300 ease-in-out',
                'animate-in slide-in-from-right-full',
                getColorClasses(notification.type)
            ]"
        >
            <div class="flex items-start space-x-3">
                <component 
                    :is="getIcon(notification.type)" 
                    :class="['h-5 w-5 mt-0.5 flex-shrink-0', getIconColorClass(notification.type)]"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-medium">{{ notification.title }}</p>
                    <p class="text-sm opacity-90 mt-1">{{ notification.message }}</p>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    class="h-6 w-6 p-0 opacity-70 hover:opacity-100"
                    @click="removeNotification(notification.id)"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>