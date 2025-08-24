# RoutePro Backend - MVC Architecture with Inheritance

A comprehensive backend system for the RoutePro travel application built using PHP with MVC architecture and object-oriented programming principles including inheritance.

## 🏗️ Architecture Overview

This backend follows the **Model-View-Controller (MVC)** design pattern with proper **inheritance hierarchy** for user management.

### 📁 Project Structure

```
RoutePro-backend/
├── app/
│   ├── controllers/          # Controller layer
│   │   ├── AuthController.php
│   │   ├── DriverController.php
│   │   ├── GuideController.php
│   │   ├── TravellerController.php
│   │   └── AdminController.php
│   ├── models/              # Model layer with inheritance
│   │   ├── User.php         # Parent abstract class
│   │   ├── Driver.php       # Child class
│   │   ├── Guide.php        # Child class
│   │   ├── Traveller.php    # Child class
│   │   ├── Admin.php        # Child class
│   │   └── [other models]
│   └── core/               # Core framework files
│       ├── Controller.php   # Base controller
│       ├── Model.php       # Base model
│       └── Database.php    # Database connection
└── public/
    └── index.php           # Entry point & router
```

## 🔗 Inheritance Hierarchy

### User Class Hierarchy

```
User (Abstract Parent Class)
├── Driver (Child Class)
├── Guide (Child Class)
├── Traveller (Child Class)
└── Admin (Child Class)
```

### Class Details

#### 🔸 User (Abstract Parent Class)
- **Type**: Abstract class that cannot be instantiated
- **Properties**: id, name, email, password, role, rating, created_at, reset_token, reset_token_expiry
- **Common Methods**:
  - `login()` - Common login functionality for all user types
  - `createUser()` - Protected method to create user in database
  - `emailExists()` - Check if email already exists
  - `generateResetToken()` - Password reset functionality
  - `resetPassword()` - Reset user password
- **Abstract Methods** (must be implemented by child classes):
  - `register()` - Registration logic specific to each user type
  - `getProfileData()` - Get profile data specific to each user type

#### 🔸 Driver (Child Class)
- **Inherits from**: User
- **Additional Properties**: driver_id, phone, license_no, vehicle_type, experience, location, status
- **Specific Methods**:
  - `register()` - Driver-specific registration with validation
  - `getProfileData()` - Get driver profile with vehicle details
  - `updateStatus()` - Update driver availability status
  - `updateLocation()` - Update driver location
  - `getAvailableDrivers()` - Static method to get all available drivers

#### 🔸 Guide (Child Class)
- **Inherits from**: User
- **Additional Properties**: guide_id, phone, nic, license_no, experience, location, languages, status
- **Specific Methods**:
  - `register()` - Guide-specific registration with language validation
  - `getProfileData()` - Get guide profile with language skills
  - `updateStatus()` - Update guide availability status
  - `updateLocation()` - Update guide location
  - `getAvailableGuides()` - Static method to get all available guides
  - `getGuidesByLanguage()` - Static method to filter guides by language

#### 🔸 Traveller (Child Class)
- **Inherits from**: User
- **Additional Properties**: traveller_id, phone
- **Specific Methods**:
  - `register()` - Traveller-specific registration
  - `getProfileData()` - Get traveller profile
  - `getBookingHistory()` - Get traveller's booking history
  - `createBooking()` - Create new booking
  - `getByUserId()` - Static method to get traveller by user ID

#### 🔸 Admin (Child Class)
- **Inherits from**: User
- **Additional Properties**: admin_id, department, permissions
- **Specific Methods**:
  - `register()` - Admin-specific registration
  - `getProfileData()` - Get admin profile
  - `getAllUsers()` - Get all system users
  - `getUsersByRole()` - Get users filtered by role
  - `deleteUser()` - Delete user from system
  - `updateUserRole()` - Update user's role
  - `getSystemStats()` - Get system statistics

## 🎯 Key Inheritance Features Demonstrated

### 1. **Method Overriding**
- Each child class implements the abstract methods `register()` and `getProfileData()` differently
- Common functionality like `login()` is inherited from the parent class

