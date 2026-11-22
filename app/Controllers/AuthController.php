<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\User;
use Database;
use PDOException;

final class AuthController
{
    public function customerLogin(): void
    {
        session_start();

        if ($this->hasRole('customer')) {
            header('Location: ' . route_url('dashboard'));
            exit;
        }

        $error = '';
        $email = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $error = 'Please enter your email and password.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                try {
                    $user = (new User((new Database())->connect()))
                        ->findByEmail($email);

                    if (
                        $user &&
                        $user['role'] === 'customer' &&
                        password_verify($password, $user['password_hash'])
                    ) {
                        $this->signIn($user, 'customer');
                        header('Location: ' . route_url('dashboard'));
                        exit;
                    }

                    $error = 'Invalid email or password.';
                } catch (PDOException $e) {
                    error_log('Customer login error: ' . $e->getMessage());
                    $error = 'Unable to log in. Please try again.';
                }
            }
        }

        View::render('customer/login', compact('error', 'email'));
    }

    public function register(): void
    {
        session_start();
        $errors = [];
        $fullName = '';
        $email = '';
        $phone = '';

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $csrfToken = $_POST['csrf_token'] ?? '';

            if (
                !is_string($csrfToken) ||
                !hash_equals($_SESSION['csrf_token'], $csrfToken)
            ) {
                $errors[] = 'Invalid form submission. Please try again.';
            }

            if ($fullName === '') {
                $errors[] = 'Full name is required.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if ($password === '' || strlen($password) < 8) {
                $errors[] = 'Password must contain at least 8 characters.';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }

            if (!$errors) {
                try {
                    $users = new User((new Database())->connect());
                    if ($users->emailExists($email)) {
                        $errors[] = 'This email address is already registered.';
                    } else {
                        $users->createCustomer(
                            $fullName,
                            $email,
                            $phone !== '' ? $phone : null,
                            password_hash($password, PASSWORD_DEFAULT)
                        );
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        header('Location: ' . route_url('login?registered=1'));
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log('Customer registration error: ' . $e->getMessage());
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }

        View::render('customer/register', compact(
            'errors',
            'fullName',
            'email',
            'phone'
        ));
    }

    public function sellerLogin(): void
    {
        session_start();
        if ($this->hasRole('seller')) {
            header('Location: ' . route_url('seller/dashboard'));
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            try {
                $user = (new User((new Database())->connect()))
                    ->findByEmail($email);

                if (
                    $user &&
                    $user['role'] === 'seller' &&
                    password_verify($password, $user['password_hash'])
                ) {
                    $this->signIn($user, 'seller');
                    header('Location: ' . route_url('seller/dashboard'));
                    exit;
                }

                $error = 'Invalid email or password.';
            } catch (PDOException $e) {
                error_log('Seller login error: ' . $e->getMessage());
                $error = 'Unable to log in. Please try again.';
            }
        }

        View::render('seller/login', compact('error'));
    }

    public function customerLogout(): void
    {
        $this->logout('customer', route_url('?logged_out=1'));
    }

    public function sellerLogout(): void
    {
        $this->logout('seller', route_url('seller/login?logged_out=1'));
    }

    private function logout(string $role, string $redirect): void
    {
        session_start();

        if ($role === 'seller' && !$this->hasRole('seller')) {
            header('Location: ' . route_url('seller/login'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit(
                $role === 'seller'
                    ? 'Use the logout button to end your seller session.'
                    : 'Use the logout button to end your session.'
            );
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $formToken = $_POST['csrf_token'] ?? '';
        if (
            !is_string($sessionToken) ||
            !is_string($formToken) ||
            $sessionToken === '' ||
            !hash_equals($sessionToken, $formToken)
        ) {
            http_response_code(403);
            exit('Invalid logout request. Refresh the page and try again.');
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $cookieParams = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookieParams['path'],
                'domain' => $cookieParams['domain'],
                'secure' => $cookieParams['secure'],
                'httponly' => $cookieParams['httponly'],
                'samesite' => $cookieParams['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();
        header('Location: ' . $redirect);
        exit;
    }

    private function signIn(array $user, string $role): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = $user['full_name'];
    }

    private function hasRole(string $role): bool
    {
        return isset($_SESSION['user_id'])
            && ($_SESSION['role'] ?? '') === $role;
    }
}
