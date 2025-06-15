# 🏦 Loan Management System (LMS)

A comprehensive Laravel-based loan management system with integrated KYC (Know Your Customer) verification, built with modern web technologies.

## 🚀 Features

### Core Loan Management
- **Loan Application & Processing** - Complete loan lifecycle management
- **Loan Categories** - Flexible loan type configuration
- **Repayment Tracking** - Automated repayment scheduling and tracking
- **Payment Processing** - Integrated payment gateway support
- **Document Management** - Secure document upload and storage
- **Analytics & Reporting** - Comprehensive loan analytics and PDF reports

### KYC (Know Your Customer) System
- **Multi-Provider Integration** - Support for multiple KYC verification providers
- **Real-time Verification** - Live status updates and progress tracking
- **Document Verification** - ID document and proof of address verification
- **Admin Management** - Complete admin interface for KYC oversight
- **Bulk Operations** - Mass approval/rejection capabilities
- **Export Functionality** - CSV export for compliance reporting

### User Management
- **Role-based Access Control** - Admin, user, and staff roles
- **Wallet System** - Digital wallet for loan disbursements
- **Transaction History** - Complete transaction tracking
- **Profile Management** - User profile and KYC status management

### Technical Features
- **Modern UI/UX** - Vue.js components with Tailwind CSS
- **Responsive Design** - Mobile-first responsive interface
- **Real-time Updates** - Live status updates and notifications
- **API Integration** - RESTful API for mobile app integration
- **Security** - Laravel Sanctum authentication and authorization

## 🛠️ Technology Stack

### Backend
- **Laravel 11** - PHP framework
- **MySQL/PostgreSQL** - Database
- **Laravel Sanctum** - API authentication
- **Spatie Laravel Permission** - Role-based access control
- **Laravel Queue** - Background job processing

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Build tool and development server
- **Axios** - HTTP client for API calls

### Integrations
- **Stripe** - Payment processing
- **Web3.php** - Blockchain integration
- **Larapex Charts** - Data visualization
- **DomPDF** - PDF generation
- **Laravel Excel** - Excel import/export

## 📋 Prerequisites

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.0
- **npm** >= 8.0
- **MySQL** >= 8.0 or **PostgreSQL** >= 13.0
- **Redis** (optional, for caching and queues)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd Loan_app
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Environment Variables
Edit `.env` file with your database and service configurations:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loan_app
DB_USERNAME=root
DB_PASSWORD=

STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key

KYC_PROVIDER_API_KEY=your_kyc_provider_key
```

### 6. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 7. Build Assets
```bash
npm run build
```

### 8. Start Development Server
```bash
# Using Laravel Sail (Docker)
./vendor/bin/sail up

# Or using traditional methods
php artisan serve
npm run dev
```

## 🏗️ Project Structure

```
Loan_app/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Admin controllers
│   │   ├── Api/            # API controllers
│   │   └── Auth/           # Authentication controllers
│   ├── Models/             # Eloquent models
│   ├── Services/           # Business logic services
│   └── Notifications/      # Email notifications
├── resources/
│   ├── js/components/      # Vue.js components
│   ├── views/              # Blade templates
│   └── sass/              # Stylesheets
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
└── database/
    ├── migrations/         # Database migrations
    └── seeders/           # Database seeders
```

## 🔧 Configuration

### KYC Providers
Configure KYC providers in `config/kyc.php`:
```php
'providers' => [
    'provider1' => [
        'api_key' => env('KYC_PROVIDER1_API_KEY'),
        'api_url' => env('KYC_PROVIDER1_API_URL'),
    ],
    'provider2' => [
        'api_key' => env('KYC_PROVIDER2_API_KEY'),
        'api_url' => env('KYC_PROVIDER2_API_URL'),
    ],
],
```

### Payment Gateways
Configure payment providers in `config/payment.php`:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],
```

## 📱 Vue.js Components

The application includes several Vue.js components for enhanced user experience:

- **KYCStatusCard** - Real-time KYC status display
- **KYCForm** - Multi-step KYC verification form
- **KYCProgress** - Visual progress tracking
- **KYCProviderSelector** - Provider selection interface

See `VUE_COMPONENTS.md` for detailed component documentation.

## 🔐 Security Features

- **CSRF Protection** - Cross-site request forgery protection
- **SQL Injection Prevention** - Eloquent ORM protection
- **XSS Protection** - Blade template escaping
- **Authentication** - Laravel Sanctum for API authentication
- **Authorization** - Role-based access control
- **Input Validation** - Comprehensive form validation
- **Rate Limiting** - API rate limiting protection

## 📊 API Documentation

### Authentication
```bash
POST /api/auth/login
POST /api/auth/register
POST /api/auth/logout
```

### Loans
```bash
GET    /api/loans
POST   /api/loans
GET    /api/loans/{id}
PUT    /api/loans/{id}
DELETE /api/loans/{id}
```

### KYC
```bash
GET    /api/kyc/status
POST   /api/kyc/start
POST   /api/kyc/verify
GET    /api/kyc/providers
```

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run specific test suite
php artisan test --filter=KYCTest

# Run with coverage
php artisan test --coverage
```

## 🚀 Deployment

### Production Build
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables
Ensure all production environment variables are set:
- Database credentials
- API keys for external services
- Mail configuration
- Queue configuration
- Cache configuration

## 📈 Monitoring & Logging

- **Laravel Logs** - Application logs in `storage/logs/`
- **Queue Monitoring** - Monitor background jobs
- **Error Tracking** - Comprehensive error logging
- **Performance Monitoring** - Application performance metrics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation in the `docs/` folder
- Review the Vue.js components documentation in `VUE_COMPONENTS.md`

## 🔄 Changelog

### Version 1.0.0
- Initial release with core loan management features
- KYC integration with multiple providers
- Vue.js frontend components
- Admin management interface
- API endpoints for mobile integration

---

## 👨‍💻 Author

**Royal T** - [GitHub](https://github.com/Royal07T)

---

**Built with ❤️ using Laravel and Vue.js**
