<template>
  <div class="w-full max-w-xl bg-white rounded-xl shadow-sm border border-slate-100 p-8 sm:p-10 space-y-6 mx-auto">

    <!-- Role Selection Step -->
    <template v-if="!roleSelected">
      <div class="space-y-4">
        <h2 class="text-xl font-bold text-slate-800">Create Account</h2>
        <p class="text-sm text-slate-500">Please select your role to proceed with registration.</p>
        
        <div class="space-y-2 pt-2">
          <label class="block text-sm font-medium text-slate-700">Select Your Role *</label>
          <select
            v-model="selectedRoleValue"
            @change="handleRoleDropdownChange"
            class="w-full px-4 py-3 bg-slate-50  border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          >
            <option value="" disabled>-- Choose a Role --</option>
            <option v-for="role in roles" :key="role.value" :value="role.value">
              {{ role.emoji }} {{ role.label }}
            </option>
          </select>
        </div>
        
        <button
          v-if="selectedRoleValue"
          @click="selectRole(selectedRoleValue)"
          class="w-full mt-4 py-2 px-6 bg-green-600 hover:bg-green-700 text-white font-medium rounded-full transition-colors shadow-sm"
        >
          Continue
        </button>
      </div>
    </template>

    <!-- Registration Form Step -->
    <form v-else @submit.prevent="handleRegister" enctype="multipart/form-data" class="space-y-5">
      
      <!-- Back Button -->
      <button type="button" @click="roleSelected = false" class="text-sm text-green-600 hover:text-green-700 font-medium flex items-center space-x-1 mb-2">
        <span>← Back to Role Selection</span>
      </button>

      <h2 class="text-xl font-bold text-slate-800 capitalize">Register as {{ selectedRole }}</h2>

      <!-- Farmer Fields -->
      <div v-if="selectedRole === 'farmer'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Full Name </label>
          <input
            v-model="form.full_name"
            type="text"
            placeholder="Enter your full name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.full_name" class="text-rose-500 text-xs mt-1 block">{{ errors.full_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="+251 9XX XXX XXX"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email (Optional)</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="your@email.com"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
          </div>
        </div>
      </div>

      <!-- Buyer Fields -->
      <div v-if="selectedRole === 'buyer'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Business Name </label>
          <input
            v-model="form.business_name"
            type="text"
            placeholder="Enter business name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.business_name" class="text-rose-500 text-xs mt-1 block">{{ errors.business_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="+251 9XX XXX XXX"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email </label>
            <input
              v-model="form.email"
              type="email"
              placeholder="your@email.com"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
            <span v-if="errors.email" class="text-rose-500 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Supplier Fields -->
      <div v-if="selectedRole === 'supplier'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Business Name </label>
          <input
            v-model="form.business_name"
            type="text"
            placeholder="Enter business name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.business_name" class="text-rose-500 text-xs mt-1 block">{{ errors.business_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input v-model="form.phone" type="tel" placeholder="+251 9XX XXX XXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email </label>
            <input v-model="form.email" type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.email" class="text-rose-500 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Transport Fields -->
      <div v-if="selectedRole === 'transport'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Full Name </label>
          <input
            v-model="form.company_name"
            type="text"
            placeholder="Enter company name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.company_name" class="text-rose-500 text-xs mt-1 block">{{ errors.company_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input v-model="form.phone" type="tel" placeholder="+251 9XX XXX XXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email (Optional)</label>
            <input v-model="form.email" type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
          </div>
        </div>
      </div>

      <!-- Expert Fields -->
      <div v-if="selectedRole === 'expert'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Full Name </label>
          <input
            v-model="form.full_name"
            type="text"
            placeholder="Enter full name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.full_name" class="text-rose-500 text-xs mt-1 block">{{ errors.full_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input v-model="form.phone" type="tel" placeholder="+251 9XX XXX XXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email </label>
            <input v-model="form.email" type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.email" class="text-rose-500 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Financial Fields -->
      <div v-if="selectedRole === 'financial'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Institution Name </label>
          <input
            v-model="form.institution_name"
            type="text"
            placeholder="Enter institution name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.institution_name" class="text-rose-500 text-xs mt-1 block">{{ errors.institution_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input v-model="form.phone" type="tel" placeholder="+251 9XX XXX XXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Official Email </label>
            <input v-model="form.email" type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.email" class="text-rose-500 text-xs mt-1 block">{{ errors.email[0] }}</span>
          </div>
        </div>
      </div>

      <!-- Cooperative Fields -->
      <div v-if="selectedRole === 'cooperative'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Cooperative Name </label>
          <input
            v-model="form.cooperative_name"
            type="text"
            placeholder="Enter cooperative name"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.cooperative_name" class="text-rose-500 text-xs mt-1 block">{{ errors.cooperative_name[0] }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Phone </label>
            <input v-model="form.phone" type="tel" placeholder="+251 9XX XXX XXX" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
            <span v-if="errors.phone" class="text-rose-500 text-xs mt-1 block">{{ errors.phone[0] }}</span>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email (Optional)</label>
            <input v-model="form.email" type="email" placeholder="your@email.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all" />
          </div>
        </div>
      </div>

      <!-- Common Address Fields -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Address </label>
          <input
            v-model="form.address"
            type="text"
            placeholder="Street, city, kebele"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.address" class="text-rose-500 text-xs mt-1 block">{{ errors.address[0] }}</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Region </label>
          <input
            v-model="form.region"
            type="text"
            placeholder="e.g., Oromia, Amhara"
            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
          />
          <span v-if="errors.region" class="text-rose-500 text-xs mt-1 block">{{ errors.region[0] }}</span>
        </div>
      </div>

      <!-- Document Section -->
      <div class="border-t border-slate-100 pt-4 space-y-3">
        <h3 class="text-sm font-semibold text-slate-800">Verification Document</h3>
        
        <div v-if="selectedRole === 'farmer'">
          <label class="block text-xs text-slate-500 mb-1">Kebele ID or Farmer ID Card </label>
          <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
            <input type="file" @change="handleFileUpload('kebele_id_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-farmer" />
            <label for="file-farmer" class="cursor-pointer text-xs text-slate-600">{{ fileNames.kebele_id_document || 'Click to upload document' }}</label>
          </div>
          <span v-if="errors.kebele_id_document" class="text-rose-500 text-xs mt-1 block">{{ errors.kebele_id_document[0] }}</span>
        </div>

        <div v-if="selectedRole === 'buyer'" class="space-y-3">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Trade License </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('trade_license_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-buyer-trade" />
              <label for="file-buyer-trade" class="cursor-pointer text-xs text-slate-600">{{ fileNames.trade_license_document || 'Click to upload trade license' }}</label>
            </div>
            <span v-if="errors.trade_license_document" class="text-rose-500 text-xs mt-1 block">{{ errors.trade_license_document[0] }}</span>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">TIN Document </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('tin_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-buyer-tin" />
              <label for="file-buyer-tin" class="cursor-pointer text-xs text-slate-600">{{ fileNames.tin_document || 'Click to upload TIN document' }}</label>
            </div>
            <span v-if="errors.tin_document" class="text-rose-500 text-xs mt-1 block">{{ errors.tin_document[0] }}</span>
          </div>
        </div>

        <div v-if="selectedRole === 'supplier'" class="space-y-3">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Business License </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('business_license_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-sup-biz" />
              <label for="file-sup-biz" class="cursor-pointer text-xs text-slate-600">{{ fileNames.business_license_document || 'Click to upload business license' }}</label>
            </div>
            <span v-if="errors.business_license_document" class="text-rose-500 text-xs mt-1 block">{{ errors.business_license_document[0] }}</span>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Sectoral Clearance Document </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('sectoral_clearance_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-sup-clear" />
              <label for="file-sup-clear" class="cursor-pointer text-xs text-slate-600">{{ fileNames.sectoral_clearance_document || 'Click to upload sectoral clearance' }}</label>
            </div>
            <span v-if="errors.sectoral_clearance_document" class="text-rose-500 text-xs mt-1 block">{{ errors.sectoral_clearance_document[0] }}</span>
          </div>
        </div>

        <div v-if="selectedRole === 'transport'" class="space-y-3">
          <div>
            <label class="block text-xs text-slate-500 mb-1">Driving License </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('driving_license_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-trans-drive" />
              <label for="file-trans-drive" class="cursor-pointer text-xs text-slate-600">{{ fileNames.driving_license_document || 'Click to upload driving license' }}</label>
            </div>
            <span v-if="errors.driving_license_document" class="text-rose-500 text-xs mt-1 block">{{ errors.driving_license_document[0] }}</span>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">Vehicle Bluebook </label>
            <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
              <input type="file" @change="handleFileUpload('vehicle_bluebook_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-trans-blue" />
              <label for="file-trans-blue" class="cursor-pointer text-xs text-slate-600">{{ fileNames.vehicle_bluebook_document || 'Click to upload vehicle bluebook' }}</label>
            </div>
            <span v-if="errors.vehicle_bluebook_document" class="text-rose-500 text-xs mt-1 block">{{ errors.vehicle_bluebook_document[0] }}</span>
          </div>
        </div>

        <div v-if="selectedRole === 'expert'">
          <label class="block text-xs text-slate-500 mb-1">Degree/Diploma Certificate </label>
          <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
            <input type="file" @change="handleFileUpload('degree_certificate_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-exp-cert" />
            <label for="file-exp-cert" class="cursor-pointer text-xs text-slate-600">{{ fileNames.degree_certificate_document || 'Click to upload certificate' }}</label>
          </div>
          <span v-if="errors.degree_certificate_document" class="text-rose-500 text-xs mt-1 block">{{ errors.degree_certificate_document[0] }}</span>
        </div>

        <div v-if="selectedRole === 'financial'">
          <label class="block text-xs text-slate-500 mb-1">NBE License or Authorization </label>
          <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
            <input type="file" @change="handleFileUpload('nbe_license_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-fin-nbe" />
            <label for="file-fin-nbe" class="cursor-pointer text-xs text-slate-600">{{ fileNames.nbe_license_document || 'Click to upload NBE license' }}</label>
          </div>
          <span v-if="errors.nbe_license_document" class="text-rose-500 text-xs mt-1 block">{{ errors.nbe_license_document[0] }}</span>
        </div>

        <div v-if="selectedRole === 'cooperative'">
          <label class="block text-xs text-slate-500 mb-1">Cooperative Registration Certificate </label>
          <div class="border border-dashed border-slate-300 rounded-lg p-3 text-center bg-slate-50 hover:bg-slate-100 cursor-pointer">
            <input type="file" @change="handleFileUpload('registration_certificate_document', $event)" accept=".pdf,.jpg,.jpeg,.png" class="hidden" id="file-coop-reg" />
            <label for="file-coop-reg" class="cursor-pointer text-xs text-slate-600">{{ fileNames.registration_certificate_document || 'Click to upload registration' }}</label>
          </div>
          <span v-if="errors.registration_certificate_document" class="text-rose-500 text-xs mt-1 block">{{ errors.registration_certificate_document[0] }}</span>
        </div>
      </div>

      <!-- Password Section -->
      <div class="border-t border-slate-100 pt-4 space-y-4">
        <h3 class="text-sm font-semibold text-slate-800">Security</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Password </label>
            <input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
            <span v-if="errors.password" class="text-rose-500 text-xs mt-1 block">{{ errors.password[0] }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password </label>
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="••••••••"
              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition-all"
            />
            <span v-if="errors.password_confirmation" class="text-rose-500 text-xs mt-1 block">{{ errors.password_confirmation[0] }}</span>
          </div>
        </div>
      </div>

      <!-- API Error Banner -->
      <div v-if="apiError" class="p-3 bg-rose-50 border border-rose-100 rounded-lg">
        <p class="text-rose-700 text-sm">{{ apiError }}</p>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-2 py-2 px-6 bg-green-600 hover:bg-green-700 text-white font-medium rounded-full transition-colors shadow-sm disabled:opacity-50"
      >
        {{ loading ? 'Creating Account...' : 'Register' }}
      </button>

      <!-- Login Link -->
      <div class="text-center pt-2">
        <p class="text-sm text-slate-500">
          Already have an account? 
          <router-link to="/auth/login" class="text-green-600 hover:text-green-700 font-medium">Login</router-link>
        </p>
      </div>
    </form>

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import apiClient from '@/api/config'

const router = useRouter()
const authStore = useAuthStore()

const roles = [
  { value: 'farmer', label: 'Farmer', emoji: '👨‍🌾' },
  { value: 'buyer', label: 'Buyer (Merchant)', emoji: '🛒' },
  { value: 'supplier', label: 'Supplier', emoji: '🏭' },
  { value: 'transport', label: 'Transport Provider', emoji: '🚚' },
  { value: 'expert', label: 'Agricultural Expert', emoji: '👨‍⚕️' },
  { value: 'financial', label: 'Financial Institution', emoji: '🏦' },
  { value: 'cooperative', label: 'Cooperative', emoji: '🤝' },
]

const roleSelected = ref(false)
const selectedRole = ref('')
const selectedRoleValue = ref('')
const loading = ref(false)
const apiError = ref('')

const form = ref<Record<string, any>>({
  full_name: '',
  business_name: '',
  company_name: '',
  institution_name: '',
  cooperative_name: '',
  phone: '',
  email: '',
  address: '',
  region: '',
  password: '',
  password_confirmation: '',
  role: '',
})

const errors = ref<Record<string, string[]>>({})
const fileNames = ref<Record<string, string>>({})

const selectRole = (role: string) => {
  selectedRole.value = role
  form.value.role = role
  roleSelected.value = true
  errors.value = {}
  apiError.value = ''
}

const handleRoleDropdownChange = (event: any) => {
  selectedRoleValue.value = event.target.value
}

const handleFileUpload = (fieldName: string, event: any) => {
  const file = event.target.files?.[0]
  if (file) {
    fileNames.value[fieldName] = file.name
    form.value[fieldName] = file
  }
}

const handleRegister = async () => {
  loading.value = true
  apiError.value = ''
  errors.value = {}

  try {
    // Validate that required fields are filled
    const role = form.value.role
    if (!form.value.phone) {
      apiError.value = 'Phone number is required'
      loading.value = false
      return
    }
    if (!form.value.address) {
      apiError.value = 'Address is required'
      loading.value = false
      return
    }
    if (!form.value.region) {
      apiError.value = 'Region is required'
      loading.value = false
      return
    }
    if (!form.value.password) {
      apiError.value = 'Password is required'
      loading.value = false
      return
    }
    if (form.value.password !== form.value.password_confirmation) {
      apiError.value = 'Passwords do not match'
      loading.value = false
      return
    }

    // Validate role-specific fields
    if (role === 'farmer' && !form.value.full_name) {
      apiError.value = 'Full name is required for farmer registration'
      loading.value = false
      return
    }
    if (role === 'buyer' && !form.value.business_name) {
      apiError.value = 'Business name is required for buyer registration'
      loading.value = false
      return
    }
    if (role === 'supplier' && !form.value.business_name) {
      apiError.value = 'Business name is required for supplier registration'
      loading.value = false
      return
    }
    if (role === 'transport' && !form.value.company_name) {
      apiError.value = 'Company name is required for transport registration'
      loading.value = false
      return
    }
    if (role === 'expert' && !form.value.full_name) {
      apiError.value = 'Full name is required for expert registration'
      loading.value = false
      return
    }
    if (role === 'financial' && !form.value.institution_name) {
      apiError.value = 'Institution name is required for financial registration'
      loading.value = false
      return
    }
    if (role === 'cooperative' && !form.value.cooperative_name) {
      apiError.value = 'Cooperative name is required for cooperative registration'
      loading.value = false
      return
    }

    const formData = new FormData()

    // Add all text fields - ALWAYS include these
    formData.append('role', form.value.role)
    formData.append('phone', form.value.phone)
    formData.append('address', form.value.address)
    formData.append('region', form.value.region)
    formData.append('password', form.value.password)
    formData.append('password_confirmation', form.value.password_confirmation)

    // Add role-specific fields - ALWAYS send for the selected role
    if (role === 'farmer') {
      formData.append('full_name', form.value.full_name)
    }
    if (role === 'buyer') {
      formData.append('business_name', form.value.business_name)
    }
    if (role === 'supplier') {
      formData.append('business_name', form.value.business_name)
    }
    if (role === 'transport') {
      formData.append('company_name', form.value.company_name)
    }
    if (role === 'expert') {
      formData.append('full_name', form.value.full_name)
    }
    if (role === 'financial') {
      formData.append('institution_name', form.value.institution_name)
    }
    if (role === 'cooperative') {
      formData.append('cooperative_name', form.value.cooperative_name)
    }

    // Add email if present (it's required for some roles, optional for others)
    if (form.value.email) {
      formData.append('email', form.value.email)
    }

    // Add file uploads
    if (form.value.kebele_id_document) formData.append('kebele_id_document', form.value.kebele_id_document)
    if (form.value.trade_license_document) formData.append('trade_license_document', form.value.trade_license_document)
    if (form.value.tin_document) formData.append('tin_document', form.value.tin_document)
    if (form.value.business_license_document) formData.append('business_license_document', form.value.business_license_document)
    if (form.value.sectoral_clearance_document) formData.append('sectoral_clearance_document', form.value.sectoral_clearance_document)
    if (form.value.driving_license_document) formData.append('driving_license_document', form.value.driving_license_document)
    if (form.value.vehicle_bluebook_document) formData.append('vehicle_bluebook_document', form.value.vehicle_bluebook_document)
    if (form.value.degree_certificate_document) formData.append('degree_certificate_document', form.value.degree_certificate_document)
    if (form.value.nbe_license_document) formData.append('nbe_license_document', form.value.nbe_license_document)
    if (form.value.registration_certificate_document) formData.append('registration_certificate_document', form.value.registration_certificate_document)

    console.log('Form data being sent:', {
      role: form.value.role,
      phone: form.value.phone,
      address: form.value.address,
      region: form.value.region,
      email: form.value.email,
      full_name: form.value.full_name,
      business_name: form.value.business_name,
      company_name: form.value.company_name
    })

    // Log FormData contents
    console.log('FormData contents:')
    for (let [key, value] of formData.entries()) {
      if (value instanceof File) {
        console.log(`  ${key}: [File] ${value.name}`)
      } else {
        console.log(`  ${key}: ${value}`)
      }
    }

    const response = await apiClient.post('/auth/register', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const { user, token } = response.data
    authStore.token = token
    localStorage.setItem('auth_token', token)
    localStorage.setItem('user_role', user.role)
    authStore.user = user

    router.push('/verification-pending')
  } catch (err: any) {
    console.error('Registration error:', err)
    
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      console.log('Validation errors:', errors.value)
    }
    
    apiError.value = err.response?.data?.message || 'Registration failed. Please check your inputs and try again.'
    console.log('API Error:', apiError.value)
  } finally {
    loading.value = false
  }
}
</script>