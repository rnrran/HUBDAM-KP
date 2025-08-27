<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { BarChart3, Eye, Filter, TrendingUp, Users } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
}

interface PayrollRecord {
    id: number;
    user_id: number;
    gaji_bersih: number;
    tanggal_dibayarkan: string;
    total_deductions: number;
    net_salary: number;
    user: User;
    created_at: string;
}

interface ChartDataPoint {
    month: string;
    gaji_bersih: number;
    total_potongan: number;
    gaji_bersih_setelah_potongan: number;
}

interface Props {
    payrolls: {
        data: PayrollRecord[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    chartData: ChartDataPoint[];
    selectedUser?: User | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
    },
    {
        title: 'Lihat Gaji',
        href: '/admin/payroll',
    },
];

const selectedFilter = ref('all');
const viewMode = ref<'table' | 'chart'>('table');

const filteredPayrolls = computed(() => {
    if (selectedFilter.value === 'all') {
        return props.payrolls.data;
    }
    return props.payrolls.data.filter(payroll => 
        payroll.user_id.toString() === selectedFilter.value
    );
});

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        // style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// Get unique users from payroll data
const uniqueUsers = computed(() => {
    const users = new Map();
    props.payrolls.data.forEach(payroll => {
        if (!users.has(payroll.user.id)) {
            users.set(payroll.user.id, payroll.user);
        }
    });
    return Array.from(users.values());
});

// Simple chart component using CSS
const maxValue = computed(() => {
    return Math.max(...props.chartData.map(d => d.gaji_bersih));
});

const getBarHeight = (value: number): string => {
    return `${(value / maxValue.value) * 100}%`;
};

const getBarColor = (type: 'gaji' | 'potongan' | 'net'): string => {
    switch (type) {
        case 'gaji': return 'bg-green-500';
        case 'potongan': return 'bg-red-500';
        case 'net': return 'bg-blue-500';
        default: return 'bg-gray-500';
    }
};
</script>

<template>
    <Head title="Lihat Gaji" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Lihat Gaji Personil</h1>
                <p class="text-muted-foreground">
                    Lihat riwayat gaji dan visualisasi data gaji personil.
                </p>
            </div>

            <!-- Controls -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    <!-- Filter -->
                    <div class="flex items-center space-x-2">
                        <Filter class="h-4 w-4" />
                        <Label for="filter-select">Filter:</Label>
                        <Select v-model="selectedFilter">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="Semua Personil" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Personil</SelectItem>
                                <SelectItem 
                                    v-for="user in uniqueUsers" 
                                    :key="user.id" 
                                    :value="user.id.toString()"
                                >
                                    {{ user.name }} - {{ user.pangkat || 'Tidak ada pangkat' }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- View Mode Toggle -->
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :class="{ 'bg-primary text-primary-foreground': viewMode === 'table' }"
                            @click="viewMode = 'table'"
                        >
                            <Eye class="h-4 w-4 mr-2" />
                            Tabel
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :class="{ 'bg-primary text-primary-foreground': viewMode === 'chart' }"
                            @click="viewMode = 'chart'"
                        >
                            <BarChart3 class="h-4 w-4 mr-2" />
                            Grafik
                        </Button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                    <div class="flex items-center space-x-2">
                        <Users class="h-4 w-4" />
                        <span>{{ uniqueUsers.length }} Personil</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <TrendingUp class="h-4 w-4" />
                        <span>{{ payrolls.total }} Record</span>
                    </div>
                </div>
            </div>

            <!-- Chart View -->
            <Card v-if="viewMode === 'chart'" class="w-full">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <BarChart3 class="h-5 w-5" />
                        <span>Visualisasi Data Gaji</span>
                    </CardTitle>
                    <CardDescription>
                        Grafik perbandingan gaji bersih, potongan, dan gaji bersih setelah potongan.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-6">
                        <!-- Legend -->
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span>Gaji Bersih</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-red-500 rounded"></div>
                                <span>Total Potongan</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                <span>Gaji Bersih Setelah Potongan</span>
                            </div>
                        </div>

                        <!-- Simple CSS Chart -->
                        <div class="space-y-4">
                            <div v-if="chartData.length === 0" class="text-center py-12 text-muted-foreground">
                                <BarChart3 class="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p>Tidak ada data untuk ditampilkan</p>
                            </div>
                            
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="data in chartData" :key="data.month" class="space-y-2">
                                    <div class="text-sm font-medium text-center">{{ data.month }}</div>
                                    <div class="relative bg-muted rounded-lg p-4 h-48">
                                        <div class="flex items-end justify-center space-x-2 h-full">
                                            <!-- Gaji Bersih Bar -->
                                            <div class="flex flex-col items-center space-y-1">
                                                <div 
                                                    class="w-8 bg-green-500 rounded-t transition-all duration-300"
                                                    :style="{ height: getBarHeight(data.gaji_bersih) }"
                                                ></div>
                                                <div class="text-xs text-center">
                                                    {{ formatCurrency(data.gaji_bersih) }}
                                                </div>
                                            </div>
                                            
                                            <!-- Potongan Bar -->
                                            <div class="flex flex-col items-center space-y-1">
                                                <div 
                                                    class="w-8 bg-red-500 rounded-t transition-all duration-300"
                                                    :style="{ height: getBarHeight(data.total_potongan) }"
                                                ></div>
                                                <div class="text-xs text-center">
                                                    {{ formatCurrency(data.total_potongan) }}
                                                </div>
                                            </div>
                                            
                                            <!-- Net Salary Bar -->
                                            <div class="flex flex-col items-center space-y-1">
                                                <div 
                                                    class="w-8 bg-blue-500 rounded-t transition-all duration-300"
                                                    :style="{ height: getBarHeight(data.gaji_bersih_setelah_potongan) }"
                                                ></div>
                                                <div class="text-xs text-center">
                                                    {{ formatCurrency(data.gaji_bersih_setelah_potongan) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Table View -->
            <Card v-if="viewMode === 'table'" class="w-full">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Eye class="h-5 w-5" />
                        <span>Riwayat Gaji Personil</span>
                    </CardTitle>
                    <CardDescription>
                        Data riwayat gaji dan potongan personil.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <div v-if="filteredPayrolls.length === 0" class="text-center py-12 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>Tidak ada data gaji yang ditemukan</p>
                        </div>
                        
                        <table v-else class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-center p-4 font-medium">Personil</th>
                                    <th class="text-center p-4 font-medium">Pangkat</th>
                                    <th class="text-center p-4 font-medium">Gaji Bersih (Rp)</th>
                                    <th class="text-center p-4 font-medium">Total Potongan (Rp)</th>
                                    <th class="text-center p-4 font-medium">Gaji Bersih Setelah Potongan (Rp)</th>
                                    <th class="text-center p-4 font-medium">Tanggal Dibayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payroll in filteredPayrolls" :key="payroll.id" class="border-b hover:bg-muted/50 text-left">
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium ">{{ payroll.user.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ payroll.user.email }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ payroll.user.pangkat || 'Tidak ada pangkat' }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(payroll.gaji_bersih) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-medium  text-red-600">
                                            {{ formatCurrency(payroll.total_deductions) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-bold text-black">
                                            {{ formatCurrency(payroll.net_salary) }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ formatDate(payroll.tanggal_dibayarkan) }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Info -->
                    <div v-if="payrolls.total > payrolls.per_page" class="mt-4 text-sm text-muted-foreground text-center">
                        Menampilkan {{ payrolls.data.length }} dari {{ payrolls.total }} record
                        (Halaman {{ payrolls.current_page }} dari {{ payrolls.last_page }})
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>