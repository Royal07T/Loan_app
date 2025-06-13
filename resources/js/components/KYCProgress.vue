<template>
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Verification Progress</h3>
    </div>

    <div class="p-6">
      <!-- Progress Bar -->
      <div class="mb-6">
        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
          <span>Overall Progress</span>
          <span>{{ overallProgress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-gradient-to-r from-blue-500 to-green-500 h-3 rounded-full transition-all duration-1000 ease-out"
            :style="{ width: overallProgress + '%' }"
          ></div>
        </div>
      </div>

      <!-- Step Progress -->
      <div class="space-y-4">
        <div 
          v-for="(step, index) in verificationSteps" 
          :key="index"
          class="flex items-center space-x-4"
        >
          <!-- Step Icon -->
          <div 
            :class="getStepIconClasses(step.status)"
            class="w-10 h-10 rounded-full flex items-center justify-center"
          >
            <svg v-if="step.status === 'completed'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else-if="step.status === 'in_progress'" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>

          <!-- Step Content -->
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-medium text-gray-900">{{ step.title }}</h4>
              <span :class="getStepStatusClasses(step.status)" class="text-xs font-medium">
                {{ getStepStatusText(step.status) }}
              </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ step.description }}</p>
            
            <!-- Sub-steps -->
            <div v-if="step.subSteps && step.subSteps.length > 0" class="mt-3 space-y-2">
              <div 
                v-for="(subStep, subIndex) in step.subSteps" 
                :key="subIndex"
                class="flex items-center space-x-2"
              >
                <div 
                  :class="getSubStepIconClasses(subStep.status)"
                  class="w-4 h-4 rounded-full flex items-center justify-center"
                >
                  <svg v-if="subStep.status === 'completed'" class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                  <div v-else-if="subStep.status === 'in_progress'" class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                  <div v-else class="w-2 h-2 bg-gray-300 rounded-full"></div>
                </div>
                <span :class="getSubStepTextClasses(subStep.status)" class="text-xs">
                  {{ subStep.title }}
                </span>
              </div>
            </div>

            <!-- Time Estimate -->
            <div v-if="step.estimatedTime" class="mt-2 text-xs text-gray-400">
              <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Estimated time: {{ step.estimatedTime }}
            </div>
          </div>
        </div>
      </div>

      <!-- Current Status Message -->
      <div v-if="currentStatusMessage" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-sm text-blue-800">{{ currentStatusMessage }}</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div v-if="showActions" class="mt-6 flex space-x-3">
        <button 
          v-if="canRetry"
          @click="retryVerification"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          Retry
        </button>
        <button 
          v-if="canCancel"
          @click="cancelVerification"
          class="px-4 py-2 text-sm font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Cancel
        </button>
        <button 
          v-if="isCompleted"
          @click="viewResults"
          class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          View Results
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KYCProgress',
  props: {
    initialStatus: {
      type: String,
      default: 'pending'
    },
    initialProgress: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      status: this.initialStatus,
      progress: this.initialProgress,
      verificationSteps: [
        {
          title: 'Document Upload',
          description: 'Uploading and processing your identification documents',
          status: 'completed',
          estimatedTime: '1-2 minutes',
          subSteps: [
            { title: 'Document validation', status: 'completed' },
            { title: 'OCR processing', status: 'completed' },
            { title: 'Data extraction', status: 'completed' }
          ]
        },
        {
          title: 'Face Verification',
          description: 'Verifying your identity through facial recognition',
          status: 'in_progress',
          estimatedTime: '2-3 minutes',
          subSteps: [
            { title: 'Face detection', status: 'completed' },
            { title: 'Liveness check', status: 'in_progress' },
            { title: 'Face matching', status: 'pending' }
          ]
        },
        {
          title: 'Background Check',
          description: 'Running security and background verification',
          status: 'pending',
          estimatedTime: '3-5 minutes',
          subSteps: [
            { title: 'Identity verification', status: 'pending' },
            { title: 'Address verification', status: 'pending' },
            { title: 'Security screening', status: 'pending' }
          ]
        },
        {
          title: 'Final Review',
          description: 'Completing final verification and approval',
          status: 'pending',
          estimatedTime: '1-2 minutes',
          subSteps: [
            { title: 'Quality assurance', status: 'pending' },
            { title: 'Manual review', status: 'pending' },
            { title: 'Approval', status: 'pending' }
          ]
        }
      ],
      currentStatusMessage: 'Processing your verification...',
      pollInterval: null
    }
  },
  computed: {
    overallProgress() {
      const completedSteps = this.verificationSteps.filter(step => step.status === 'completed').length
      const totalSteps = this.verificationSteps.length
      return Math.round((completedSteps / totalSteps) * 100)
    },
    showActions() {
      return this.status === 'failed' || this.status === 'completed' || this.status === 'cancelled'
    },
    canRetry() {
      return this.status === 'failed'
    },
    canCancel() {
      return this.status === 'pending' || this.status === 'in_progress'
    },
    isCompleted() {
      return this.status === 'completed'
    }
  },
  mounted() {
    this.startPolling()
    this.updateStepProgress()
  },
  beforeUnmount() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval)
    }
  },
  methods: {
    getStepIconClasses(status) {
      const classes = {
        'completed': 'bg-green-100 text-green-600',
        'in_progress': 'bg-blue-100 text-blue-600',
        'failed': 'bg-red-100 text-red-600',
        'pending': 'bg-gray-100 text-gray-400'
      }
      return classes[status] || classes.pending
    },
    getStepStatusClasses(status) {
      const classes = {
        'completed': 'text-green-600',
        'in_progress': 'text-blue-600',
        'failed': 'text-red-600',
        'pending': 'text-gray-400'
      }
      return classes[status] || classes.pending
    },
    getStepStatusText(status) {
      const texts = {
        'completed': 'Completed',
        'in_progress': 'In Progress',
        'failed': 'Failed',
        'pending': 'Pending'
      }
      return texts[status] || 'Pending'
    },
    getSubStepIconClasses(status) {
      const classes = {
        'completed': 'bg-green-500',
        'in_progress': 'bg-blue-500',
        'failed': 'bg-red-500',
        'pending': 'bg-gray-300'
      }
      return classes[status] || classes.pending
    },
    getSubStepTextClasses(status) {
      const classes = {
        'completed': 'text-green-600',
        'in_progress': 'text-blue-600',
        'failed': 'text-red-600',
        'pending': 'text-gray-400'
      }
      return classes[status] || classes.pending
    },
    updateStepProgress() {
      // Simulate progress updates
      let currentStepIndex = 0
      
      const updateInterval = setInterval(() => {
        if (currentStepIndex < this.verificationSteps.length) {
          const step = this.verificationSteps[currentStepIndex]
          
          if (step.status === 'pending') {
            step.status = 'in_progress'
            this.currentStatusMessage = `Processing ${step.title.toLowerCase()}...`
          } else if (step.status === 'in_progress') {
            // Simulate sub-step progress
            const pendingSubSteps = step.subSteps.filter(sub => sub.status === 'pending')
            if (pendingSubSteps.length > 0) {
              const nextSubStep = pendingSubSteps[0]
              nextSubStep.status = 'in_progress'
              
              setTimeout(() => {
                nextSubStep.status = 'completed'
              }, 2000)
            } else {
              step.status = 'completed'
              currentStepIndex++
              
              if (currentStepIndex < this.verificationSteps.length) {
                this.verificationSteps[currentStepIndex].status = 'in_progress'
              } else {
                this.status = 'completed'
                this.currentStatusMessage = 'Verification completed successfully!'
                clearInterval(updateInterval)
              }
            }
          }
        }
      }, 3000)
    },
    startPolling() {
      this.pollInterval = setInterval(async () => {
        try {
          const response = await fetch('/kyc/status')
          const data = await response.json()
          
          if (data.success) {
            this.status = data.status
            this.updateProgressFromStatus(data.status)
            
            if (data.status === 'completed' || data.status === 'failed') {
              clearInterval(this.pollInterval)
            }
          }
        } catch (error) {
          console.error('Error polling KYC status:', error)
        }
      }, 5000)
    },
    updateProgressFromStatus(status) {
      switch (status) {
        case 'completed':
          this.verificationSteps.forEach(step => {
            step.status = 'completed'
            step.subSteps.forEach(subStep => {
              subStep.status = 'completed'
            })
          })
          this.currentStatusMessage = 'Verification completed successfully!'
          break
        case 'failed':
          this.currentStatusMessage = 'Verification failed. Please try again.'
          break
        case 'cancelled':
          this.currentStatusMessage = 'Verification was cancelled.'
          break
      }
    },
    retryVerification() {
      this.$emit('retry-verification')
    },
    cancelVerification() {
      if (confirm('Are you sure you want to cancel the verification?')) {
        this.$emit('cancel-verification')
      }
    },
    viewResults() {
      this.$emit('view-results')
    }
  }
}
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style> 