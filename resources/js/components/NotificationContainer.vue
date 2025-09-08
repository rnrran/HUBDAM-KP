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
            return 'bg-green-100 border-green-300 text-green-900   ';
        case 'error': 
            return 'bg-red-100 border-red-300 text-red-900   ';
        case 'warning': 
            return 'bg-yellow-100 border-yellow-300 text-yellow-900   ';
        case 'info': 
            return 'bg-blue-100 border-blue-300 text-blue-900   ';
        default: 
            return 'bg-gray-100 border-gray-300 text-gray-900   ';
    }
};

const getIconColorClass = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-700 ';
        case 'error': return 'text-red-700 ';
        case 'warning': return 'text-yellow-700 ';
        case 'info': return 'text-blue-700 ';
        default: return 'text-gray-700 ';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <div
            v-for="notification in notifications"
            :key="notification.id"
            :class="[
                'max-w-md w-full rounded-lg border-2 p-4 shadow-xl transition-all duration-300 ease-in-out',
                'animate-in slide-in-from-right-full backdrop-blur-sm',
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