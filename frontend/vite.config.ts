import { fileURLToPath } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  build: {
    // Optimize chunk sizes for faster loading
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor': ['vue', 'vue-router', 'pinia', 'axios'],
          'auth': ['./src/views/auth'],
          'admin': ['./src/views/admin'],
          'farmer': ['./src/views/farmer'],
          'buyer': ['./src/views/buyer'],
          'supplier': ['./src/views/supplier'],
          'transport': ['./src/views/transport'],
          'expert': ['./src/views/expert'],
          'financial': ['./src/views/financial'],
          'cooperative': ['./src/views/cooperative']
        }
      }
    },
    // Optimize minification
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    },
    // Target modern browsers for smaller bundles
    target: 'esnext',
    // Enable CSS code splitting
    cssCodeSplit: true,
    // Optimize source maps
    sourcemap: false,
    // Increase chunk size warning limit
    chunkSizeWarningLimit: 500
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false
      }
    }
  }
})
