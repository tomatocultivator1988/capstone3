# Service Architecture Guide

This guide explains the new service layer architecture implemented in the application, including how to create and use services effectively.

## Overview

The service layer provides a clean separation between business logic and data access. This architecture offers several benefits:

- **Separation of Concerns**: Business logic is separated from controllers and models
- **Testability**: Services can be easily mocked and tested in isolation
- **Reusability**: Services can be used across multiple controllers
- **Maintainability**: Changes to business logic are centralized in service classes
- **Dependency Injection**: Proper dependency management for better code organization

## Architecture Components

### 1. Service Interfaces

Service interfaces define contracts for business operations. They are located in `src/App/Services/`.

Example: `UserService.php`
```php
interface UserService
{
    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null);
    public function updateUser(int $user_id, string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null): bool;
    // ... other methods
}
```

### 2. Service Implementations

Service implementations contain the actual business logic and coordinate with models.

Example: `UserServiceImpl.php`
```php
class UserServiceImpl implements UserService
{
    private User $userModel;

    public function __construct(?User $userModel = null)
    {
        $this->userModel = $userModel ?? new User();
    }

    public function createUser(string $school_id, string $full_name, string $role, ?int $year_level = null, ?string $section = null)
    {
        // Business logic here
        // Validation, data processing, model coordination
    }
}
```

### 3. Service Container

The `ServiceContainer` manages service dependencies and provides centralized service registration.

```php
$container = ServiceContainer::getInstance();
$userService = $container->get(UserService::class);
```

## Usage Examples

### Using Services in Controllers

```php
class MyController
{
    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? ServiceContainer::getInstance()->get(UserService::class);
    }

    public function createUser()
    {
        $userId = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);
        // Handle response
    }
}
```

### Direct Service Usage

```php
use App\Services\ServiceContainer;
use App\Services\UserService;

$container = ServiceContainer::getInstance();
$userService = $container->get(UserService::class);

$userId = $userService->createUser('2024001', 'John Doe', 'student', 10, 'A');
```

## Service Features

### 1. Validation

Services include built-in validation for business rules:

```php
$errors = $userService->validateUserData([
    'school_id' => '123',
    'full_name' => 'John Doe',
    'role' => 'student',
    'year_level' => 10,
    'section' => 'A'
]);

if (!empty($errors)) {
    // Handle validation errors
}
```

### 2. Error Handling

Services provide consistent error handling and logging:

```php
try {
    $userId = $userService->createUser($school_id, $full_name, $role, $year_level, $section);
} catch (Exception $e) {
    // Errors are logged automatically
    // Handle failure case
}
```

### 3. Business Logic Centralization

All user-related business logic is centralized in the UserService:

- User creation with automatic password generation
- Validation of user data
- Role-specific requirements (e.g., students need year_level and section)
- Duplicate checking
- Data sanitization

## Best Practices

### 1. Always Use Interfaces

- Define interfaces for all services
- Controllers should depend on interfaces, not implementations
- This enables easy testing and swapping of implementations

### 2. Dependency Injection

```php
// Good - Uses dependency injection
public function __construct(?UserService $userService = null)
{
    $this->userService = $userService ?? ServiceContainer::getInstance()->get(UserService::class);
}

// Avoid - Hard dependency
public function __construct()
{
    $this->userService = new UserServiceImpl();
}
```

### 3. Error Handling

- Services should handle and log errors internally
- Return appropriate success/failure indicators
- Use exceptions for exceptional cases

### 4. Validation

- Validate input data in services, not controllers
- Return validation errors for proper user feedback
- Use consistent validation rules

### 5. Testing

Services can be easily tested with mocked dependencies:

```php
public function testCreateUser()
{
    $mockUserModel = $this->createMock(User::class);
    $userService = new UserServiceImpl($mockUserModel);
    
    $result = $userService->createUser('2024001', 'John Doe', 'student', 10, 'A');
    
    $this->assertNotFalse($result);
}
```

## Migration from Legacy Code

### Updating Existing Controllers

1. **Identify Direct Model Usage**: Look for controllers directly using models
2. **Extract Business Logic**: Move business logic from controllers to services
3. **Inject Services**: Use dependency injection for service access
4. **Update Method Calls**: Replace model calls with service calls

### Example Migration

**Before:**
```php
class UserController
{
    public function create()
    {
        $userModel = new User();
        // Validation logic here
        // Business logic here
        $result = $userModel->create($data);
    }
}
```

**After:**
```php
class UserController
{
    private UserService $userService;

    public function __construct(?UserService $userService = null)
    {
        $this->userService = $userService ?? ServiceContainer::getInstance()->get(UserService::class);
    }

    public function create()
    {
        $result = $this->userService->createUser($school_id, $full_name, $role, $year_level, $section);
    }
}
```

## Creating New Services

### 1. Define the Interface

```php
<?php
namespace App\Services;

interface ExamService
{
    public function createExam(array $examData);
    public function updateExam(int $examId, array $examData): bool;
    public function deleteExam(int $examId): bool;
    // ... other methods
}
```

### 2. Implement the Service

```php
<?php
namespace App\Services;

use App\Models\Exam;

class ExamServiceImpl implements ExamService
{
    private Exam $examModel;

    public function __construct(?Exam $examModel = null)
    {
        $this->examModel = $examModel ?? new Exam();
    }

    public function createExam(array $examData)
    {
        // Validation
        $errors = $this->validateExamData($examData);
        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        // Business logic
        return $this->examModel->create($examData);
    }

    private function validateExamData(array $examData): array
    {
        // Validation logic
        return [];
    }
}
```

### 3. Register in Service Container

```php
// In ServiceContainer::registerDefaults()
$this->register(ExamService::class, ExamServiceImpl::class, true);
```

## Testing Services

### Unit Testing Example

```php
use PHPUnit\Framework\TestCase;
use App\Services\UserServiceImpl;
use App\Models\User;

class UserServiceTest extends TestCase
{
    public function testCreateUserSuccess()
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('create')->willReturn(123);
        
        $service = new UserServiceImpl($mockUser);
        $result = $service->createUser('2024001', 'John Doe', 'student', 10, 'A');
        
        $this->assertEquals(123, $result);
    }

    public function testCreateUserValidationFailure()
    {
        $service = new UserServiceImpl();
        $result = $service->createUser('', '', 'invalid_role');
        
        $this->assertFalse($result);
    }
}
```

## Conclusion

The service layer provides a robust foundation for organizing business logic in the application. By following these patterns and best practices, you can create maintainable, testable, and scalable code that clearly separates concerns and follows SOLID principles.

For questions or additional examples, refer to the existing `UserService` implementation and the `ExampleServiceController` for practical usage patterns.