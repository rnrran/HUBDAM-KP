<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import NotificationContainer from '@/components/NotificationContainer.vue';
import ImageCropper from '@/components/ImageCropper.vue';
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
import { LoaderCircle, Eye, EyeOff, RefreshCw, User } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import pangkatData from '@/data/pangkat.json';

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
    nomor_registrasi?: string;
    role?: string;
    profile_photo?: string;
}

interface Props {
    users: User[];
    selectedUser?: User | null;
    pangkatOptions?: string[];
    currentRole?: string; // 'admin' | 'supervisor' | 'pengguna'
}

const props = defineProps<Props>();
const page = usePage();

const { success, error } = useNotifications();

// Use JSON data for pangkat options
const pangkatOptions = props.pangkatOptions || pangkatData;

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
const showImageCropper = ref(false);
const selectedImageFile = ref<File | null>(null);
const imagePreviewUrl = ref<string>('');

const form = useForm({
    name: '',
    email: '',
    password: '',
    pangkat: '',
    nomor_registrasi: '',
    role: '',
    profile_photo: null as File | null,
});

// Computed role permission (supervisor is view-only)
const isEditable = computed(() => {
    // prefer prop if provided, fallback to shared page prop
    const role = props.currentRole || (page.props.currentRole as string) || 'pengguna';
    return role !== 'supervisor';
});

// Initialize form if selectedUser is provided
if (props.selectedUser) {
    selectedUserId.value = props.selectedUser.id.toString();
    form.name = props.selectedUser.name;
    form.email = props.selectedUser.email;
    form.pangkat = props.selectedUser.pangkat || '';
    form.nomor_registrasi = props.selectedUser.nomor_registrasi || '';
    // Normalize legacy role names to new RBAC set
    const incomingRole = props.selectedUser.role || 'pengguna';
    form.role = incomingRole === 'non-admin' ? 'pengguna' : incomingRole;
}

// Users data loaded from controller

const canUpdateAccount = computed(() => {
    return form.name && form.email && selectedUserId.value;
});

const selectedUser = computed(() => {
    return props.users.find(user => user.id.toString() === selectedUserId.value);
});

