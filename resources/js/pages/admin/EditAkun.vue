<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
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
} from '@/components/ui/select/';
import InputError from '@/components/InputError.vue';
import { LoaderCircle, Eye, EyeOff, RefreshCw, User } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
    nomor_registrasi?: string;
}

interface Props {
    users: User[];
    selectedUser?: User | null;
    pangkatOptions: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
    },
    {
        title: 'Edit Akun',
        href: '/admin/users/edit',
    },
];

const selectedUserId = ref<string>('');
const showPassword = ref(false);
const showConfirmModal = ref(false);
const generatingPassword = ref(false);
const loadingUserData = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    pangkat: '',
    nomor_registrasi: '',
});

// Initialize form if selectedUser is provided
if (props.selectedUser) {
    selectedUserId.value = props.selectedUser.id.toString();
    form.name = props.selectedUser.name;
    form.email = props.selectedUser.email;
    form.pangkat = props.selectedUser.pangkat || '';
    form.nomor_registrasi = props.selectedUser.nomor_registrasi || '';
}

const canUpdateAccount = computed(() => {
    return form.name && form.email && selectedUserId.value;
});

const selectedUser = computed(() => {
    return props.users.find(user => user.id.toString() === selectedUserId.value);
});

// Watch for user selection changes
watch(selectedUserId, async (newUserId) => {
    if (!newUserId) {
        form.reset();
        return;
    }

    loadingUserData.value = true;
    try {
        const response = await axios.get(route('admin.users.show', newUserId));
        const userData = response.data;
        
        form.name = userData.name;
        form.email = userData.email;
        form.pangkat = userData.pangkat || '';
        form.nomor_registrasi = userData.nomor_registrasi || '';
        form.password = ''; // Always reset password field
    } catch (error) {
        console.error('Error loading user data:', error);
    } finally {
        loadingUserData.value = false;
    }
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

const showConfirmation = () => {
    showConfirmModal.value = true;
};

const handleConfirm = () => {
    if (!selectedUserId.value) return;
    
    showConfirmModal.value = false;
    form.put(route('admin.users.update', selectedUserId.value), {
        onSuccess: () => {
            // Keep the form filled for further edits
        },
    });
};

const handleCancel = () => {
    showConfirmModal.value = false;
};
</script>

<template>
    <Head title="Edit Akun" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Edit Akun Personil</h1>
                <p class="text-muted-foreground">
                    Pilih dan edit informasi akun personil yang ada.
                </p>
            </div>

            <!-- User Selection -->
            <Card class="w-full max-w-4xl">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <User class="h-5 w-5" />
                        <span>Pilih Pengguna</span>
                    </CardTitle>
                    <CardDescription>
                        Pilih pengguna yang ingin diedit informasinya.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-2">
                        <Label for="user-select">Pilih Pengguna</Label>
                        <Select v-model="selectedUserId">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih pengguna untuk diedit" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="user in users" 
                                    :key="user.id" 
                                    :value="user.id.toString()"
                                >
                                    {{ user.name }} - {{ user.email }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Edit Form -->
            <Card v-if="selectedUserId" class="w-full max-w-4xl">
                <CardHeader>
                    <CardTitle>Edit Informasi Pengguna</CardTitle>
                    <CardDescription>
                        Edit informasi untuk {{ selectedUser?.name }}.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div v-if="loadingUserData" class="flex items-center justify-center py-8">
                        <LoaderCircle class="h-8 w-8 animate-spin" />
                        <span class="ml-2">Memuat data pengguna...</span>
                    </div>

                    <template v-else>
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
                            <Label for="password">Password Baru</Label>
                            <div class="relative">
                                <Input 
                                    id="password" 
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password" 
                                    placeholder="Kosongkan jika tidak ingin mengubah password"
                                    class="pr-20"
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
                                Generate Password Baru
                            </Button>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="pangkat">Pangkat</Label>
                            <Select v-model="form.pangkat">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih pangkat" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">
                                        Tidak ada pangkat
                                    </SelectItem>
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

                        <div class="flex justify-end">
                            <Button 
                                @click="showConfirmation"
                                :disabled="!canUpdateAccount || form.processing"
                            >
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                Perbarui Akun
                            </Button>
                        </div>
                    </template>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="!selectedUserId" class="w-full max-w-4xl">
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <User class="h-12 w-12 text-muted-foreground mb-4" />
                    <p class="text-lg font-medium text-muted-foreground mb-2">
                        Pilih Pengguna untuk Diedit
                    </p>
                    <p class="text-sm text-muted-foreground text-center">
                        Pilih pengguna dari dropdown di atas untuk mulai mengedit informasi akun.
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmationModal
            v-model:open="showConfirmModal"
            title="Konfirmasi Perbarui Akun"
            :description="`Apakah Anda yakin ingin memperbarui informasi akun ${selectedUser?.name}?`"
            confirm-text="Perbarui Akun"
            cancel-text="Batal"
            :loading="form.processing"
            @confirm="handleConfirm"
            @cancel="handleCancel"
        />
    </AppLayout>
</template>