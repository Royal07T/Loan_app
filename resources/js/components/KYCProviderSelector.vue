<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Choose KYC Provider</h3>
      <p class="text-sm text-gray-600 mt-1">Select the verification provider that best suits your needs</p>
    </div>

    <div class="p-6">
      <!-- Provider Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div 
          v-for="provider in providers" 
          :key="provider.id"
          @click="selectProvider(provider.id)"
          :class="getProviderCardClasses(provider.id)"
          class="p-4 border rounded-lg cursor-pointer transition-all duration-200 hover:shadow-md"
        >
          <!-- Provider Header -->
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <img 
                :src="provider.logo" 
                :alt="provider.name"
                class="w-8 h-8 rounded"
                @error="handleImageError"
              >
              <h4 class="ml-2 text-sm font-medium text-gray-900">{{ provider.name }}</h4>
            </div>
            <div v-if="selectedProvider === provider.id" class="text-blue-600">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>

          <!-- Provider Features -->
          <div class="space-y-2">
            <div class="flex items-center text-xs text-gray-600">
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              {{ provider.verificationTime }}
            </div>
            <div class="flex items-center text-xs text-gray-600">
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              {{ provider.successRate }}% success rate
            </div>
            <div class="flex items-center text-xs text-gray-600">
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              {{ provider.countries }} countries
            </div>
          </div>

          <!-- Provider Rating -->
          <div class="flex items-center mt-3">
            <div class="flex items-center">
              <svg 
                v-for="star in 5" 
                :key="star"
                :class="star <= provider.rating ? 'text-yellow-400' : 'text-gray-300'"
                class="w-3 h-3" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
            </div>
            <span class="ml-1 text-xs text-gray-600">{{ provider.rating }}/5</span>
          </div>

          <!-- Provider Status -->
          <div class="mt-3">
            <span :class="getProviderStatusClasses(provider.status)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
              <div :class="getProviderStatusIconClasses(provider.status)" class="w-1 h-1 rounded-full mr-1"></div>
              {{ provider.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Provider Comparison -->
      <div v-if="showComparison" class="mb-6">
        <h4 class="text-md font-medium text-gray-900 mb-4">Provider Comparison</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                <th 
                  v-for="provider in selectedProvidersForComparison" 
                  :key="provider.id"
                  class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  {{ provider.name }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="feature in comparisonFeatures" :key="feature.key">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ feature.name }}</td>
                <td 
                  v-for="provider in selectedProvidersForComparison" 
                  :key="provider.id"
                  class="px-4 py-3 text-sm text-gray-500"
                >
                  {{ provider[feature.key] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Selected Provider Details -->
      <div v-if="selectedProviderDetails" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
        <h4 class="text-md font-medium text-blue-900 mb-2">Selected Provider: {{ selectedProviderDetails.name }}</h4>
        <p class="text-sm text-blue-700 mb-3">{{ selectedProviderDetails.description }}</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-blue-600 font-medium">Verification Time:</span>
            <p class="text-blue-700">{{ selectedProviderDetails.verificationTime }}</p>
          </div>
          <div>
            <span class="text-blue-600 font-medium">Success Rate:</span>
            <p class="text-blue-700">{{ selectedProviderDetails.successRate }}%</p>
          </div>
          <div>
            <span class="text-blue-600 font-medium">Countries:</span>
            <p class="text-blue-700">{{ selectedProviderDetails.countries }}</p>
          </div>
          <div>
            <span class="text-blue-600 font-medium">Rating:</span>
            <p class="text-blue-700">{{ selectedProviderDetails.rating }}/5</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-between">
        <button 
          v-if="selectedProvider"
          @click="showComparison = !showComparison"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          {{ showComparison ? 'Hide' : 'Show' }} Comparison
        </button>
        <div v-else></div>

        <button 
          @click="proceedWithProvider"
          :disabled="!selectedProvider"
          class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Continue with {{ selectedProviderDetails?.name || 'Provider' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KYCProviderSelector',
  data() {
    return {
      selectedProvider: null,
      showComparison: false,
      providers: [
        {
          id: 'shuftipro',
          name: 'ShuftiPro',
          logo: '/images/providers/shuftipro.png',
          description: 'Fast and reliable identity verification with global coverage',
          verificationTime: '2-5 minutes',
          successRate: 98.5,
          countries: 150,
          rating: 4.8,
          status: 'active',
          features: ['Document verification', 'Face verification', 'Background checks', 'Liveness detection']
        },
        {
          id: 'smile_identity',
          name: 'Smile Identity',
          logo: '/images/providers/smile-identity.png',
          description: 'AI-powered identity verification for Africa and emerging markets',
          verificationTime: '3-7 minutes',
          successRate: 97.2,
          countries: 45,
          rating: 4.6,
          status: 'active',
          features: ['Document verification', 'Face verification', 'Biometric matching', 'Anti-fraud detection']
        },
        {
          id: 'jumio',
          name: 'Jumio',
          logo: '/images/providers/jumio.png',
          description: 'Enterprise-grade identity verification with advanced security',
          verificationTime: '1-3 minutes',
          successRate: 99.1,
          countries: 200,
          rating: 4.9,
          status: 'active',
          features: ['Document verification', 'Face verification', 'Liveness detection', 'Compliance tools']
        },
        {
          id: 'onfido',
          name: 'Onfido',
          logo: '/images/providers/onfido.png',
          description: 'Trusted identity verification for businesses worldwide',
          verificationTime: '2-4 minutes',
          successRate: 98.8,
          countries: 195,
          rating: 4.7,
          status: 'active',
          features: ['Document verification', 'Face verification', 'Biometric analysis', 'Fraud prevention']
        },
        {
          id: 'sumsub',
          name: 'Sumsub',
          logo: '/images/providers/sumsub.png',
          description: 'Comprehensive KYC/AML verification platform',
          verificationTime: '3-6 minutes',
          successRate: 97.9,
          countries: 220,
          rating: 4.5,
          status: 'active',
          features: ['Document verification', 'Face verification', 'AML screening', 'Risk assessment']
        },
        {
          id: 'veriff',
          name: 'Veriff',
          logo: '/images/providers/veriff.png',
          description: 'Real-time identity verification with instant results',
          verificationTime: '1-2 minutes',
          successRate: 98.3,
          countries: 180,
          rating: 4.4,
          status: 'active',
          features: ['Document verification', 'Face verification', 'Liveness detection', 'Instant verification']
        }
      ],
      comparisonFeatures: [
        { key: 'verificationTime', name: 'Verification Time' },
        { key: 'successRate', name: 'Success Rate' },
        { key: 'countries', name: 'Countries Supported' },
        { key: 'rating', name: 'User Rating' }
      ]
    }
  },
  computed: {
    selectedProviderDetails() {
      return this.providers.find(p => p.id === this.selectedProvider)
    },
    selectedProvidersForComparison() {
      if (!this.selectedProvider) return []
      
      const selected = this.providers.find(p => p.id === this.selectedProvider)
      const recommended = this.providers.find(p => p.id === 'jumio') // Default recommendation
      
      return selected && recommended ? [selected, recommended] : [selected]
    }
  },
  methods: {
    selectProvider(providerId) {
      this.selectedProvider = providerId
      this.$emit('provider-selected', providerId)
    },
    getProviderCardClasses(providerId) {
      return this.selectedProvider === providerId
        ? 'border-blue-500 bg-blue-50'
        : 'border-gray-300 hover:border-gray-400'
    },
    getProviderStatusClasses(status) {
      const classes = {
        'active': 'bg-green-100 text-green-800',
        'maintenance': 'bg-yellow-100 text-yellow-800',
        'inactive': 'bg-red-100 text-red-800'
      }
      return classes[status] || classes.inactive
    },
    getProviderStatusIconClasses(status) {
      const classes = {
        'active': 'bg-green-400',
        'maintenance': 'bg-yellow-400',
        'inactive': 'bg-red-400'
      }
      return classes[status] || classes.inactive
    },
    handleImageError(event) {
      // Fallback to a default icon if image fails to load
      event.target.style.display = 'none'
    },
    proceedWithProvider() {
      if (this.selectedProvider) {
        this.$emit('proceed', this.selectedProvider)
      }
    }
  }
}
</script>

<style scoped>
.provider-card {
  transition: all 0.2s ease-in-out;
}

.provider-card:hover {
  transform: translateY(-2px);
}

.provider-card.selected {
  border-color: #3b82f6;
  background-color: #eff6ff;
}
</style> 