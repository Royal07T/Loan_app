# Vue.js Components for KYC System

This document describes the Vue.js components created to enhance the user experience of the KYC (Know Your Customer) verification system.

## 🚀 Overview

The KYC system now includes modern, interactive Vue.js components that provide:
- Real-time status updates
- Interactive forms with validation
- Progress tracking
- Provider selection
- Responsive design with Tailwind CSS

## 📦 Components

### 1. KYCStatusCard.vue
**Purpose**: Displays the current KYC verification status with interactive elements.

**Features**:
- Real-time status display with appropriate icons
- Progress simulation for pending verifications
- Rejection details with reasons
- Action buttons (Start/Resubmit KYC)
- Status polling for updates

**Props**:
- `initialStatus`: String - Initial KYC status
- `initialData`: Object - Initial KYC data

**Events**:
- `start-kyc`: Emitted when user wants to start KYC
- `resubmit-kyc`: Emitted when user wants to resubmit KYC
- `status-changed`: Emitted when status changes

**Usage**:
```vue
<kyc-status-card 
    :initial-status="'pending'"
    :initial-data="kycData"
    @start-kyc="handleStartKYC"
    @resubmit-kyc="handleResubmitKYC"
    @status-changed="handleStatusChange"
></kyc-status-card>
```

### 2. KYCForm.vue
**Purpose**: Multi-step form for KYC verification with dynamic validation.

**Features**:
- Step-by-step form wizard
- Real-time validation
- Dynamic form fields based on verification type
- Document type selection
- Review step before submission

**Events**:
- `verification-started`: Emitted when verification is successfully started

**Usage**:
```vue
<kyc-form @verification-started="handleVerificationStarted"></kyc-form>
```

### 3. KYCProgress.vue
**Purpose**: Visual progress tracker for KYC verification process.

**Features**:
- Animated progress bar
- Step-by-step progress tracking
- Real-time status updates
- Action buttons (Retry, Cancel, View Results)
- Time estimates for each step

**Props**:
- `initialStatus`: String - Initial progress status
- `initialProgress`: Number - Initial progress percentage

**Events**:
- `retry-verification`: Emitted when user wants to retry
- `cancel-verification`: Emitted when user wants to cancel
- `view-results`: Emitted when user wants to view results

**Usage**:
```vue
<kyc-progress 
    :initial-status="'pending'"
    :initial-progress="25"
    @retry-verification="handleRetry"
    @cancel-verification="handleCancel"
    @view-results="handleViewResults"
></kyc-progress>
```

### 4. KYCProviderSelector.vue
**Purpose**: Interactive provider selection with comparison features.

**Features**:
- Provider cards with ratings and features
- Provider comparison table
- Real-time selection feedback
- Provider status indicators
- Feature comparison

**Events**:
- `provider-selected`: Emitted when a provider is selected
- `proceed`: Emitted when user wants to proceed with selected provider

**Usage**:
```vue
<kyc-provider-selector 
    @provider-selected="handleProviderSelected"
    @proceed="handleProviderProceed"
></kyc-provider-selector>
```

## 🛠️ Setup and Installation

### Prerequisites
- Node.js and npm
- Laravel 11
- Vite

### Installation Steps

1. **Install Vue.js and Vite plugin**:
```bash
npm install vue@3 @vitejs/plugin-vue
```

2. **Update Vite configuration** (`vite.config.js`):
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
```

3. **Update main JavaScript file** (`resources/js/app.js`):
```javascript
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
```

4. **Build assets**:
```bash
npm run build
```

## 📱 Usage in Blade Templates

### Basic Usage
```blade
@extends('layouts.app')

@section('content')
<div id="app">
    <kyc-status-card 
        :initial-status="'{{ Auth::user()->kyc_status ?? 'not_started' }}'"
        :initial-data='@json(Auth::user()->kyc_data ?? [])'
        @start-kyc="redirectToKYC"
        @resubmit-kyc="redirectToKYC"
        @status-changed="handleKYCStatusChange"
    ></kyc-status-card>
</div>

<script>
window.redirectToKYC = function() {
    window.location.href = '/kyc';
};

window.handleKYCStatusChange = function(status) {
    console.log('KYC status changed:', status);
};
</script>
@endsection
```

### Modal Usage
```blade
<!-- Modal trigger -->
<button @click="showKYCForm = true">Start KYC</button>

<!-- Modal -->
<div v-if="showKYCForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <kyc-form @verification-started="handleVerificationStarted"></kyc-form>
    </div>
</div>
```

## 🔧 API Endpoints

The components use the following API endpoints:

### KYC Status
- **GET** `/kyc/status` - Get current KYC status
- **POST** `/kyc/initialize` - Initialize KYC verification
- **POST** `/kyc/resubmit` - Resubmit KYC verification

### Response Format
```json
{
    "success": true,
    "status": "pending",
    "data": {
        "verification_id": "12345",
        "provider": "shuftipro",
        "submitted_at": "2024-01-01T00:00:00Z"
    },
    "expires_at": "2024-01-02T00:00:00Z",
    "is_expired": false
}
```

## 🎨 Styling

All components use Tailwind CSS for styling and are fully responsive. The design follows modern UI/UX principles with:

- Clean, minimalist design
- Consistent color scheme
- Smooth animations and transitions
- Mobile-first responsive design
- Accessible color contrasts

## 🔄 State Management

Components use Vue's reactive data system for state management:

- Local component state for UI interactions
- Props for initial data from Laravel
- Events for parent-child communication
- Global functions for cross-component communication

## 🧪 Testing

### Demo Page
Visit `/kyc/demo` to see all components in action with interactive controls.

### Manual Testing
1. Navigate to the dashboard
2. Check KYC status card functionality
3. Test form interactions
4. Verify progress tracking
5. Test provider selection

## 🚀 Performance

- Components are lazy-loaded
- Optimized bundle size with Vite
- Efficient re-rendering with Vue's reactivity
- Minimal DOM manipulation

## 🔒 Security

- CSRF protection for all API calls
- Input validation on both client and server
- XSS protection with Vue's template system
- Rate limiting on API endpoints

## 📈 Future Enhancements

1. **Real-time Updates**: WebSocket integration for live status updates
2. **Offline Support**: Service worker for offline functionality
3. **Advanced Validation**: Custom validation rules and error handling
4. **Accessibility**: ARIA labels and keyboard navigation
5. **Internationalization**: Multi-language support
6. **Analytics**: User interaction tracking
7. **Mobile App**: React Native or Flutter integration

## 🐛 Troubleshooting

### Common Issues

1. **Components not loading**:
   - Check if Vue is properly mounted
   - Verify component registration
   - Check browser console for errors

2. **Styling issues**:
   - Ensure Tailwind CSS is loaded
   - Check for CSS conflicts
   - Verify responsive breakpoints

3. **API errors**:
   - Check CSRF token
   - Verify API endpoints
   - Check authentication status

### Debug Mode
Enable Vue DevTools for debugging:
```javascript
// In development
if (process.env.NODE_ENV === 'development') {
    app.config.devtools = true;
}
```

## 📚 Resources

- [Vue.js Documentation](https://vuejs.org/)
- [Vite Documentation](https://vitejs.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Laravel Documentation](https://laravel.com/docs)

## 🤝 Contributing

When adding new components or modifying existing ones:

1. Follow Vue.js best practices
2. Maintain consistent styling with Tailwind
3. Add proper documentation
4. Include error handling
5. Test across different devices
6. Update this documentation

---

**Note**: These components are designed to work with the Laravel KYC system and require the backend API endpoints to be properly configured. 