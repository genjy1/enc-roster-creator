import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './main.css'
import 'flag-icons'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
