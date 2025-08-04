# 📚 Exam Management System

A comprehensive web-based examination management system built with PHP, following the MVC (Model-View-Controller) architecture with DAO (Data Access Object) pattern and Service layer.

## 🎯 System Overview

The Exam Management System provides role-based access for:
- **👨‍💼 Administrators** - Manage users, subjects, and system-wide exams
- **👨‍🏫 Faculty** - Create and manage exams, questions, and view results
- **👨‍🎓 Students** - Take exams and view their results

## 🏗️ Architecture

```
src/
├── config/          # Database configuration
├── controller/      # MVC Controllers
├── core/           # Core framework (Router, View)
├── dao/            # Data Access Objects
│   ├── interface/  # DAO interfaces
│   └── impl/       # DAO implementations
├── model/          # Data models
├── service/        # Business logic layer
│   └── impl/       # Service implementations
└── views/          # View templates
    ├── auth/       # Authentication views
    ├── dashboard/  # Dashboard views
    └── layouts/    # Layout templates

public/             # Web accessible files
├── api/           # API endpoints
├── index.php      # Main entry point
├── login_mvc.php  # Login page
├── dashboard_mvc.php # Dashboard router
└── logout.php     # Logout handler
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx web server
- Composer

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd exam-management-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up the database**
   ```bash
   # Import the database schema
   mysql -u root -p < capstone2.sql
   ```

4. **Configure database connection**
   ```php
   // src/config/Database.php
   private $host = '127.0.0.1';
   private $database = 'capstone2';
   private $username = 'root';
   private $password = '';
   ```

5. **Start the web server**
   ```bash
   # Using PHP built-in server (for development)
   php -S localhost:8000 -t public
   ```

6. **Access the system**
   - Open browser: `http://localhost:8000`
   - Login with test credentials:
     - **Admin**: `ADMIN001` / `password123`
     - **Faculty**: `FAC001` / `password123`
     - **Student**: `2020-001` / `password123`

## 🧪 Testing

Run the test suite to ensure everything works correctly:

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test categories
php vendor/bin/phpunit tests/Unit/DAO/
php vendor/bin/phpunit tests/Unit/Service/
php vendor/bin/phpunit tests/Unit/Model/
```

## 📊 Database Schema

### Core Tables
- **`users`** - User accounts with role-based access
- **`subjects`** - Course/subject information
- **`exams`** - Examination details and configuration
- **`questions`** - Exam questions with multiple choice options
- **`exam_attempts`** - Student exam attempts and scores
- **`student_answers`** - Individual question responses
- **`subject_assignments`** - Faculty-subject assignments

### Sample Data
The system comes with sample data:
- 1 Admin user
- 2 Faculty members
- 4 Students (2nd and 3rd year)

## 🔐 Authentication & Authorization

### Role-Based Access Control
- **Admin**: Full system access, user management, exam oversight
- **Faculty**: Create/manage exams, view results for assigned subjects
- **Student**: Take exams, view personal results

### Security Features
- Password hashing with bcrypt
- Session-based authentication
- CSRF protection
- Input validation and sanitization

## 🎨 User Interface

### Modern Design
- Responsive design with Tailwind CSS
- Role-specific dashboards
- Intuitive navigation
- Real-time feedback

### Key Features
- **Login Portal**: Secure authentication with role detection
- **Admin Dashboard**: System overview, user management, analytics
- **Faculty Dashboard**: Exam creation, question management, results
- **Student Dashboard**: Available exams, progress tracking, results

## 🔧 API Endpoints

### Authentication
- `POST /api/auth/login.php` - User login

### Future Endpoints (to be implemented)
- `GET /api/exams/` - List exams
- `POST /api/exams/` - Create exam
- `GET /api/results/` - Get exam results
- `POST /api/answers/` - Submit exam answers

## 🛠️ Development

### Code Structure
- **MVC Pattern**: Separation of concerns
- **DAO Pattern**: Database abstraction layer
- **Service Layer**: Business logic encapsulation
- **Dependency Injection**: Loose coupling

### Key Classes
- `UserDAOImpl` - User data operations
- `AuthServiceImpl` - Authentication logic
- `UserServiceImpl` - User management
- `Router` - URL routing
- `View` - Template rendering

## 📝 Features

### ✅ Implemented
- [x] User authentication and authorization
- [x] Role-based dashboard access
- [x] Database integration with DAO pattern
- [x] Service layer for business logic
- [x] Comprehensive unit testing
- [x] Modern responsive UI
- [x] Session management

### 🚧 In Development
- [ ] Exam creation and management
- [ ] Question bank system
- [ ] Real-time exam taking
- [ ] Automated grading
- [ ] Result analytics
- [ ] Email notifications

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Contact the development team
- Check the documentation

---

**Built with ❤️ for educational institutions**