<?php

namespace App\Config;

use App\DAO\Impl\UserDAOImpl;
use App\DAO\Impl\SubjectDAOImpl;
use App\DAO\Impl\ExamDAOImpl;
use App\DAO\Impl\QuestionDAOImpl;
use App\DAO\Impl\ExamResultDAOImpl;
use App\Service\Impl\UserServiceImpl;
use App\Service\Impl\SubjectServiceImpl;
use App\Service\Impl\ExamServiceImpl;
use App\Service\Impl\QuestionServiceImpl;
use App\Service\Impl\ExamResultServiceImpl;
use App\Service\Impl\AuthServiceImpl;
use App\Controller\UserController;
use App\Controller\SubjectController;
use App\Controller\ExamController;
use App\Controller\QuestionController;
use App\Controller\ExamResultController;
use App\Controller\AuthController;

/**
 * Service Container
 * 
 * Manages dependency injection for all services, DAOs, and controllers.
 * Provides a centralized way to create and manage object instances.
 */
class ServiceContainer
{
    private array $services = [];

    public function __construct()
    {
        $this->registerServices();
    }

    /**
     * Register all services in the container
     */
    private function registerServices(): void
    {
        // Register DAOs
        $this->services['UserDAO'] = fn() => new UserDAOImpl();
        $this->services['SubjectDAO'] = fn() => new SubjectDAOImpl();
        $this->services['ExamDAO'] = fn() => new ExamDAOImpl();
        $this->services['QuestionDAO'] = fn() => new QuestionDAOImpl();
        $this->services['ExamResultDAO'] = fn() => new ExamResultDAOImpl();

        // Register Services
        $this->services['UserService'] = fn() => new UserServiceImpl($this->get('UserDAO'));
        $this->services['SubjectService'] = fn() => new SubjectServiceImpl($this->get('SubjectDAO'));
        $this->services['ExamService'] = fn() => new ExamServiceImpl($this->get('ExamDAO'));
        $this->services['QuestionService'] = fn() => new QuestionServiceImpl($this->get('QuestionDAO'));
        $this->services['ExamResultService'] = fn() => new ExamResultServiceImpl($this->get('ExamResultDAO'));
        $this->services['AuthService'] = fn() => new AuthServiceImpl($this->get('UserService'));

        // Register Controllers
        $this->services['UserController'] = fn() => new UserController($this->get('UserService'), $this->get('AuthService'));
        $this->services['SubjectController'] = fn() => new SubjectController($this->get('SubjectService'), $this->get('AuthService'));
        $this->services['ExamController'] = fn() => new ExamController($this->get('ExamService'), $this->get('AuthService'));
        $this->services['QuestionController'] = fn() => new QuestionController($this->get('QuestionService'), $this->get('AuthService'));
        $this->services['ExamResultController'] = fn() => new ExamResultController($this->get('ExamResultService'), $this->get('AuthService'));
        $this->services['AuthController'] = fn() => new AuthController($this->get('AuthService'));
    }

    /**
     * Get a service from the container
     */
    public function get(string $serviceName)
    {
        if (!isset($this->services[$serviceName])) {
            throw new \Exception("Service '$serviceName' not found");
        }
        return $this->services[$serviceName]();
    }

    /**
     * Check if a service exists in the container
     */
    public function has(string $serviceName): bool
    {
        return isset($this->services[$serviceName]);
    }
}