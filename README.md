# Food Delivery System - Backend API

Complete REST API for a food delivery platform built with Laravel 12, MySQL, and Laravel Sanctum.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Docker & Docker Compose
- MySQL 8.0+

### Installation

1. **Clone the repository**
```bash
cd backend-laravel
```

2. **Install dependencies**
```bash
composer install
```

3. **Start Docker MySQL**
```bash
cd ..
docker-compose up -d
```

4. **Configure environment**
```bash
cp .env.example .env
```

The `.env` file is already configured with:
- Database: `food_delivery`
- Host: `127.0.0.1`
- Port: `3309`
- User: `food_delivery_user`
- Password: `food_delivery_pass`

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed database**
```bash
php artisan db:seed
```

This creates:
- Admin user: `admin@fooddelivery.com` / `password`
- Customer: `customer@test.com` / `password`
- Restaurant Owner: `restaurant@test.com` / `password`
- Delivery Partner: `delivery@test.com` / `password`
- 3 sample restaurants with menus
- 10 restaurant categories

7. **Start development server**
```bash
php artisan serve
```

API will be available at `http://localhost:8000/api`

## 📊 API Endpoints (53 Total)

### Authentication (7 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | - | Register new user |
| POST | `/api/auth/send-otp` | - | Send OTP to email |
| POST | `/api/auth/verify-otp` | - | Verify OTP and get token |
| POST | `/api/auth/login` | - | Login with credentials |
| POST | `/api/auth/logout` | ✓ | Logout user |
| GET | `/api/auth/user` | ✓ | Get current user |
| POST | `/api/auth/refresh` | ✓ | Refresh access token |

### Restaurants (16 endpoints)

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/restaurants` | customer | List all approved restaurants |
| GET | `/api/restaurants/{id}` | customer | Get restaurant details |
| POST | `/api/restaurants` | restaurant_owner | Create restaurant |
| GET | `/api/restaurants/my/list` | restaurant_owner | Get owner's restaurants |
| PUT | `/api/restaurants/{id}` | restaurant_owner | Update restaurant |
| DELETE | `/api/restaurants/{id}` | restaurant_owner | Delete restaurant |
| PUT | `/api/restaurants/{id}/status` | admin | Approve/reject restaurant |

**Menu Management**

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/restaurants/{id}/menu/categories` | restaurant_owner | List menu categories |
| POST | `/api/restaurants/{id}/menu/categories` | restaurant_owner | Create category |
| PUT | `/api/restaurants/{id}/menu/categories/{categoryId}` | restaurant_owner | Update category |
| GET | `/api/restaurants/{id}/menu/items` | restaurant_owner | List menu items |
| POST | `/api/restaurants/{id}/menu/items` | restaurant_owner | Create menu item |
| PUT | `/api/restaurants/{id}/menu/items/{itemId}` | restaurant_owner | Update menu item |
| DELETE | `/api/restaurants/{id}/menu/items/{itemId}` | restaurant_owner | Delete menu item |
| POST | `/api/restaurants/{id}/menu/items/{itemId}/variants` | restaurant_owner | Add item variant |
| POST | `/api/restaurants/{id}/menu/items/{itemId}/addons` | restaurant_owner | Add item addon |

### Orders (7 endpoints)

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/api/orders` | customer | Place new order |
| GET | `/api/orders` | customer | Get user's orders |
| GET | `/api/orders/{id}` | customer | Get order details |
| PUT | `/api/orders/{id}/cancel` | customer | Cancel order |
| POST | `/api/orders/{id}/review` | customer | Submit review |
| GET | `/api/orders/restaurant/{restaurantId}` | restaurant_owner | Get restaurant orders |
| PUT | `/api/orders/{id}/status` | restaurant_owner | Update order status |

### Delivery (10 endpoints)

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/api/delivery/register` | authenticated | Register as delivery partner |
| PUT | `/api/delivery/profile` | delivery_partner | Update profile |
| PUT | `/api/delivery/location` | delivery_partner | Update location |
| PUT | `/api/delivery/availability` | delivery_partner | Toggle availability |
| GET | `/api/delivery/available-orders` | delivery_partner | Get nearby orders |
| POST | `/api/delivery/accept/{orderId}` | delivery_partner | Accept delivery |
| POST | `/api/delivery/reject/{orderId}` | delivery_partner | Reject delivery |
| GET | `/api/delivery/my-deliveries` | delivery_partner | Get delivery history |
| GET | `/api/delivery/pending-requests` | delivery_partner | Get pending requests |
| PUT | `/api/delivery/verify/{partnerId}` | admin | Verify delivery partner |

