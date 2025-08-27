<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
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
import pangkatData from '@/data/pangkat.json';
import type { BreadcrumbItem } from '@/types';

interface UserOption {
  id: number;
  name: string;
  email: string;
  pangkat?: string | null;
  nomor_registrasi?: string | null;
  role: 'admin' | 'supervisor' | 'pengguna' | string;
  profile_photo?: string | null;
}

interface Props {
  users?: UserOption[];
  selectedUser?: UserOption | null;
  pangkatOptions?: string[];
}

const props = defineProps<Props>();

const { success, error } = useNotifications();

const pangkatOptions = props.pangkatOptions || pangkatData;

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Halaman Utama', href: '/dashboard' },
  { title: 'Edit Akun', href: '/admin/users/edit' },
];

const selectedUserId = ref<string | null>(props.selectedUser ? String(props.selectedUser.id) : null);
const showConfirmModal = ref(false);
const showImageCropper = ref(false);
const selectedImageFile = ref<File | null>(null);
const imagePreviewUrl = ref<string>('');
const selectedImageName = ref<string>('');

const form = useForm({
  name: props.selectedUser?.name ?? '',
  email: props.selectedUser?.email ?? '',
  password: '',
  pangkat: props.selectedUser?.pangkat ?? '',
  nomor_registrasi: props.selectedUser?.nomor_registrasi ?? '',
  role: (props.selectedUser?.role as any) ?? 'pengguna',
  profile_photo: null as File | null,
});

watch(() => props.selectedUser, (u) => {
  if (!u) return;
  form.name = u.name || '';
  form.email = u.email || '';
  form.password = '';
  form.pangkat = u.pangkat || '';
  form.nomor_registrasi = u.nomor_registrasi || '';
  form.role = (u.role as any) || 'pengguna';
  form.profile_photo = null;
  imagePreviewUrl.value = '';
  selectedImageName.value = '';
});

const canSubmit = computed(() => !!selectedUserId.value && !!form.name && !!form.email);

watch(selectedUserId, (id) => {
  if (!id) return;
  router.get(route('admin.users.edit', { user: id }));
});

const handleConfirm = () => {
  if (!selectedUserId.value) return;
  showConfirmModal.value = false;
  form.post(route('admin.users.update', { user: selectedUserId.value }), {
    forceFormData: true,
    onSuccess: () => {
      success('Berhasil!', 'Perubahan data pengguna disimpan.');
      form.password = '';
    },
    onError: () => {
      error('Gagal!', 'Tidak dapat menyimpan perubahan.');
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
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreviewUrl.value = e.target?.result as string;
  };
  reader.readAsDataURL(croppedFile);
  selectedImageName.value = croppedFile.name || 'Gambar hasil crop';
};

const displayedPhotoUrl = computed(() => {
  if (imagePreviewUrl.value) return imagePreviewUrl.value;
  const existing = props.selectedUser?.profile_photo;
  if (existing) return `/storage/${existing}`;
  return '';
});
</script>

<template>
  <Head title="Edit Akun" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
      <div class="space-y-2">
        <h1 class="text-2xl font-bold tracking-tight">Edit Akun</h1>
        <p class="text-muted-foreground">Pilih pengguna kemudian ubah data yang diperlukan.</p>
      </div>

      <Card class="w-full max-w">
        <CardHeader>
          <CardTitle>Pilih Pengguna</CardTitle>
          <CardDescription>Pilih pengguna yang akan diedit.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid gap-2 max-w-md">
            <Label for="user">Pengguna</Label>
            <Select v-model="selectedUserId">
              <SelectTrigger>
                <SelectValue :placeholder="'Pilih Pengguna'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="null as any">Pilih Pengguna</SelectItem>
                <SelectItem v-for="u in (props.users || [])" :key="u.id" :value="String(u.id)">
                  {{ u.name }} — {{ u.email }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      <Card v-if="props.selectedUser" class="w-full max-w">
        <CardHeader>
          <CardTitle>Informasi Pengguna</CardTitle>
          <CardDescription>Perbarui informasi pengguna terpilih.</CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <div class="grid gap-2">
            <Label for="name">Nama Lengkap *</Label>
            <Input id="name" v-model="form.name" placeholder="Masukkan nama lengkap" required />
            <InputError :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="email">Email *</Label>
            <Input id="email" type="email" v-model="form.email" placeholder="contoh@email.com" required />
            <InputError :message="form.errors.email" />
          </div>

          <div class="grid gap-2">
            <Label for="password">Password (opsional)</Label>
            <Input id="password" type="password" v-model="form.password" placeholder="Biarkan kosong jika tidak diubah" />
            <InputError :message="form.errors.password" />
          </div>

          <div class="grid gap-2">
            <Label for="pangkat">Pangkat</Label>
            <Select v-model="form.pangkat" :value="form.pangkat">
              <SelectTrigger>
                <SelectValue :placeholder="form.pangkat || 'Pilih pangkat'" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="pangkat in pangkatOptions" :key="pangkat" :value="pangkat">
                  {{ pangkat }}
                </SelectItem>
              </SelectContent>
            </Select>
            <InputError :message="form.errors.pangkat" />
          </div>

          <div class="grid gap-2">
            <Label for="nomor_registrasi">Nomor Registrasi</Label>
            <Input id="nomor_registrasi" v-model="form.nomor_registrasi" placeholder="Masukkan nomor registrasi" />
            <InputError :message="form.errors.nomor_registrasi" />
          </div>

          <div class="grid gap-2">
            <Label for="role">Role *</Label>
            <Select v-model="form.role" :value="form.role">
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

          <div class="grid gap-2">
            <Label>Foto Profile</Label>
            <div class="flex items-center space-x-4">
              <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-gray-200 bg-gray-100 flex items-center justify-center">
                <template v-if="displayedPhotoUrl">
                  <img :src="displayedPhotoUrl" alt="Profile Photo" class="w-full h-full object-cover" />
                </template>
                <template v-else>
                  <div class="text-gray-400 text-xs text-center">No Image</div>
                </template>
              </div>
              <div class="flex flex-col space-y-2">
                <Input type="file" accept="image/*" @change="handleFileChange" class="max-w-xs" />
                <p class="text-sm text-muted-foreground">{{ selectedImageName || 'Format: JPG, PNG, GIF. Maksimal 2MB.' }}</p>
              </div>
            </div>
            <InputError :message="form.errors.profile_photo" />
          </div>

          <div class="flex justify-end">
            <Button :disabled="!canSubmit" @click="showConfirmModal = true">Simpan Perubahan</Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <ConfirmationModal
      v-model:open="showConfirmModal"
      title="Konfirmasi Simpan"
      description="Simpan perubahan pada akun pengguna ini?"
      confirm-text="Simpan"
      cancel-text="Batal"
      :loading="form.processing"
      @confirm="handleConfirm"
      @cancel="() => (showConfirmModal = false)"
    />

    <NotificationContainer />

    <ImageCropper v-model="showImageCropper" :image-file="selectedImageFile" @cropped="handleCroppedImage" />
  </AppLayout>
  </template>


