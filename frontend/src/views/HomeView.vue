<template>
  <div class="w-full bg-white min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <RouterLink to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">A</span>
            </div>
            <span class="text-xl font-bold text-gray-900">AgriConnect</span>
          </RouterLink>

          <!-- Auth Links -->
          <div class="flex items-center space-x-4">
            <RouterLink 
              to="/auth/login" 
              class="text-gray-700 hover:text-green-600 font-medium transition"
            >
              Login
            </RouterLink>
            <RouterLink 
              to="/auth/register" 
              class="px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg hover:shadow-lg transition"
            >
              Sign Up
            </RouterLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
    <!-- Hero Section with Background Image -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-16">
      <div 
        class="absolute inset-0 bg-cover bg-center"
        :style="{
          backgroundImage: 'url(https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=1920&h=1080&fit=crop)',
          backgroundAttachment: 'fixed'
        }"
      >
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/70"></div>
      </div>
      
      <div class="relative z-10 text-center text-white max-w-5xl px-4 py-20 animate-fade-in-up">
        <h2 class="text-5xl md:text-7xl font-bold mb-6 leading-tight">
          Smart Agriculture Platform
        </h2>
        <p class="text-xl md:text-2xl mb-4 text-gray-200 font-light">
          Connect farmers, buyers, suppliers, and experts in one integrated digital ecosystem
        </p>
        <p class="text-lg md:text-xl text-gray-300 mb-10">
          Transforming agriculture through technology, transparency, and collaboration
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link 
            to="/auth/register" 
            class="inline-block px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:shadow-2xl font-bold text-lg transition transform hover:scale-105 duration-200"
          >
            Get Started Free
          </router-link>
          <button 
            class="inline-block px-8 py-4 bg-white/20 text-white rounded-lg hover:bg-white/30 font-bold text-lg transition backdrop-blur border border-white/40 duration-200"
            @click="scrollToFeatures"
          >
            Learn More
          </button>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 animate-bounce">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white" ref="featuresSection">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
          <h3 class="text-5xl font-bold text-gray-900 mb-4">Powerful Features</h3>
          <p class="text-xl text-gray-600">Everything you need to manage and grow your agricultural business</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=200&fit=crop" alt="Online Marketplace" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Online Marketplace</h4>
              <p class="text-gray-600 leading-relaxed">Farmers publish and sell agricultural products directly to buyers, eliminating middlemen and maximizing profits</p>
            </div>
          </div>

          <!-- Feature 2 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=400&h=200&fit=crop" alt="Farm Management" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Farm Management</h4>
              <p class="text-gray-600 leading-relaxed">Digitize farm records, crops, harvests, and livestock data for better insights and management</p>
            </div>
          </div>

          <!-- Feature 3 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=200&fit=crop" alt="Market Intelligence" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Market Intelligence</h4>
              <p class="text-gray-600 leading-relaxed">Real-time market prices, weather forecasts, and trends for informed decision-making</p>
            </div>
          </div>

          <!-- Feature 4 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=400&h=200&fit=crop" alt="Smart Logistics" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Smart Logistics</h4>
              <p class="text-gray-600 leading-relaxed">Connect with transport providers, track deliveries, and optimize routes in real-time</p>
            </div>
          </div>

          <!-- Feature 5 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=400&h=200&fit=crop" alt="Financial Services" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Financial Services</h4>
              <p class="text-gray-600 leading-relaxed">Digital payments, microfinance loans, crop insurance, and investment opportunities</p>
            </div>
          </div>

          <!-- Feature 6 -->
          <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=200&fit=crop" alt="Expert Consultation" class="w-full h-40 object-cover">
            <div class="p-8">
              <h4 class="text-2xl font-bold text-gray-900 mb-3">Expert Consultation</h4>
              <p class="text-gray-600 leading-relaxed">Access certified experts for personalized agricultural guidance and training programs</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-24 bg-white">
      <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
          <div>
            <img 
              src="https://images.unsplash.com/photo-1560493676-04071c5f467b?w=600&h=600&fit=crop" 
              alt="Farmer with Produce" 
              class="rounded-2xl shadow-2xl w-full"
            >
          </div>
          <div>
            <h3 class="text-4xl font-bold text-gray-900 mb-8">Why AgriConnect?</h3>
            <div class="space-y-6">
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div>
                  <h4 class="text-xl font-bold text-gray-900 mb-1">Increased Income</h4>
                  <p class="text-gray-600">Earn 30-50% more by eliminating middlemen and connecting directly with buyers nationwide</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div>
                  <h4 class="text-xl font-bold text-gray-900 mb-1">Better Market Access</h4>
                  <p class="text-gray-600">Reach thousands of quality buyers and access premium agricultural inputs and equipment</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div>
                  <h4 class="text-xl font-bold text-gray-900 mb-1">Data-Driven Decisions</h4>
                  <p class="text-gray-600">Make informed decisions with real-time weather data, market trends, and agricultural insights</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-purple-100">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div>
                  <h4 class="text-xl font-bold text-gray-900 mb-1">Complete Digitalization</h4>
                  <p class="text-gray-600">Manage farms, crops, inventory, sales, and finances digitally with automatic record-keeping</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- User Roles Section -->
    <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
          <h3 class="text-5xl font-bold text-gray-900 mb-4">Built for Everyone</h3>
          <p class="text-xl text-gray-600">Tailored features for all stakeholders in the agricultural ecosystem</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Farmers -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
            <div class="text-6xl mb-4">👨‍🌾</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Farmers</h4>
            <p class="text-gray-600 text-sm mb-4">Sell products, manage farms, request expert advice, access loans</p>
          </div>

          <!-- Buyers -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
            <div class="text-6xl mb-4">🛒</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Buyers</h4>
            <p class="text-gray-600 text-sm mb-4">Browse marketplace, place orders, track deliveries, review products</p>
          </div>

          <!-- Suppliers -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-yellow-500">
            <div class="text-6xl mb-4">📦</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Suppliers</h4>
            <p class="text-gray-600 text-sm mb-4">Supply inputs, manage inventory, process orders, build reputation</p>
          </div>

          <!-- Transport -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-red-500">
            <div class="text-6xl mb-4">🚚</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Transport</h4>
            <p class="text-gray-600 text-sm mb-4">Manage deliveries, track vehicles, optimize routes, earn commission</p>
          </div>

          <!-- Experts -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-purple-500">
            <div class="text-6xl mb-4">👨‍🏫</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Experts</h4>
            <p class="text-gray-600 text-sm mb-4">Provide consultation, publish articles, conduct training, earn fees</p>
          </div>

          <!-- Cooperatives -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-indigo-500">
            <div class="text-6xl mb-4">👥</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Cooperatives</h4>
            <p class="text-gray-600 text-sm mb-4">Manage members, collective sales, generate reports, build communities</p>
          </div>

          <!-- Financial -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-orange-500">
            <div class="text-6xl mb-4">💰</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Financial</h4>
            <p class="text-gray-600 text-sm mb-4">Manage loans, insurance, payments, and financial services</p>
          </div>

          <!-- Admin -->
          <div class="bg-white rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4 border-gray-500">
            <div class="text-6xl mb-4">⚙️</div>
            <h4 class="text-xl font-bold text-gray-900 mb-2">Admin</h4>
            <p class="text-gray-600 text-sm mb-4">Manage system, users, roles, reports, and platform settings</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section 
      class="py-24 bg-cover bg-center relative"
      :style="{
        backgroundImage: 'url(https://images.unsplash.com/photo-1625246333195-78d9c38ad576?w=1920&h=600&fit=crop)'
      }"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/60"></div>
      <div class="relative z-10 max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="text-5xl md:text-6xl font-bold text-green-400 mb-2">10K+</div>
            <p class="text-xl text-gray-200">Farmers Connected</p>
            <p class="text-sm text-gray-400 mt-2">Growing daily</p>
          </div>
          <div class="text-center">
            <div class="text-5xl md:text-6xl font-bold text-blue-400 mb-2">5K+</div>
            <p class="text-xl text-gray-200">Active Buyers</p>
            <p class="text-sm text-gray-400 mt-2">Making purchases</p>
          </div>
          <div class="text-center">
            <div class="text-5xl md:text-6xl font-bold text-yellow-400 mb-2">2K+</div>
            <p class="text-xl text-gray-200">Suppliers</p>
            <p class="text-sm text-gray-400 mt-2">Quality inputs</p>
          </div>
          <div class="text-center">
            <div class="text-5xl md:text-6xl font-bold text-purple-400 mb-2">50M+</div>
            <p class="text-xl text-gray-200">Transaction Value</p>
            <p class="text-sm text-gray-400 mt-2">This year</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-24 bg-gradient-to-r from-green-600 to-green-700">
      <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h3 class="text-5xl font-bold mb-4">Ready to Transform Agriculture?</h3>
        <p class="text-2xl mb-4 text-green-100">Join thousands of farmers, buyers, and businesses already using AgriConnect</p>
        <p class="text-lg text-green-200 mb-10 max-w-2xl mx-auto">Start growing your business today with our comprehensive platform built for the agricultural ecosystem</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link 
            to="/auth/register" 
            class="inline-block px-10 py-4 bg-white text-green-600 rounded-lg hover:bg-gray-100 font-bold text-lg transition transform hover:scale-105 duration-200 hover:shadow-lg"
          >
            Get Started Free
          </router-link>
          <button 
            class="inline-block px-10 py-4 bg-green-700 text-white rounded-lg hover:bg-green-800 font-bold text-lg transition border-2 border-white duration-200"
            @click="scrollToFeatures"
          >
            Explore Features
          </button>
        </div>
        <p class="text-sm text-green-200 mt-6">✓ No credit card required • ✓ Free tier available • ✓ 24/7 Support</p>
      </div>
    </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <!-- Company Info -->
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">A</span>
              </div>
              <span class="text-white font-bold">AgriConnect</span>
            </div>
            <p class="text-sm text-gray-500">Connecting farmers with markets for sustainable agriculture.</p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="text-white font-semibold mb-4">Quick Links</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="/" class="hover:text-white transition">Home</a></li>
              <li><a href="/" class="hover:text-white transition">About Us</a></li>
              <li><a href="/" class="hover:text-white transition">Contact Us</a></li>
            </ul>
          </div>

          <!-- Resources -->
          <div>
            <h4 class="text-white font-semibold mb-4">Resources</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Documentation</a></li>
              <li><a href="#" class="hover:text-white transition">API Reference</a></li>
              <li><a href="#" class="hover:text-white transition">FAQ</a></li>
              <li><a href="#" class="hover:text-white transition">Blog</a></li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h4 class="text-white font-semibold mb-4">Legal</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
              <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
              <li><a href="#" class="hover:text-white transition">Cookie Policy</a></li>
              <li><a href="#" class="hover:text-white transition">Disclaimer</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex justify-between items-center">
          <p class="text-sm text-gray-500">&copy; 2026 AgriConnect. All rights reserved.</p>
          <div class="flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-white transition">Twitter</a>
            <a href="#" class="text-gray-400 hover:text-white transition">Facebook</a>
            <a href="#" class="text-gray-400 hover:text-white transition">LinkedIn</a>
            <a href="#" class="text-gray-400 hover:text-white transition">Instagram</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const featuresSection = ref<HTMLElement>()

const scrollToFeatures = () => {
  featuresSection.value?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<style scoped>
/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes bounce-soft {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.animate-slide-in {
  animation: slideInLeft 0.8s ease-out both;
}

.animate-bounce {
  animation: bounce-soft 2s infinite;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Parallax effect for hero */
section:first-of-type {
  background-attachment: fixed;
}

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #16a34a 0%, #2563eb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
