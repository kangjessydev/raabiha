/**
 * src/main.js — Vue 3 SPA Entry Point
 * Bootstraps the Raabiha Dashboard and mounts it on #app.
 */
import { createApp } from 'vue'
import App from './App.vue'
import './assets/css/tailwind.css'

const app = createApp(App)

// Mount the Vue application
app.mount('#app')
