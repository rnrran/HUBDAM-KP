<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import NotificationContainer from '@/components/NotificationContainer.vue';
import { useNotifications } from '@/composables/useNotifications';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { LoaderCircle, Eye, EyeOff, RefreshCw } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import pangkatData from '@/data/pangkat.json';

interface Props {
    pangkatOptions?: string[];
}

const props = defineProps<Props>();

const { success, error } = useNotifications();

// Use JSON data for pangkat options
const pangkatOptions = props.pangkatOptions || pangkatData;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
    },
    {
        title: 'Buat Akun',
        href: '/admin/users/create',
    },
];

const currentStep = ref(1);
const showPassword = ref(false);
const showConfirmModal = ref(false);
const generatingPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    pangkat: '',
    nomor_registrasi: '',
});

const canProceedToStep2 = computed(() => {
    return form.name && form.email && form.password;
});

const canCreateAccount = computed(() => {
    return canProceedToStep2.value;
});

const stepTwoButtonText = computed(() => {
    if (form.pangkat || form.nomor_registrasi) {
        return 'Buat Akun';
    }
    return 'Lewati sekarang, buat akun';
});

const generatePassword = async () => {
    generatingPassword.value = true;
    try {
        const response = await axios.get(route('admin.generate.password'));
        form.password = response.data.password;
    } catch (error) {
        console.error('Error generating password:', error);
    } finally {
        generatingPassword.value = false;
    }
};

const nextStep = () => {
    if (canProceedToStep2.value) {
        currentStep.value = 2;
    }
};

const prevStep = () => {
    currentStep.value = 1;
};

const showConfirmation = () => {
    showConfirmModal.value = true;
};

const handleConfirm = () => {
    showConfirmModal.value = false;
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            success('Berhasil!', `Akun untuk ${form.name} berhasil dibuat.`);
            form.reset();
            currentStep.value = 1;
        },
        onError: () => {
            error('Gagal!', 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.');
        },
    });
};

const handleCancel = () => {
    showConfirmModal.value = false;
};
</script>

<template>
    <Head title="Buat Akun" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Buat Akun Baru</h1>
                <p class="text-muted-foreground">
                    Buat akun baru untuk personil dengan mengisi informasi yang diperlukan.
                </p>
            </div>

            <!-- Progress Indicator -->
            <div class="flex items-center space-x-4 mb-6">
                <div class="flex items-center space-x-2">
                    <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                        currentStep >= 1 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                    ]">
                        1
                    </div>
                    <span :class="currentStep >= 1 ? 'text-foreground' : 'text-muted-foreground'">
                        Informasi Dasar
                    </span>
                </div>
                
                <div class="flex-1 h-px bg-border"></div>
                
                <div class="flex items-center space-x-2">
                    <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                        currentStep >= 2 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                    ]">
                        2
                    </div>
                    <span :class="currentStep >= 2 ? 'text-foreground' : 'text-muted-foreground'">
                        Informasi Tambahan
                    </span>
                </div>
            </div>

            <!-- Step 1: Basic Information -->
            <Card v-if="currentStep === 1" class="w-full max-w-4xl">
                <CardHeader>
                    <CardTitle>Informasi Dasar</CardTitle>
                    <CardDescription>
                        Masukkan informasi dasar yang diperlukan untuk membuat akun baru.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nama Lengkap *</Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            placeholder="Masukkan nama lengkap"
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email *</Label>
                        <Input 
                            id="email" 
                            type="email"
                            v-model="form.email" 
                            placeholder="contoh@email.com"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password *</Label>
                        <div class="relative">
                            <Input 
                                id="password" 
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password" 
                                placeholder="Masukkan password"
                                class="pr-20"
                                required
                            />
                            <div class="absolute inset-y-0 right-0 flex items-center space-x-1 pr-3">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 w-6 p-0"
                                    @click="showPassword = !showPassword"
                                >
                                    <Eye v-if="!showPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                        <Button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            class="w-fit"
                            @click="generatePassword"
                            :disabled="generatingPassword"
                        >
                            <RefreshCw v-if="generatingPassword" class="h-4 w-4 animate-spin mr-2" />
                            <RefreshCw v-else class="h-4 w-4 mr-2" />
                            Generate Password
                        </Button>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex justify-end">
                        <Button 
                            @click="nextStep"
                            :disabled="!canProceedToStep2"
                        >
                            Selanjutnya
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Step 2: Additional Information -->
            <Card v-if="currentStep === 2" class="w-full max-w-4xl">
                <CardHeader>
                    <CardTitle>Informasi Tambahan</CardTitle>
                    <CardDescription>
                        Lengkapi informasi tambahan untuk personil (opsional).
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="pangkat">Pangkat</Label>
                        <Select v-model="form.pangkat">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih pangkat" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="pangkat in pangkatOptions" 
                                    :key="pangkat" 
                                    :value="pangkat"
                                >
                                    {{ pangkat }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.pangkat" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nomor_registrasi">Nomor Registrasi</Label>
                        <Input 
                            id="nomor_registrasi" 
                            v-model="form.nomor_registrasi" 
                            placeholder="Masukkan nomor registrasi"
                        />
                        <InputError :message="form.errors.nomor_registrasi" />
                    </div>

                    <div class="flex justify-between">
                        <Button variant="outline" @click="prevStep">
                            Kembali
                        </Button>
                        <Button 
                            @click="showConfirmation"
                            :disabled="!canCreateAccount"
                        >
                            {{ stepTwoButtonText }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmationModal
            v-model:open="showConfirmModal"
            title="Konfirmasi Buat Akun"
            description="Apakah Anda yakin ingin membuat akun baru dengan informasi yang telah diisi?"
            confirm-text="Buat Akun"
            cancel-text="Batal"
            :loading="form.processing"
            @confirm="handleConfirm"
            @cancel="handleCancel"
        />
        
        <!-- Notifications -->
        <NotificationContainer />
    </AppLayout>
</template>