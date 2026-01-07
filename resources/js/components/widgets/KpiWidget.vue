<template>
    <div class="kpi-widget">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        </div>
        <div v-else-if="kpis" class="grid grid-cols-2 gap-4">
            <div v-for="kpi in kpis" :key="kpi.name" class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ formatValue(kpi.value, kpi.format) }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ kpi.name }}</div>
                <div v-if="kpi.change" :class="[
                    'text-xs mt-1',
                    kpi.change > 0 ? 'text-green-600' : 'text-red-600'
                ]">
                    {{ kpi.change > 0 ? '+' : '' }}{{ kpi.change }}%
                </div>
            </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
            No KPI data available
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'KpiWidget',
    props: {
        widget: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        const kpis = ref(null);
        const loading = ref(true);
        const organizationId = document.getElementById('dashboard-app').dataset.organizationId;

        const loadKPIs = async () => {
            try {
                loading.value = true;
                const response = await axios.get(`/main/${organizationId}/dashboard/kpis`);
                kpis.value = response.data.data || [];
            } catch (error) {
                console.error('Failed to load KPIs:', error);
            } finally {
                loading.value = false;
            }
        };

        const formatValue = (value, format) => {
            if (format === 'currency') {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
            }
            if (format === 'percentage') {
                return `${value}%`;
            }
            if (format === 'number') {
                return new Intl.NumberFormat('en-US').format(value);
            }
            return value;
        };

        onMounted(() => {
            loadKPIs();
            // Refresh every 5 minutes
            setInterval(loadKPIs, 300000);
        });

        return {
            kpis,
            loading,
            formatValue,
        };
    },
};
</script>