### Admin (9 endpoints)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/dashboard` | Get dashboard stats |
| GET | `/api/admin/users` | List all users |
| PUT | `/api/admin/users/{userId}/status` | Update user status |
| GET | `/api/admin/restaurants` | List all restaurants |
| GET | `/api/admin/delivery-partners` | List all delivery partners |
| GET | `/api/admin/orders` | List all orders |
| GET | `/api/admin/analytics` | Get analytics data |
| GET | `/api/admin/settings` | Get system settings |
| PUT | `/api/admin/settings` | Update system settings |

### Notifications (4 endpoints)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | Get user notifications |
| GET | `/api/notifications/unread-count` | Get unread count |
| PUT | `/api/notifications/{id}/read` | Mark as read |
| PUT | `/api/notifications/read-all` | Mark all as read |

## 🔐 Authentication

All protected endpoints require a Bearer token:

```bash
Authorization: Bearer {token}
```

Get token by:
1. Register → Verify OTP → Receive token
2. Login → Receive token

## 📝 Request Examples

### Register User
```bash
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "role": "customer"
}
```

### Place Order
```bash
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "restaurant_id": 1,
  "delivery_address_id": 1,
  "payment_method": "cash",
  "items": [
    {
      "menu_item_id": 1,
      "quantity": 2,
      "variant_id": 1,
      "addons": [1, 2],
      "special_instructions": "Extra cheese"
    }
  ]
}
```

### Search Nearby Restaurants
```bash
GET /api/restaurants?lat=40.7128&lng=-74.0060&radius=5
Authorization: Bearer {token}
```

## 🏗️ Architecture

### Modular Structure
- **Services**: Business logic separated from controllers
  - `OtpService`: OTP generation and verification
  - `OrderService`: Order lifecycle management
  - `DeliveryService`: Assignment and tracking
  - `NotificationService`: Multi-channel notifications

- **Controllers**: Thin, handle HTTP only
- **Models**: 19 models with relationships
- **Middleware**: Role-based access control

### Database
- 23 tables with proper indexing
- Foreign key constraints
- Soft deletes on critical tables
- Geolocation support (lat/lng)

## 🔒 Roles & Permissions

| Role | Capabilities |
|------|-------------|
| `admin` | Full system access, approve restaurants/partners, view analytics |
| `customer` | Browse restaurants, place orders, track deliveries |
| `restaurant_owner` | Manage restaurants, menus, receive orders |
| `delivery_partner` | Accept deliveries, update location, complete deliveries |

## 🌟 Features

✅ OTP-based authentication (email, phone-ready)  
✅ Geolocation-based restaurant discovery  
✅ Real-time delivery partner tracking  
✅ Order lifecycle management (9 states)  
✅ Polymorphic review system  
✅ Admin analytics & reporting  
✅ Notification system  
✅ Role-based access control  
✅ Price snapshots (historical accuracy)  

## 🧪 Testing

Run seeders to populate test data:
```bash
php artisan db:seed
```

Test users:
- Admin: `admin@fooddelivery.com`
- Customer: `customer@test.com`
- Restaurant Owner: `restaurant@test.com`
- Delivery Partner: `delivery@test.com`

All passwords: `password`

## 📦 Tech Stack

- **Framework**: Laravel 12
- **Database**: MySQL 8.0 (Docker)
- **Authentication**: Laravel Sanctum
- **OTP**: Email (SMS-ready)
- **Geolocation**: Haversine formula

## 🔧 Configuration

### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3309
DB_DATABASE=food_delivery
DB_USERNAME=food_delivery_user
DB_PASSWORD=food_delivery_pass
```

### Mail (for OTP)
Configure SMTP in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## 📄 License

This project is for educational purposes.

## 🚀 Next Steps

- [ ] Payment gateway integration
- [ ] Real-time tracking (WebSockets)
- [ ] SMS OTP integration
- [ ] API rate limiting
- [ ] Mobile apps (React Native)
