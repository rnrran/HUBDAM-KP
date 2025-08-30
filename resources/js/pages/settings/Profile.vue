<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import ImageCropper from '@/components/ImageCropper.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { ref } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    user: User;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan',
        href: '/settings',
    },
    {
        title: 'Edit Profile',
        href: '/settings/profile',
    },
];

const user = props.user;

const form = useForm({
    name: user.name,
    email: user.email,
    pangkat: user.pangkat || '',
    nomor_registrasi: user.nomor_registrasi || '',
    role: user.role || 'tamu',
    profile_photo: null as File | null,
});

const showImageCropper = ref(false);
const selectedImageFile = ref<File | null>(null);
const imagePreviewUrl = ref<string>('');

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('profile_photo');
        },
    });
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

const getProfilePhotoUrl = () => {
    if (imagePreviewUrl.value) {
        return imagePreviewUrl.value;
    }
    if (user.profile_photo) {
        return `/storage/${user.profile_photo}`;
    }
    return '/resources/js/assets/default_icons/pfp.png';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Edit Profile" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Informasi Profile" description="Update informasi profile Anda" />

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Profile Photo Upload -->
                    <div class="grid gap-2">
                        <Label>Foto Profile</Label>
                        <div class="flex items-center space-x-4">
                            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200">
                                <img 
                                    :src="getProfilePhotoUrl()" 
                                    alt="Profile Photo" 
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="flex flex-col space-y-2">
                                <Input 
                                    type="file" 
                                    accept="image/*" 
                                    @change="handleFileChange"
                                    class="max-w-xs"
                                />
                                <p class="text-sm text-muted-foreground">
                                    Format: JPG, PNG, GIF. Maksimal 2MB.
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.profile_photo" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pangkat">Pangkat</Label>
                        <Input id="pangkat" class="mt-1 block w-full" v-model="form.pangkat" placeholder="Masukkan pangkat" />
                        <InputError class="mt-2" :message="form.errors.pangkat" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nomor_registrasi">Nomor Registrasi</Label>
                        <Input id="nomor_registrasi" class="mt-1 block w-full" v-model="form.nomor_registrasi" placeholder="Masukkan nomor registrasi" />
                        <InputError class="mt-2" :message="form.errors.nomor_registrasi" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <Select v-model="form.role" :value="form.role">
                            <SelectTrigger>
                                <SelectValue :placeholder="form.role || 'Pilih role'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="tamu">Tamu</SelectItem>
                                <SelectItem value="admin">Admin</SelectItem>
                                <SelectItem value="non-admin">Non-Admin</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-2" :message="form.errors.role" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Email anda belum terverifikasi.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Klik di sini untuk mengirim ulang email verifikasi.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Simpan</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Berhasil disimpan.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
        
        <!-- Image Cropper Modal -->
        <ImageCropper
            v-model="showImageCropper"
            :image-file="selectedImageFile"
            @cropped="handleCroppedImage"
        />
    </AppLayout>
</template>
