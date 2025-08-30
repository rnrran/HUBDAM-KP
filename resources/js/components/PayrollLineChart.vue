<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Line } from 'vue-chartjs';
import { BarChart3 } from 'lucide-vue-next';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface ChartDataPoint {
    month: string;
    gaji_bersih: number;
    total_potongan: number;
    gaji_bersih_setelah_potongan: number;
}

interface Props {
    chartData: ChartDataPoint[];
    height?: number;
}

const props = withDefaults(defineProps<Props>(), {
    height: 400
});

const chartRef = ref<InstanceType<typeof Line> | null>(null);

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            mode: 'index' as const,
            intersect: false,
            callbacks: {
                label: function(context: any) {
                    const label = context.dataset.label || '';
                    const value = context.parsed.y;
                    return `${label}: ${formatCurrency(value)}`;
                }
            }
        }
    },
    scales: {
        x: {
            display: true,
            title: {
                display: true,
                text: 'Periode Waktu'
            },
            grid: {
                display: false
            }
        },
        y: {
            display: true,
            title: {
                display: true,
                text: 'Jumlah (Rp)'
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.1)'
            },
            ticks: {
                callback: function(value: any) {
                    return formatCurrency(value);
                }
            }
        }
    },
    interaction: {
        mode: 'nearest' as const,
        axis: 'x' as const,
        intersect: false
    }
}));

const chartData = computed(() => ({
    labels: props.chartData.map(d => d.month),
    datasets: [
        {
            label: 'Gaji Bersih',
            data: props.chartData.map(d => d.gaji_bersih),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: '#10b981',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.4
        },
        {
            label: 'Total Potongan',
            data: props.chartData.map(d => d.total_potongan),
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: '#ef4444',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.4
        },
        {
            label: 'Gaji Bersih Setelah Potongan',
            data: props.chartData.map(d => d.gaji_bersih_setelah_potongan),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            fill: true,
            tension: 0.4
        }
    ]
}));
</script>

<template>
    <div class="w-full">
        <div v-if="props.chartData.length === 0" class="text-center py-12 text-muted-foreground">
            <BarChart3 class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>Tidak ada data untuk ditampilkan</p>
        </div>
        
        <div v-else class="relative" :style="{ height: `${height}px` }">
            <Line
                ref="chartRef"
                :data="chartData"
                :options="chartOptions"
                class="w-full"
            />
        </div>
    </div>
</template>
