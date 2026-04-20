require('./bootstrap');
import './styles/data-table.css'

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'
import SiswaTable from './components/SiswaTable.vue'


const app = createApp({})

app.component('dashboard-component', Dashboard)
app.component('siswa-table', SiswaTable)

app.mount('#app')
