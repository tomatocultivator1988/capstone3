<?php

namespace App\Config;

/**
 * Router
 * 
 * Handles URL routing and dispatches requests to the appropriate controller methods.
 * Supports RESTful API routing with parameter extraction.
 */
class Router
{
    private array $routes = [];
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
        $this->registerRoutes();
    }

    /**
     * Register all routes
     */
    private function registerRoutes(): void
    {
        // Auth routes
        $this->addRoute('POST', '/api/auth/login', 'AuthController', 'login');
        $this->addRoute('POST', '/api/auth/logout', 'AuthController', 'logout');

        // User routes
        $this->addRoute('GET', '/api/users', 'UserController', 'index');
        $this->addRoute('GET', '/api/users/{id}', 'UserController', 'show');
        $this->addRoute('POST', '/api/users', 'UserController', 'store');
        $this->addRoute('PUT', '/api/users/{id}', 'UserController', 'update');
        $this->addRoute('DELETE', '/api/users/{id}', 'UserController', 'delete');
        $this->addRoute('GET', '/api/users/by_role/{role}', 'UserController', 'getUsersByRole');
        $this->addRoute('GET', '/api/users/students/{year_level}/{section}', 'UserController', 'getStudentsByYearSection');

        // Subject routes
        $this->addRoute('GET', '/api/subjects', 'SubjectController', 'index');
        $this->addRoute('GET', '/api/subjects/{id}', 'SubjectController', 'show');
        $this->addRoute('POST', '/api/subjects', 'SubjectController', 'store');
        $this->addRoute('PUT', '/api/subjects/{id}', 'SubjectController', 'update');
        $this->addRoute('DELETE', '/api/subjects/{id}', 'SubjectController', 'delete');
        $this->addRoute('GET', '/api/subjects/by_faculty/{faculty_id}', 'SubjectController', 'getByFaculty');

        // Exam routes
        $this->addRoute('GET', '/api/exams', 'ExamController', 'index');
        $this->addRoute('GET', '/api/exams/{id}', 'ExamController', 'show');
        $this->addRoute('POST', '/api/exams', 'ExamController', 'store');
        $this->addRoute('PUT', '/api/exams/{id}', 'ExamController', 'update');
        $this->addRoute('DELETE', '/api/exams/{id}', 'ExamController', 'delete');
        $this->addRoute('GET', '/api/exams/by_faculty/{faculty_id}', 'ExamController', 'getByFaculty');
        $this->addRoute('GET', '/api/exams/for_student/{year_level}/{section}', 'ExamController', 'getForStudent');
        $this->addRoute('GET', '/api/exams/by_subject/{subject_id}', 'ExamController', 'getBySubject');
        $this->addRoute('GET', '/api/exams/active', 'ExamController', 'getActive');
        $this->addRoute('PUT', '/api/exams/{id}/status', 'ExamController', 'updateStatus');

        // Question routes
        $this->addRoute('GET', '/api/questions', 'QuestionController', 'index');
        $this->addRoute('GET', '/api/questions/{id}', 'QuestionController', 'show');
        $this->addRoute('GET', '/api/exams/{exam_id}/questions', 'QuestionController', 'getByExam');
        $this->addRoute('POST', '/api/questions', 'QuestionController', 'store');
        $this->addRoute('PUT', '/api/questions/{id}', 'QuestionController', 'update');
        $this->addRoute('DELETE', '/api/questions/{id}', 'QuestionController', 'delete');
        $this->addRoute('POST', '/api/exams/{exam_id}/questions/reorder', 'QuestionController', 'reorder');

        // Exam Result routes
        $this->addRoute('GET', '/api/results', 'ExamResultController', 'index');
        $this->addRoute('GET', '/api/results/{id}', 'ExamResultController', 'show');
        $this->addRoute('POST', '/api/results', 'ExamResultController', 'store');
        $this->addRoute('PUT', '/api/results/{id}', 'ExamResultController', 'update');
        $this->addRoute('DELETE', '/api/results/{id}', 'ExamResultController', 'delete');
        $this->addRoute('GET', '/api/exams/{exam_id}/results', 'ExamResultController', 'getByExam');
        $this->addRoute('GET', '/api/students/{student_id}/results', 'ExamResultController', 'getByStudent');
    }

    /**
     * Add a route to the router
     */
    private function addRoute(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Dispatch the request to the appropriate controller method
     */
    public function dispatch(string $method, string $uri): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->buildPattern($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                $controller = $this->container->get($route['controller']);
                $action = $route['action'];
                
                // Extract parameters from URL
                $params = array_slice($matches, 1);
                
                // Call the controller method with parameters
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        // No route found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }

    /**
     * Build regex pattern from route path
     */
    private function buildPattern(string $path): string
    {
        // Convert /api/users/{id} to /api/users/([^/]+)
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}