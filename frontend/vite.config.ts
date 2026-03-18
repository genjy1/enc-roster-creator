import { fileURLToPath, URL } from 'node:url'
import tailwindcss from '@tailwindcss/vite'


import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: 'localhost',
      port: 80,
      protocol: 'ws',
    },
    watch: {
      // Windows не пробрасывает файловые события в Docker — используем polling
      usePolling: true,
      interval: 300,
    },
  },
})
