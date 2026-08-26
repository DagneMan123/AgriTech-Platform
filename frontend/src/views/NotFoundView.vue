<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center px-4 py-12">
    <div class="text-center max-w-md">
      <!-- 404 Illustration -->
      <div class="mb-8">
        <div class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-gray-300 to-gray-400">
          404
        </div>
      </div>

      <!-- Content -->
      <h1 class="text-4xl font-bold text-gray-900 mb-3">
        Page Not Found
      </h1>

      <p class="text-lg text-gray-600 mb-8">
        The page you are looking for doesn't exist or has been moved. Let's get you back on track.
      </p>

      <!-- Helpful Links -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <p class="text-sm text-gray-600 mb-4">Popular Pages:</p>
        <div class="flex flex-wrap gap-3 justify-center">
          <router-link
            to="/"
            class="px-4 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition text-sm font-medium"
          >
            Home
          </router-link>
          <router-link
            to="/auth/login"
            class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition text-sm font-medium"
          >
            Login
          </router-link>
          <router-link
            to="/auth/register"
            class="px-4 py-2 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition text-sm font-medium"
          >
            Register
          </router-link>
        </div>
      </div>

      <!-- Primary Button -->
      <router-link
        to="/"
        class="inline-block bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-3 px-8 rounded-lg transition shadow-lg hover:shadow-xl transform hover:scale-105"
      >
        ← Go Home
      </router-link>

      <!-- Support Text -->
      <p class="text-sm text-gray-500 mt-8">
        Need help? <a href="mailto:support@agritech.com" class="text-green-600 hover:text-green-700 font-semibold">Contact Support</a>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
// This component renders a professional 404 page
</script>

<style scoped>
/* Smooth transitions */
a {
  transition: all 0.3s ease;
}
</style>
