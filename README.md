# 🛍️ **E-Commerce Platform** 🛍️

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-11+-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)
![Stripe](https://img.shields.io/badge/Stripe-Payment-635BFF?style=for-the-badge&logo=stripe&logoColor=white)

**A Modern, Scalable, and Feature-Rich E-Commerce Solution Built with Laravel 12+**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11%2B-red.svg)](https://laravel.com)

</div>

---

## 🌟 **Overview**

**E-commerce** is a comprehensive, modern e-commerce platform designed to provide a seamless shopping experience for customers while offering powerful management tools for administrators. Built with Laravel 11+ and following the latest best practices, this platform combines robust backend architecture with intelligent features like AI-powered recommendations, multi-language support, and advanced payment processing.

### 🎯 **Key Highlights**

- 🚀 **Laravel 12+** with modern architecture
- 🌍 **Multi-language Support** (Arabic & English)
- 💳 **Multiple Payment Gateways** (Stripe, Wallet)
- 🔐 **Advanced RBAC System** with granular permissions
- 📱 **Mobile-First API Design**
- 📊 **Real-time Analytics** and reporting
- 🔔 **Smart Notifications** (Email & SMS)

---

## 🏗️ **Architecture & Technology Stack**

### **Backend Technologies**
- **Framework**: Laravel 12+ (Latest)
- **PHP Version**: 8.2+
- **Database**: MySQL
- **Authentication**: JWT (tymon/jwt-auth)
- **API**: RESTful with comprehensive documentation


### **Third-Party Integrations**
- **Payment Processing**: Stripe
- **Notifications**: Email & SMS channels
- **File Storage**: Laravel Storage
- **Monitoring**: Laravel Telescope

---

## 🚀 **Core Features**

### 👥 **User Management**
- **Multi-role Authentication System**
- **OTP-based Registration & Verification**
- **Password Reset with OTP**
- **Profile Management**
- **Address Management**
- **Dark Mode Support**

### 🛒 **E-Commerce Core**
- **Product Catalog** with variants and attributes
- **Advanced Search & Filtering**
- **Shopping Cart** with real-time calculations
- **Wishlist Management**
- **Order Management** with status tracking
- **Product Reviews & Ratings**

### 💰 **Payment & Wallet System**
- **Stripe Payment Integration**
- **Digital Wallet System**
- **Transaction History**
- **Multiple Payment Methods**
- **Secure Payment Processing**

### 🤖 **Product Recommendations**
- **Collaborative Filtering**
- **Content-Based Filtering**
- **User Behavior Analysis**
- **Trending Products**
- **Personalized Suggestions**
- **Cross-selling & Up-selling**

### 🎛️ **Admin Dashboard**
- **Comprehensive Admin Panel**
- **Role-Based Access Control (RBAC)**
- **Product Management**
- **Order Management**
- **User Management**
- **Analytics & Reports**
- **Content Management**

### 🌍 **Multi-language & Localization**
- **Arabic & English Support**
- **Translatable Content**
- **RTL Support**
- **Localized Pricing**
- **Cultural Adaptations**

---

## 📋 **System Requirements**

### **Server Requirements**
- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or PostgreSQL 13+
- Redis (optional, for caching)
- Node.js 18+ (for frontend assets)

### **PHP Extensions**
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

---

## 🛠️ **Installation & Setup**

### **1. Clone the Repository**
```bash
git clone https://github.com/Eslam-Amr/coconut-e-commerce-.git
cd coconut-e-commerce-
```

### **2. Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### **3. Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database and other settings in .env
```

### **4. Database Setup**
```bash
# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed
```

### **5. Build Assets**
```bash
# Build frontend assets
npm run build

# Or for development
npm run dev
```

### **6. Start the Application**
```bash
# Start Laravel development server
php artisan serve

# Or use the convenient dev script
composer run dev
```

---

## 🔧 **Configuration**

### **Environment Variables**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=el_3almya
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=60

# Stripe Configuration
STRIPE_KEY=your-stripe-public-key
STRIPE_SECRET=your-stripe-secret-key
STRIPE_BASE_URL=https://api.stripe.com

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

---

## 📚 **API Documentation**

### **Authentication Endpoints**
```
POST /api/client/register          # User registration
POST /api/client/login             # User login
POST /api/client/logout            # User logout
POST /api/client/forget-password   # Password reset request
POST /api/client/verify-otp        # OTP verification
```

### **Product Endpoints**
```
GET  /api/guest/products           # Get all products
GET  /api/guest/products/{id}      # Get product details
GET  /api/guest/products/trending  # Get trending products
GET  /api/guest/products/featured  # Get featured products
GET  /api/guest/products/top-rated # Get top-rated products
```

### **Cart & Order Endpoints**
```
GET    /api/client/cart            # Get cart items
POST   /api/client/cart/add        # Add item to cart
POST   /api/client/cart/update     # Update cart item
DELETE /api/client/cart/remove     # Remove cart item
POST   /api/client/orders/confirm  # Confirm order
GET    /api/client/orders          # Get user orders
```

### **Recommendation Endpoints**
```
GET /api/guest/recommendations           # Get general recommendations
GET /api/client/recommendations          # Get personalized recommendations
GET /api/guest/recommendations/top-rated # Get top-rated products
GET /api/guest/recommendations/trending  # Get trending products
```

### **Admin Endpoints**
```
# Authentication
POST /api/admin/login              # Admin login
POST /api/admin/logout             # Admin logout

# Product Management
GET    /api/admin/products         # List products
POST   /api/admin/products         # Create product
PUT    /api/admin/products/{id}    # Update product
DELETE /api/admin/products/{id}    # Delete product

# User Management
GET    /api/admin/admins           # List admins
POST   /api/admin/admins           # Create admin
PUT    /api/admin/admins/{id}      # Update admin
DELETE /api/admin/admins/{id}      # Delete admin
```

---

## 🎨 **User Interface**

### **Client Application**
- **Modern, Responsive Design**
- **Mobile-First Approach**
- **Dark/Light Mode Toggle**
- **Intuitive Navigation**
- **Fast Loading Times**
- **Accessibility Compliant**

### **Admin Dashboard**
- **Comprehensive Management Interface**
- **Real-time Data Updates**
- **Advanced Filtering & Search**
- **Bulk Operations**
- **Export/Import Functionality**
- **Analytics Dashboard**

---

## 🔐 **Security Features**

### **Authentication & Authorization**
- **JWT-based Authentication**
- **Role-Based Access Control (RBAC)**
- **Granular Permissions System**
- **OTP Verification**
- **Password Hashing (bcrypt)**

### **Data Protection**
- **SQL Injection Prevention**
- **XSS Protection**
- **CSRF Protection**
- **Input Validation & Sanitization**
- **Secure File Uploads**

### **API Security**
- **Rate Limiting**
- **CORS Configuration**
- **Request Validation**
- **Error Handling**
- **Logging & Monitoring**

---

## 🤖 **Recommendation System**

### **Recommendation Algorithms**
1. **Collaborative Filtering**
   - User-based recommendations
   - Item-based recommendations
   - Matrix factorization

2. **Content-Based Filtering**
   - Product similarity
   - Category-based recommendations
   - Brand affinity

3. **Hybrid Approach**
   - Combined algorithms
   - Machine learning integration
   - Real-time personalization

### **User Behavior Tracking**
- **Product Views**
- **Purchase History**
- **Wishlist Interactions**
- **Search Patterns**
- **Session Analytics**

---

## 💳 **Payment System**

### **Supported Payment Methods**
- **Credit/Debit Cards** (via Stripe)
- **Digital Wallet**
- **Bank Transfers**
- **Cash on Delivery**

### **Payment Features**
- **Secure Processing**
- **Transaction History**
- **Refund Management**
- **Multi-currency Support**
- **Tax Calculation**

---

## 🌍 **Internationalization**

### **Supported Languages**
- **Arabic** (RTL Support)
- **English** (LTR Support)

### **Localization Features**
- **Translatable Content**
- **Currency Localization**
- **Date/Time Formatting**
- **Cultural Adaptations**
- **Regional Settings**

---

## 📊 **Analytics & Reporting**

### **User Analytics**
- **Registration Trends**
- **Login Patterns**
- **User Engagement**
- **Geographic Distribution**

### **Sales Analytics**
- **Revenue Tracking**
- **Order Analytics**
- **Product Performance**
- **Customer Behavior**

### **System Analytics**
- **Performance Metrics**
- **Error Tracking**
- **API Usage Statistics**
- **Resource Utilization**

---

## 🧪 **Testing**

### **Test Coverage**
- **Unit Tests**
- **Feature Tests**
- **Integration Tests**
- **API Tests**

### **Running Tests**
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## 🚀 **Deployment**

### **Production Deployment**
```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Set up queue workers
php artisan queue:work
```

### **Docker Deployment**
```bash
# Build and run with Docker
docker-compose up -d
```

---

## 📈 **Performance Optimization**

### **Caching Strategy**
- **Redis Caching**
- **Database Query Optimization**
- **Asset Minification**
- **CDN Integration**

### **Database Optimization**
- **Indexing Strategy**
- **Query Optimization**
- **Connection Pooling**
- **Read Replicas**

---

## 🔧 **Development**

### **Code Standards**
- **PSR-12 Coding Standards**
- **Laravel Best Practices**
- **SOLID Principles**
- **Clean Architecture**

### **Development Tools**
- **Laravel Pint** (Code Style)
- **Laravel Telescope** (Debugging)
- **Laravel Pail** (Logging)
- **PHPUnit** (Testing)

---

## 📝 **Contributing**

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**
3. **Make your changes**
4. **Add tests for new features**
5. **Ensure all tests pass**
6. **Submit a pull request**

### **Contribution Guidelines**
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation
- Use conventional commit messages

---

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🆘 **Support & Contact**

### **Documentation**
- [API Documentation](docs/api.md)
- [User Guide](docs/user-guide.md)
- [Admin Guide](docs/admin-guide.md)
- [Developer Guide](docs/developer-guide.md)



### **Contact Information**
- **Email**: eslamamr537@gmail.com


---

## 🙏 **Acknowledgments**

- **Laravel Community** for the amazing framework
- **TailwindCSS Team** for the utility-first CSS framework
- **Stripe** for payment processing
- **All Contributors** who helped make this project possible

---

<div align="center">

**Made with ❤️ by Eslam Amr**



</div>