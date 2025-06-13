import './bootstrap';
import { createApp } from 'vue';

// Import KYC components
import KYCStatusCard from './components/KYCStatusCard.vue';
import KYCForm from './components/KYCForm.vue';
import KYCProgress from './components/KYCProgress.vue';
import KYCProviderSelector from './components/KYCProviderSelector.vue';

// Initialize Vue app
const app = createApp({});

// Register components globally
app.component('kyc-status-card', KYCStatusCard);
app.component('kyc-form', KYCForm);
app.component('kyc-progress', KYCProgress);
app.component('kyc-provider-selector', KYCProviderSelector);

// Mount the app
app.mount('#app');
