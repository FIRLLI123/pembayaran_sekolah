require('./bootstrap');
import './styles/data-table.css'

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'
import SiswaTable from './components/SiswaTable.vue'
import Alert from './components/Alert.vue'

const app = createApp({})

app.component('dashboard-component', Dashboard)
app.component('siswa-table', SiswaTable)
app.component('alert-component', Alert) // 👈 pindahin ke atas

app.mount('#app')