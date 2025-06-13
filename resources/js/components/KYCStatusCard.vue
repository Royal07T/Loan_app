<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">KYC Verification Status</h3>
    </div>
    
    <div class="p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="statusIconClasses" class="w-12 h-12 rounded-full flex items-center justify-center">
              <svg v-if="status === 'verified'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg v-else-if="status === 'pending'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <svg v-else-if="status === 'rejected'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <h4 class="text-lg font-medium text-gray-900">{{ statusTitle }}</h4>
            <p class="text-sm text-gray-500">{{ statusDescription }}</p>
            <p v-if="expiresAt" class="text-xs text-gray-400 mt-1">
              Expires: {{ formatDate(expiresAt) }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center space-x-3">
          <button 
            v-if="status === 'not_started' || status === 'rejected'"
            @click="startKYC"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ status === 'rejected' ? 'Resubmit KYC' : 'Start KYC' }}
          </button>
          
          <span v-else-if="status === 'pending'" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
            <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            In Progress
          </span>
          
          <span v-else-if="status === 'verified'" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Verified
          </span>
        </div>
      </div>

      <!-- Rejection Details -->
      <div v-if="status === 'rejected' && rejectionDetails" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
        <h5 class="text-sm font-medium text-red-800 mb-2">Rejection Reason</h5>
        <p class="text-sm text-red-700">{{ rejectionDetails.reason }}</p>
        <p v-if="rejectionDetails.notes" class="text-sm text-red-600 mt-1">{{ rejectionDetails.notes }}</p>
        <div class="mt-3">
          <button 
            @click="showResubmitModal = true"
            class="text-sm text-red-600 hover:text-red-800 underline"
          >
            View full details and resubmit →
          </button>
        </div>
      </div>

      <!-- Progress Bar for Pending Status -->
      <div v-if="status === 'pending'" class="mt-4">
        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
          <span>Verification Progress</span>
          <span>{{ progressPercentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-500"
            :style="{ width: progressPercentage + '%' }"
          ></div>
        </div>
        <p class="text-xs text-gray-500 mt-2">{{ progressMessage }}</p>
      </div>
    </div>

    <!-- Resubmit Modal -->
    <div v-if="showResubmitModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">KYC Resubmission</h3>
          <div class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Previous Rejection Details:</h4>
            <div class="bg-red-50 p-3 rounded-md">
              <p class="text-sm text-red-700"><strong>Reason:</strong> {{ rejectionDetails?.reason }}</p>
              <p v-if="rejectionDetails?.notes" class="text-sm text-red-600 mt-1">{{ rejectionDetails.notes }}</p>
            </div>
          </div>
          <div class="flex justify-end space-x-3">
            <button 
              @click="showResubmitModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
            >
              Cancel
            </button>
            <button 
              @click="resubmitKYC"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
            >
              Resubmit
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KYCStatusCard',
  props: {
    initialStatus: {
      type: String,
      default: 'not_started'
    },
    initialData: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      status: this.initialStatus,
      kycData: this.initialData,
      showResubmitModal: false,
      progressPercentage: 0,
      progressMessage: 'Initializing verification...',
      progressInterval: null
    }
  },
  computed: {
    statusIconClasses() {
      const classes = {
        'verified': 'bg-green-100 text-green-600',
        'pending': 'bg-yellow-100 text-yellow-600',
        'rejected': 'bg-red-100 text-red-600',
        'not_started': 'bg-gray-100 text-gray-600'
      }
      return classes[this.status] || classes.not_started
    },
    statusTitle() {
      const titles = {
        'verified': 'KYC Verified',
        'pending': 'KYC Under Review',
        'rejected': 'KYC Rejected',
        'not_started': 'KYC Not Started'
      }
      return titles[this.status] || titles.not_started
    },
    statusDescription() {
      const descriptions = {
        'verified': 'Your identity has been verified successfully',
        'pending': 'Your verification is being reviewed by our team',
        'rejected': 'Your verification was not approved. Please check the reason and resubmit.',
        'not_started': 'Complete your KYC verification to access all features'
      }
      return descriptions[this.status] || descriptions.not_started
    },
    expiresAt() {
      return this.kycData?.expires_at || null
    },
    rejectionDetails() {
      return this.kycData?.admin_rejection || null
    }
  },
  mounted() {
    this.startProgressSimulation()
    this.pollStatus()
  },
  beforeUnmount() {
    if (this.progressInterval) {
      clearInterval(this.progressInterval)
    }
  },
  methods: {
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    startKYC() {
      this.$emit('start-kyc')
    },
    resubmitKYC() {
      this.showResubmitModal = false
      this.$emit('resubmit-kyc')
    },
    startProgressSimulation() {
      if (this.status === 'pending') {
        this.progressInterval = setInterval(() => {
          if (this.progressPercentage < 90) {
            this.progressPercentage += Math.random() * 10
            this.updateProgressMessage()
          }
        }, 2000)
      }
    },
    updateProgressMessage() {
      const messages = [
        'Initializing verification...',
        'Processing documents...',
        'Verifying identity...',
        'Running background checks...',
        'Finalizing verification...'
      ]
      const index = Math.floor((this.progressPercentage / 100) * messages.length)
      this.progressMessage = messages[Math.min(index, messages.length - 1)]
    },
    async pollStatus() {
      if (this.status === 'pending') {
        try {
          const response = await fetch('/kyc/status')
          const data = await response.json()
          if (data.success && data.status !== this.status) {
            this.status = data.status
            this.kycData = data.data || {}
            this.$emit('status-changed', this.status)
          }
        } catch (error) {
          console.error('Error polling KYC status:', error)
        }
      }
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