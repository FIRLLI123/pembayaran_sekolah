<template>
    <div>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <div class="d-none d-sm-inline-block">
                <form class="form-inline" @submit.prevent>
                    <label class="mr-2">Filter Branch:</label>
                    <select class="form-control form-control-sm">
                        <option value="">Semua Branch</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4" v-for="card in cards" :key="card.title">
                <div class="card shadow h-100 py-2" :class="card.border">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1" :class="card.text">
                                    {{ card.title }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ card.value }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i :class="card.icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Row -->
        <div class="row">
            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Earnings Overview (Coming Soon)</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Revenue Sources (Coming Soon)</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle" href="#" role="button">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="myPieChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            <span class="mr-2"><i class="fas fa-circle text-primary"></i> Direct</span>
                            <span class="mr-2"><i class="fas fa-circle text-success"></i> Social</span>
                            <span class="mr-2"><i class="fas fa-circle text-info"></i> Referral</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Row -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pemasangan per Sales - Bulan Ini</h6>
                    </div>
                    <div class="card-body">
                        <template v-if="installationsBySales.length">
                            <div v-for="sales in installationsBySales" :key="sales.name">
                                <h4 class="small font-weight-bold">
                                    {{ sales.name }}
                                    <span class="float-right">{{ sales.total }}/{{ sales.max }}</span>
                                </h4>
                                <div class="progress mb-4">
                                    <div class="progress-bar" :class="getProgressColor(sales.percent)" role="progressbar" :style="{ width: sales.percent + '%' }" :aria-valuenow="sales.percent" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </template>
                        <p v-else class="text-muted">Tidak ada data pemasangan bulan ini.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pemasangan per Branch - Bulan Ini</h6>
                    </div>
                    <div class="card-body">
                        <template v-if="installationsByBranch.length">
                            <div v-for="branch in installationsByBranch" :key="branch.name">
                                <h4 class="small font-weight-bold">
                                    {{ branch.name }}
                                    <span class="float-right">{{ branch.total }}/{{ branch.max }}</span>
                                </h4>
                                <div class="progress mb-4">
                                    <div class="progress-bar" :class="getProgressColor(branch.percent)" role="progressbar" :style="{ width: branch.percent + '%' }" :aria-valuenow="branch.percent" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </template>
                        <p v-else class="text-muted">Tidak ada data pemasangan bulan ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Chart from "chart.js/auto"

export default {
    name: "Dashboard",

    data(){
        return{
            cards: [
                {
                    title: "Jumlah Pelanggan",
                    value: 0,
                    border: "border-left-primary",
                    text: "text-primary",
                    icon: "fas fa-users fa-2x text-gray-300"
                },
                {
                    title: "Jumlah Pemasangan",
                    value: 0,
                    border: "border-left-success",
                    text: "text-success",
                    icon: "fas fa-check-circle fa-2x text-gray-300"
                },
                {
                    title: "Jumlah Stok Gudang",
                    value: 0,
                    border: "border-left-info",
                    text: "text-info",
                    icon: "fas fa-box fa-2x text-gray-300"
                },
                {
                    title: "Jumlah Sales",
                    value: 0,
                    border: "border-left-warning",
                    text: "text-warning",
                    icon: "fas fa-chart-line fa-2x text-gray-300"
                }
            ],
            installationsBySales: [],
            installationsByBranch: []
        }
    },

    mounted(){
        this.initChart()
    },

    methods:{
        getProgressColor(percentage){
            if (percentage >= 80) return "bg-danger"
            if (percentage >= 60) return "bg-warning"
            if (percentage >= 40) return "bg-info"
            return "bg-success"
        },
        initChart(){
            new Chart(document.getElementById("myAreaChart"), {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar"],
                    datasets: [{
                        label: "Earnings",
                        data: [0, 0, 0]
                    }]
                }
            })

            new Chart(document.getElementById("myPieChart"), {
                type: "pie",
                data: {
                    labels: ["A", "B", "C"],
                    datasets: [{
                        data: [0, 0, 0]
                    }]
                }
            })
        }
    }
}
</script>
