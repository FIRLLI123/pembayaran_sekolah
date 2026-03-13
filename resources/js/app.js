require('./bootstrap');

import { createApp } from 'vue'
import Dashboard from './components/Dashboard.vue'

const app = createApp({})

app.component('dashboard-component', Dashboard)

app.mount('#app')