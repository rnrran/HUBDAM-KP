import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export type AppRole = 'admin' | 'supervisor' | 'pengguna' | string;

export function useRBAC() {
  const page = usePage();

  const role = computed<AppRole>(() => {
    const r = (page.props.currentRole as string) || 'pengguna';
    return (['admin', 'supervisor', 'pengguna'].includes(r) ? r : 'pengguna') as AppRole;
  });

  const isAdmin = computed(() => role.value === 'admin');
  const isSupervisor = computed(() => role.value === 'supervisor');
  const isPengguna = computed(() => role.value === 'pengguna');

  // Guards per rbac.md
  const canViewPayrollList = computed(() => isAdmin.value || isSupervisor.value); // pengguna sees only own in dashboard
  const canEditPayroll = computed(() => isAdmin.value); // supervisor cannot edit/delete
  const canDeletePayroll = computed(() => isAdmin.value);
  const canInputPayroll = computed(() => isAdmin.value); // supervisor cannot input
  const canManageUsers = computed(() => isAdmin.value); // edit/buat akun only for admin

  // Scaffold hook helpers
  const guardAction = (allowed: boolean, action: () => void) => {
    if (!allowed) return;
    action();
  };

  return {
    role,
    isAdmin,
    isSupervisor,
    isPengguna,
    canViewPayrollList,
    canEditPayroll,
    canDeletePayroll,
    canInputPayroll,
    canManageUsers,
    guardAction,
  };
}


