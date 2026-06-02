import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
  ],

  // Source root is the theme directory itself
  root: resolve(__dirname),

  // Entry point — the Vue SPA bootstrap
  build: {
    // Output directly into the theme's assets folder
    // WordPress will enqueue from here via wp_enqueue_script()
    outDir: resolve(__dirname, 'assets'),
    emptyOutDir: false, // Don't wipe existing assets (CSS, images)

    // Generate manifest.json so functions.php can resolve hashed filenames
    manifest: true,

    rollupOptions: {
      input: {
        // Main entry — functions.php reads 'src/main.js' key from manifest
        'src/main.js': resolve(__dirname, 'src/main.js'),
      },
      output: {
        // Organize JS chunks into js/ subdirectory
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'css/[name]-[hash][extname]'
          }
          return 'img/[name]-[hash][extname]'
        },
      },
    },

    // Target modern browsers (no IE11 polyfills needed)
    target: 'es2020',

    // Minify in production
    minify: 'esbuild',

    // Source maps for debugging (disable in production if preferred)
    sourcemap: false,
  },

  // Dev server — proxies WordPress backend during development
  server: {
    port: 3000,
    proxy: {
      // Proxy AJAX calls to local WordPress
      '/wp-admin': 'http://localhost:8080',
      '/wp-json':  'http://localhost:8080',
    },
    // HMR cors for WP integration
    cors: true,
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
})
