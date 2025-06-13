<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">KYC Verification Form</h3>
      <p class="text-sm text-gray-600 mt-1">Complete your identity verification to access loan services</p>
    </div>

    <div class="p-6">
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Step Indicator -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div 
              v-for="(step, index) in steps" 
              :key="index"
              class="flex items-center"
            >
              <div 
                :class="getStepClasses(index)"
                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
              >
                <svg v-if="step.completed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span v-else>{{ index + 1 }}</span>
              </div>
              <span class="ml-2 text-sm font-medium text-gray-900">{{ step.title }}</span>
              <div v-if="index < steps.length - 1" class="ml-4 w-16 h-0.5 bg-gray-300"></div>
            </div>
          </div>
        </div>

        <!-- Step 1: Basic Information -->
        <div v-if="currentStep === 0" class="space-y-4">
          <h4 class="text-md font-medium text-gray-900">Basic Information</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                Country of Residence <span class="text-red-500">*</span>
              </label>
              <select 
                id="country" 
                v-model="form.country" 
                @change="validateField('country')"
                :class="getFieldClasses('country')"
                required
              >
                <option value="">Select your country</option>
                <option v-for="country in countries" :key="country.code" :value="country.code">
                  {{ country.name }}
                </option>
              </select>
              <p v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country }}</p>
            </div>

            <div>
              <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                Preferred Language
              </label>
              <select 
                id="language" 
                v-model="form.language"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="lang in languages" :key="lang.code" :value="lang.code">
                  {{ lang.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Step 2: Verification Type -->
        <div v-if="currentStep === 1" class="space-y-4">
          <h4 class="text-md font-medium text-gray-900">Verification Type</h4>
          
          <div class="space-y-3">
            <div 
              v-for="type in verificationTypes" 
              :key="type.value"
              @click="selectVerificationType(type.value)"
              :class="getVerificationTypeClasses(type.value)"
              class="p-4 border rounded-lg cursor-pointer transition-all duration-200"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div :class="getVerificationTypeIconClasses(type.value)" class="w-8 h-8 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="type.icon"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-3">
                  <h5 class="text-sm font-medium text-gray-900">{{ type.title }}</h5>
                  <p class="text-sm text-gray-500">{{ type.description }}</p>
                </div>
                <div class="ml-auto">
                  <svg v-if="form.verification_mode === type.value" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Document Types -->
        <div v-if="currentStep === 2" class="space-y-4">
          <h4 class="text-md font-medium text-gray-900">Document Types</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div 
              v-for="doc in documentTypes" 
              :key="doc.value"
              @click="toggleDocumentType(doc.value)"
              :class="getDocumentTypeClasses(doc.value)"
              class="p-4 border rounded-lg cursor-pointer transition-all duration-200"
            >
              <div class="flex items-center">
                <input 
                  type="checkbox" 
                  :id="doc.value"
                  :value="doc.value"
                  v-model="form.supported_types"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label :for="doc.value" class="ml-3 text-sm font-medium text-gray-900">
                  {{ doc.title }}
                </label>
              </div>
              <p class="text-xs text-gray-500 mt-1">{{ doc.description }}</p>
            </div>
          </div>
        </div>

        <!-- Step 4: Review -->
        <div v-if="currentStep === 3" class="space-y-4">
          <h4 class="text-md font-medium text-gray-900">Review Your Information</h4>
          
          <div class="bg-gray-50 rounded-lg p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Country:</span>
              <span class="text-sm font-medium text-gray-900">{{ getCountryName(form.country) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Language:</span>
              <span class="text-sm font-medium text-gray-900">{{ getLanguageName(form.language) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Verification Type:</span>
              <span class="text-sm font-medium text-gray-900">{{ getVerificationTypeName(form.verification_mode) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Document Types:</span>
              <span class="text-sm font-medium text-gray-900">{{ form.supported_types.join(', ') }}</span>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between pt-6">
          <button 
            v-if="currentStep > 0"
            @click="previousStep"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Previous
          </button>
          <div v-else></div>

          <button 
            v-if="currentStep < steps.length - 1"
            @click="nextStep"
            type="button"
            :disabled="!canProceed"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
          <button 
            v-else
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            {{ isSubmitting ? 'Processing...' : 'Start Verification' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">Verification Started!</h3>
          <p class="text-sm text-gray-500 mt-2">
            Your KYC verification has been initiated. You will be redirected to complete the verification process.
          </p>
          <div class="mt-4">
            <button 
              @click="redirectToVerification"
              class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Continue to Verification
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KYCForm',
  data() {
    return {
      currentStep: 0,
      isSubmitting: false,
      showSuccessModal: false,
      errors: {},
      form: {
        country: '',
        language: 'EN',
        verification_mode: 'any',
        supported_types: ['id_card', 'passport']
      },
      steps: [
        { title: 'Basic Info', completed: false },
        { title: 'Verification Type', completed: false },
        { title: 'Documents', completed: false },
        { title: 'Review', completed: false }
      ],
      countries: [
        { code: 'NG', name: 'Nigeria' },
        { code: 'GH', name: 'Ghana' },
        { code: 'KE', name: 'Kenya' },
        { code: 'ZA', name: 'South Africa' },
        { code: 'US', name: 'United States' },
        { code: 'GB', name: 'United Kingdom' },
        { code: 'CA', name: 'Canada' },
        { code: 'AU', name: 'Australia' }
      ],
      languages: [
        { code: 'EN', name: 'English' },
        { code: 'FR', name: 'French' },
        { code: 'ES', name: 'Spanish' },
        { code: 'AR', name: 'Arabic' }
      ],
      verificationTypes: [
        {
          value: 'any',
          title: 'Complete Verification',
          description: 'Full identity verification with document and face verification',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        {
          value: 'document',
          title: 'Document Only',
          description: 'Verify your identity using official documents only',
          icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
        },
        {
          value: 'face',
          title: 'Face Verification',
          description: 'Verify your identity using facial recognition',
          icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        }
      ],
      documentTypes: [
        {
          value: 'id_card',
          title: 'National ID Card',
          description: 'Government-issued national identification card'
        },
        {
          value: 'passport',
          title: 'Passport',
          description: 'Valid passport from your country of citizenship'
        },
        {
          value: 'driving_license',
          title: 'Driving License',
          description: 'Valid driver\'s license with photo'
        },
        {
          value: 'utility_bill',
          title: 'Utility Bill',
          description: 'Recent utility bill for address verification'
        }
      ]
    }
  },
  computed: {
    canProceed() {
      switch (this.currentStep) {
        case 0:
          return this.form.country && !this.errors.country
        case 1:
          return this.form.verification_mode
        case 2:
          return this.form.supported_types.length > 0
        default:
          return true
      }
    }
  },
  methods: {
    validateField(field) {
      this.errors[field] = ''
      
      if (field === 'country' && !this.form.country) {
        this.errors.country = 'Please select your country'
      }
    },
    getFieldClasses(field) {
      const baseClasses = 'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
      return this.errors[field] 
        ? baseClasses + ' border-red-300' 
        : baseClasses + ' border-gray-300'
    },
    getStepClasses(index) {
      if (this.steps[index].completed) {
        return 'bg-green-600 text-white'
      } else if (index === this.currentStep) {
        return 'bg-blue-600 text-white'
      } else {
        return 'bg-gray-300 text-gray-600'
      }
    },
    getVerificationTypeClasses(value) {
      return this.form.verification_mode === value
        ? 'border-blue-500 bg-blue-50'
        : 'border-gray-300 hover:border-gray-400'
    },
    getVerificationTypeIconClasses(value) {
      return this.form.verification_mode === value
        ? 'bg-blue-100 text-blue-600'
        : 'bg-gray-100 text-gray-600'
    },
    getDocumentTypeClasses(value) {
      return this.form.supported_types.includes(value)
        ? 'border-blue-500 bg-blue-50'
        : 'border-gray-300 hover:border-gray-400'
    },
    selectVerificationType(value) {
      this.form.verification_mode = value
    },
    toggleDocumentType(value) {
      const index = this.form.supported_types.indexOf(value)
      if (index > -1) {
        this.form.supported_types.splice(index, 1)
      } else {
        this.form.supported_types.push(value)
      }
    },
    nextStep() {
      if (this.canProceed) {
        this.steps[this.currentStep].completed = true
        this.currentStep++
      }
    },
    previousStep() {
      if (this.currentStep > 0) {
        this.currentStep--
      }
    },
    getCountryName(code) {
      const country = this.countries.find(c => c.code === code)
      return country ? country.name : code
    },
    getLanguageName(code) {
      const language = this.languages.find(l => l.code === code)
      return language ? language.name : code
    },
    getVerificationTypeName(value) {
      const type = this.verificationTypes.find(t => t.value === value)
      return type ? type.title : value
    },
    async submitForm() {
      this.isSubmitting = true
      
      try {
        const response = await fetch('/kyc/initialize', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(this.form)
        })
        
        const data = await response.json()
        
        if (data.success) {
          this.showSuccessModal = true
          this.$emit('verification-started', data)
        } else {
          throw new Error(data.message || 'Failed to start verification')
        }
      } catch (error) {
        console.error('Error starting KYC verification:', error)
        alert('Failed to start verification: ' + error.message)
      } finally {
        this.isSubmitting = false
      }
    },
    redirectToVerification() {
      this.showSuccessModal = false
      // Redirect to verification URL or dashboard
      window.location.href = '/home'
    }
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style> 