// Single watch function for user selection changes
watch(selectedUserId, async (newUserId, oldUserId) => {
    if (!newUserId) {
        // Reset form when clearing selection
        if (oldUserId) {
            // Use a more controlled reset to avoid triggering watch again
            form.name = '';
            form.email = '';
            form.pangkat = '';
            form.nomor_registrasi = '';
            form.role = '';
            form.password = '';
            form.profile_photo = null;
        }
        return;
    }
    
    // Always load data for new user selection
    loadingUserData.value = true;
    try {
        const response = await axios.get(route('admin.users.show', newUserId));
        const userData = response.data;
        
        // Update form with new user data
        form.name = userData.name;
        form.email = userData.email;
        form.pangkat = userData.pangkat || '';
        form.nomor_registrasi = userData.nomor_registrasi || '';
        form.role = (userData.role === 'non-admin') ? 'pengguna' : (userData.role || 'pengguna');
        form.password = ''; // Always reset password field
        form.profile_photo = null; // Reset profile photo
        imagePreviewUrl.value = ''; // Reset image preview
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
    if (!isEditable.value) return;
    showConfirmModal.value = true;
};

const handleConfirm = () => {
    if (!selectedUserId.value) return;
    
    showConfirmModal.value = false;
    // Transform role to backend-expected value if needed
    form.transform((data) => ({
        ...data,
        role: data.role === 'pengguna' ? 'non-admin' : data.role,
    })).post(route('admin.users.update', selectedUserId.value), {
        onSuccess: () => {
            success('Berhasil!', `Akun ${selectedUser.value?.name} berhasil diperbarui.`);
            // Reset only the profile photo field to avoid triggering watch
            form.profile_photo = null;
        },
        onError: () => {
            error('Gagal!', 'Terjadi kesalahan saat memperbarui akun. Silakan coba lagi.');
        },
    });
};

const handleCancel = () => {
    showConfirmModal.value = false;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedImageFile.value = target.files[0];
        showImageCropper.value = true;
    }
};

const handleCroppedImage = (croppedFile: File) => {
    form.profile_photo = croppedFile;
    
    // Create preview URL for immediate display
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreviewUrl.value = e.target?.result as string;
    };
    reader.readAsDataURL(croppedFile);
    
    // Reset file input
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const getProfilePhotoUrl = (user: User) => {
    if (user.profile_photo) {
        return `/storage/${user.profile_photo}`;
    }
    return '/resources/js/assets/default_icons/pfp.png';
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

            <!-- User selector -->
            <Card class="w-full">
                <CardHeader>
                    <CardTitle>Pilih Pengguna</CardTitle>
                    <CardDescription>Pilih pengguna yang akan diperbarui.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-2 max-w-xl">
                        <Label for="selected_user">Pengguna</Label>
                        <Select v-model="selectedUserId">
                            <SelectTrigger>
                                <SelectValue :placeholder="selectedUser ? selectedUser.name : 'Pilih pengguna...'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="u in props.users" 
                                    :key="u.id" 
                                    :value="u.id.toString()"
                                >
                                    {{ u.name }} - {{ u.email }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            

            <!-- Edit Form -->
            <Card v-if="selectedUserId" class="w-full">
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
                                :disabled="!isEditable"
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
                                :disabled="!isEditable"
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
                                    :disabled="!isEditable"
                                />
                                <div class="absolute inset-y-0 right-0 flex items-center space-x-1 pr-3">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        class="h-6 w-6 p-0"
                                        @click="showPassword = !showPassword"
                                        :disabled="!isEditable"
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
                                :disabled="generatingPassword || !isEditable"
                            >
                                <RefreshCw v-if="generatingPassword" class="h-4 w-4 animate-spin mr-2" />
                                <RefreshCw v-else class="h-4 w-4 mr-2" />
                                Generate Password Baru
                            </Button>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="pangkat">Pangkat</Label>
                            <Select v-model="form.pangkat" :disabled="!isEditable">
                                <SelectTrigger>
                                    <SelectValue :placeholder="form.pangkat || 'Pilih pangkat'" />
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
                                :disabled="!isEditable"
                            />
                            <InputError :message="form.errors.nomor_registrasi" />
                        </div>

                        <!-- Profile Photo Upload -->
                        <div class="grid gap-2">
                            <Label>Foto Profile</Label>
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200">
                                    <img 
                                        v-if="imagePreviewUrl" 
                                        :src="imagePreviewUrl" 
                                        alt="Profile Photo Preview" 
                                        class="w-full h-full object-cover"
                                    />
                                    <img 
                                        v-else-if="selectedUser && selectedUser.profile_photo" 
                                        :src="getProfilePhotoUrl(selectedUser)" 
                                        alt="Profile Photo" 
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center">
                                        <img 
                                            src="/resources/js/assets/default_icons/pfp.png" 
                                            alt="Default Profile" 
                                            class="w-12 h-12 opacity-50"
                                        />
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2">
                                    <Input 
                                        type="file" 
                                        accept="image/*" 
                                        @change="handleFileChange"
                                        class="max-w-xs"
                                        :disabled="!isEditable"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Format: JPG, PNG, GIF. Maksimal 2MB.
                                    </p>
                                    <p v-if="form.profile_photo" class="text-sm text-green-600">
                                        ✓ File dipilih: {{ form.profile_photo.name }}
                                    </p>
                                </div>
                            </div>
                            <InputError :message="form.errors.profile_photo" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="role">Role</Label>
                            <Select v-model="form.role" :disabled="!isEditable">
                                <SelectTrigger>
                                    <SelectValue :placeholder="form.role || 'Pilih role'" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pengguna">Pengguna</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                    <SelectItem value="supervisor">Supervisor</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>

                        <div class="flex justify-end">
                            <Button 
                                @click="showConfirmation"
                                :disabled="!canUpdateAccount || form.processing || !isEditable"
                            >
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                                Perbarui Akun
                            </Button>
                        </div>
                    </template>
                </CardContent>
            </Card>

            <!-- Empty State -->
            <Card v-if="!selectedUserId" class="w-full max-w">
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
        
        <!-- Notifications -->
        <NotificationContainer />

        <!-- Image Cropper Modal -->
        <ImageCropper
            v-model="showImageCropper"
            :image-file="selectedImageFile"
            @cropped="handleCroppedImage"
        />
    </AppLayout>
</template>