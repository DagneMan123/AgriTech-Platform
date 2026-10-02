<template>
  <div 
    class="w-full min-h-screen flex flex-col transition-colors duration-500"
    :style="{ 
      backgroundColor: colors.bg,
      color: colors.text
    }"
  >
    <!-- Navigation -->
    <nav 
      class="backdrop-blur-md shadow-sm sticky top-0 z-50 transition-colors duration-500"
      :style="{
        backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        borderBottomColor: colors.border,
        borderBottomWidth: '1px'
      }"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <RouterLink to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">A</span>
            </div>
            <span class="text-xl font-bold" :style="{ color: colors.textHeading }">AgriConnect</span>
          </RouterLink>

          <!-- Auth & Theme Toggle Links -->
          <div class="flex items-center space-x-4">
            <!-- Theme Toggle Button -->
           <div class="theme-toggle-corner">
              <button 
           type="button" 
          class="icon-theme-btn w-10 h-10 rounded-full transition-all duration-300 hover:scale-110 flex items-center justify-center" 
           @click="toggleTheme" 
           :title="isDarkMode ? 'Switch to Light' : 'Switch to Dark'"
           :style="{
          backgroundColor: isDarkMode ? '#1f2937' : '#f1f5f9',
          color: isDarkMode ? '#9ca3af' : '#64748b'
        }"
            >
          <span class="theme-icon text-lg">{{ isDarkMode ? '🌙' : '☀️' }}</span>
          </button>
            </div>

            <RouterLink 
              to="/auth/login" 
              class="px-4 py-2 rounded-lg hover:shadow-lg transition font-semibold"
              :style="{
                backgroundColor: isDarkMode ? '#10b981' : '#22c55e',
                color: 'white'
              }"
            >
              Login
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
      <section 
        class="py-24 transition-colors duration-500" 
        :style="{ backgroundColor: isDarkMode ? '#0b0f17' : '#f8fafc' }"
        ref="featuresSection"
      >
        <div class="max-w-7xl mx-auto px-4">
          <div class="text-center mb-16">
            <h3 class="text-5xl font-bold mb-4" :style="{ color: colors.textHeading }">Powerful Features</h3>
            <p class="text-xl" :style="{ color: colors.textMuted }">Everything you need to manage and grow your agricultural business</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=200&fit=crop" alt="Online Marketplace" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Online Marketplace</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Farmers publish and sell agricultural products directly to buyers, eliminating middlemen and maximizing profits</p>
              </div>
            </div>

            <!-- Feature 2 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1574943320219-553eb213f72d?w=400&h=200&fit=crop" alt="Farm Management" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Farm Management</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Digitize farm records, crops, harvests, and livestock data for better insights and management</p>
              </div>
            </div>

            <!-- Feature 3 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=200&fit=crop" alt="Market Intelligence" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Market Intelligence</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Real-time market prices, weather forecasts, and trends for informed decision-making</p>
              </div>
            </div>

            <!-- Feature 4 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=400&h=200&fit=crop" alt="Smart Logistics" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Smart Logistics</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Connect with transport providers, track deliveries, and optimize routes in real-time</p>
              </div>
            </div>

            <!-- Feature 5 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=400&h=200&fit=crop" alt="Financial Services" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Financial Services</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Digital payments, microfinance loans, crop insurance, and investment opportunities</p>
              </div>
            </div>

            <!-- Feature 6 -->
            <div class="rounded-xl shadow-md overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2" :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: isDarkMode ? '#1f2937' : '#e5e7eb', borderWidth: '1px' }">
              <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=400&h=200&fit=crop" alt="Expert Consultation" class="w-full h-40 object-cover">
              <div class="p-8">
                <h4 class="text-2xl font-bold mb-3" :style="{ color: colors.textHeading }">Expert Consultation</h4>
                <p class="leading-relaxed" :style="{ color: colors.textMuted }">Access certified experts for personalized agricultural guidance and training programs</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Benefits Section -->
      <section 
        class="py-24 transition-all duration-500"
        :style="{ backgroundColor: isDarkMode ? '#0b0f17' : '#f8fafc' }"
      >
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
              <h3 class="text-4xl font-bold mb-8" :style="{ color: colors.textHeading }">Why AgriConnect?</h3>
              <div class="space-y-6">
                <!-- Increased Income -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div 
                      class="flex items-center justify-center h-12 w-12 rounded-xl"
                      :style="{ backgroundColor: isDarkMode ? '#1e3a1f' : '#dcfce7' }"
                    >
                      <svg :style="{ color: isDarkMode ? '#4ade80' : '#22c55e' }" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-xl font-bold mb-1" :style="{ color: colors.textHeading }">Increased Income</h4>
                    <p :style="{ color: colors.textMuted }">Earn 30-50% more by eliminating middlemen and connecting directly with buyers nationwide</p>
                  </div>
                </div>

                <!-- Better Market Access -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div 
                      class="flex items-center justify-center h-12 w-12 rounded-xl"
                      :style="{ backgroundColor: isDarkMode ? '#1e3a8a' : '#dbeafe' }"
                    >
                      <svg :style="{ color: isDarkMode ? '#60a5fa' : '#3b82f6' }" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-xl font-bold mb-1" :style="{ color: colors.textHeading }">Better Market Access</h4>
                    <p :style="{ color: colors.textMuted }">Reach thousands of quality buyers and access premium agricultural inputs and equipment</p>
                  </div>
                </div>

                <!-- Data-Driven Decisions -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div 
                      class="flex items-center justify-center h-12 w-12 rounded-xl"
                      :style="{ backgroundColor: isDarkMode ? '#422c08' : '#fef3c7' }"
                    >
                      <svg :style="{ color: isDarkMode ? '#facc15' : '#eab308' }" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-xl font-bold mb-1" :style="{ color: colors.textHeading }">Data-Driven Decisions</h4>
                    <p :style="{ color: colors.textMuted }">Make informed decisions with real-time weather data, market trends, and agricultural insights</p>
                  </div>
                </div>

                <!-- Complete Digitalization -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div 
                      class="flex items-center justify-center h-12 w-12 rounded-xl"
                      :style="{ backgroundColor: isDarkMode ? '#3730a3' : '#e9d5ff' }"
                    >
                      <svg :style="{ color: isDarkMode ? '#c084fc' : '#a855f7' }" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </div>
                  </div>
                  <div>
                    <h4 class="text-xl font-bold mb-1" :style="{ color: colors.textHeading }">Complete Digitalization</h4>
                    <p :style="{ color: colors.textMuted }">Manage farms, crops, inventory, sales, and finances digitally with automatic record-keeping</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- User Roles Section -->
      <section 
        class="py-24 transition-all duration-500"
        :style="{ backgroundColor: isDarkMode ? '#0b0f17' : '#f8fafc' }"
      >
        <div class="max-w-7xl mx-auto px-4">
          <div class="text-center mb-16">
            <h3 class="text-5xl font-bold mb-4" :style="{ color: colors.textHeading }">Built for Everyone</h3>
            <p class="text-xl" :style="{ color: colors.textMuted }">Tailored features for all stakeholders in the agricultural ecosystem</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Farmers -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#22c55e' }"
            >
              <div class="text-6xl mb-4">👨‍🌾</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Farmers</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Sell products, manage farms, request expert advice, access loans</p>
            </div>

            <!-- Buyers -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#3b82f6' }"
            >
              <div class="text-6xl mb-4">🛒</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Buyers</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Browse marketplace, place orders, track deliveries, review products</p>
            </div>

            <!-- Suppliers -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#eab308' }"
            >
              <div class="text-6xl mb-4">📦</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Suppliers</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Supply inputs, manage inventory, process orders, build reputation</p>
            </div>

            <!-- Transport -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#ef4444' }"
            >
              <div class="text-6xl mb-4">🚚</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Transport</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Manage deliveries, track vehicles, optimize routes, earn commission</p>
            </div>

            <!-- Experts -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#a855f7' }"
            >
              <div class="text-6xl mb-4">👨‍🏫</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Experts</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Provide consultation, publish articles, conduct training, earn fees</p>
            </div>

            <!-- Cooperatives -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#6366f1' }"
            >
              <div class="text-6xl mb-4">👥</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Cooperatives</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Manage members, collective sales, generate reports, build communities</p>
            </div>

            <!-- Financial -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#f97316' }"
            >
              <div class="text-6xl mb-4">💰</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Financial</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Manage loans, insurance, payments, and financial services</p>
            </div>

            <!-- Admin -->
            <div 
              class="rounded-xl p-8 shadow-md hover:shadow-xl transition-all duration-300 border-l-4"
              :style="{ backgroundColor: isDarkMode ? '#111827' : '#ffffff', borderColor: '#6b7280' }"
            >
              <div class="text-6xl mb-4">⚙️</div>
              <h4 class="text-xl font-bold mb-2" :style="{ color: colors.textHeading }">Admin</h4>
              <p class="text-sm mb-4" :style="{ color: colors.textMuted }">Manage system, users, roles, reports, and platform settings</p>
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
      <section 
        class="py-24 transition-all duration-500"
        style="background-color: #10b981;"
      >
        <div class="max-w-4xl mx-auto px-4 text-center">
          <h3 class="text-5xl font-bold mb-4 text-white">Ready to Transform Agriculture?</h3>
          <p class="text-2xl mb-4 text-white">Join thousands of farmers, buyers, and businesses already using AgriConnect</p>
          <p class="text-lg mb-10 max-w-2xl mx-auto text-white">Start growing your business today with our comprehensive platform built for the agricultural ecosystem</p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <button 
              class="inline-block px-10 py-4 text-white rounded-lg font-bold text-lg transition border-2"
              style="background-color: #22c55e; border-color: #ffffff;"
              @click="scrollToFeatures"
            >
              Explore Features
            </button>
          </div>
          <p class="text-sm mt-6 text-white">✓ No credit card required • ✓ Free tier available • ✓ 24/7 Support</p>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer 
      class="mt-20 border-t transition-all duration-500"
      :style="{ 
        backgroundColor: isDarkMode ? '#0f172a' : '#f8fafc',
        borderTopColor: colors.border
      }"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <!-- Company Info -->
          <div>
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">A</span>
              </div>
              <span class="font-bold" :style="{ color: colors.textHeading }">AgriConnect</span>
            </div>
            <p class="text-sm" :style="{ color: colors.textMuted }">Connecting farmers with markets for sustainable agriculture.</p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="font-semibold mb-4" :style="{ color: colors.textHeading }">Quick Links</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="/" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Home</a></li>
              <li><a href="/" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">About Us</a></li>
              <li><a href="/" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Contact Us</a></li>
            </ul>
          </div>

          <!-- Resources -->
          <div>
            <h4 class="font-semibold mb-4" :style="{ color: colors.textHeading }">Resources</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Documentation</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">API Reference</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">FAQ</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Blog</a></li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h4 class="font-semibold mb-4" :style="{ color: colors.textHeading }">Legal</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Privacy Policy</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Terms of Service</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Cookie Policy</a></li>
              <li><a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Disclaimer</a></li>
            </ul>
          </div>
        </div>

        <div 
          class="border-t pt-8 flex flex-col md:flex-row justify-between items-center gap-4"
          :style="{ borderTopColor: colors.border }"
        >
          <p class="text-sm" :style="{ color: colors.textMuted }">2026 AgriConnect. All rights reserved.</p>
          <div class="flex space-x-6">
            <a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Twitter</a>
            <a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Facebook</a>
            <a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">LinkedIn</a>
            <a href="#" class="transition hover:opacity-80" :style="{ color: colors.textMuted }">Instagram</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useThemeStore } from '@/stores/themeStore'