### 2. **Property Inheritance**
- All child classes inherit basic user properties (id, name, email, etc.)
- Each child class adds its own specific properties

### 3. **Code Reusability**
- Common database operations are defined once in the parent class
- Authentication logic is shared across all user types
- Validation helper methods are available to all child classes

### 4. **Polymorphism**
- The same method name (`register()`, `getProfileData()`) behaves differently for each user type
- Factory pattern in `AuthController` creates appropriate user objects based on role

## 🛠️ API Endpoints

### Authentication
- `POST /auth/login` - User login (all roles)
- `POST /auth/register` - User registration (role-specific)
- `POST /auth/logout` - User logout
- `GET /auth/profile` - Get user profile

### Driver Endpoints
- `GET /driver/profile` - Get driver profile
- `PUT /driver/profile` - Update driver profile
- `PUT /driver/status` - Update driver status
- `PUT /driver/location` - Update driver location
- `GET /drivers/available` - Get available drivers

### Guide Endpoints
- `GET /guide/profile` - Get guide profile
- `PUT /guide/profile` - Update guide profile
- `PUT /guide/status` - Update guide status
- `PUT /guide/location` - Update guide location
- `GET /guides/available` - Get available guides
- `POST /guides/by-language` - Get guides by language

### Traveller Endpoints
- `GET /traveller/profile` - Get traveller profile
- `PUT /traveller/profile` - Update traveller profile
- `GET /traveller/bookings` - Get booking history
- `POST /traveller/bookings` - Create new booking

### Admin Endpoints
- `GET /admin/users` - Get all users
- `POST /admin/users/by-role` - Get users by role
- `DELETE /admin/users/delete` - Delete user
- `PUT /admin/users/update-role` - Update user role
- `GET /admin/stats` - Get system statistics
- `GET /admin/profile` - Get admin profile

## 🔒 Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **Session Management**: Secure session handling for authentication
- **Role-Based Access Control**: Methods check user roles before execution
- **Input Validation**: Comprehensive validation in all registration methods
- **SQL Injection Prevention**: PDO prepared statements used throughout

## 📊 Database Schema

### users table
```sql
- id (Primary Key)
- name
- email (Unique)
- password (Hashed)
- role (driver/guide/traveller/admin)
- rating
- created_at
- reset_token
- reset_token_expiry
```

### drivers table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- name
- phone
- license_no
- vehicle_type
- experience
- location
- status
```

### guides table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- name
- phone
- nic
- license_no
- experience
- location
- languages
- status
```

### travellers table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- name
- phone
```

### admins table
```sql
- id (Primary Key)
- user_id (Foreign Key to users)
- name
- department
- permissions
```

## 🚀 Getting Started

1. **Setup Database**
   - Create MySQL database `route_pro_db`
   - Update database credentials in `app/core/Model.php`

2. **Configure Web Server**
   - Place project in web server directory (e.g., `htdocs/RoutePro-backend(02)`)
   - Ensure PHP 7.4+ is installed

3. **Test API**
   - Access: `http://localhost/RoutePro-backend(02)/public/`
   - Use tools like Postman to test endpoints

## 💡 Example Usage

### Register a Driver
```json
POST /auth/register
{
    "role": "driver",
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword",
    "phone": "1234567890",
    "license_no": "LIC123456",
    "vehicle_type": "Car",
    "experience": "5 years",
    "location": "Colombo"
}
```

### Login
```json
POST /auth/login
{
    "email": "john@example.com",
    "password": "securepassword",
    "role": "driver"
}
```

## 🏆 Benefits of This Architecture

1. **Maintainability**: Clear separation of concerns
2. **Scalability**: Easy to add new user types or features
3. **Reusability**: Common functionality shared through inheritance
4. **Security**: Centralized authentication and authorization
5. **Flexibility**: Role-specific functionality while maintaining consistency

This architecture demonstrates professional PHP development practices with proper OOP principles, making it suitable for enterprise-level applications.