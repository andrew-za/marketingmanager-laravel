<template>
    <div class="chart-container" :style="{ height: height + 'px' }">
        <canvas ref="chartCanvas"></canvas>
    </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
    name: 'ChartComponent',
    props: {
        type: {
            type: String,
            required: true,
            validator: (value) => ['line', 'bar', 'pie', 'doughnut', 'radar', 'polarArea'].includes(value)
        },
        data: {
            type: Object,
            required: true
        },
        options: {
            type: Object,
            default: () => ({})
        },
        height: {
            type: Number,
            default: 300
        }
    },
    data() {
        return {
            chart: null
        };
    },
    mounted() {
        this.createChart();
    },
    watch: {
        data: {
            deep: true,
            handler() {
                if (this.chart) {
                    this.chart.data = this.data;
                    this.chart.update();
                }
            }
        },
        type() {
            this.destroyChart();
            this.createChart();
        }
    },
    beforeUnmount() {
        this.destroyChart();
    },
    methods: {
        createChart() {
            const ctx = this.$refs.chartCanvas;
            if (!ctx) return;

            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                ...this.options
            };

            this.chart = new Chart(ctx, {
                type: this.type,
                data: this.data,
                options: defaultOptions
            });
        },
        destroyChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    }
};
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
}
</style>

