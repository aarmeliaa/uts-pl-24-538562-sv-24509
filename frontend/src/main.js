import { createApp } from 'vue'

// Bootstrap CSS first, then our own overrides.
import 'bootstrap/dist/css/bootstrap.min.css'
import './assets/main.css'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router)

app.mount('#app')