const themeStore = useThemeStore()
const featuresSection = ref<HTMLElement>()

// Use computed to reactively track the theme from store
const isDarkMode = computed(() => themeStore.isDark)

// Color schemes
const colors = computed(() => ({
  bg: isDarkMode.value ? '#0f172a' : '#ffffff',
  bgLight: isDarkMode.value ? '#1e293b' : '#f8fafc',
  bgDark: isDarkMode.value ? '#0f172a' : '#ffffff',
  text: isDarkMode.value ? '#e2e8f0' : '#1e293b',
  textHeading: isDarkMode.value ? '#f1f5f9' : '#0f172a',
  textMuted: isDarkMode.value ? '#cbd5e1' : '#64748b',
  textSecondary: isDarkMode.value ? '#94a3b8' : '#475569',
  border: isDarkMode.value ? '#334155' : '#e2e8f0',
  cardBg: isDarkMode.value ? '#0f172a' : '#ffffff',
  navBg: isDarkMode.value ? 'rgba(15, 23, 42, 0.98)' : 'rgba(255, 255, 255, 0.98)',
  navBorder: isDarkMode.value ? '#334155' : '#e2e8f0',
  btnBg: isDarkMode.value ? '#1e293b' : '#f1f5f9',
  btnText: isDarkMode.value ? '#cbd5e1' : '#64748b',
  gradient: isDarkMode.value 
    ? 'linear-gradient(135deg, #064e3b 0%, #1e293b 100%)' 
    : 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
  footerBg: isDarkMode.value ? '#0f172a' : '#1e293b',
}))

const scrollToFeatures = () => {
  featuresSection.value?.scrollIntoView({ behavior: 'smooth' })
}

const toggleTheme = () => {
  themeStore.toggleTheme()
}

onMounted(() => {
  themeStore.initializeTheme()
})
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

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #16a34a 0%, #2563eb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>