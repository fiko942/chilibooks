<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class AuthController extends BaseController
{
    protected function userModel(): UserModel
    {
        return new UserModel();
    }

    /**
     * @return array{user: array<string, mixed>|null, database_error: bool}
     */
    protected function lookupUserByEmail(string $email): array
    {
        try {
            return [
                'user' => $this->userModel()->where('email', $email)->first(),
                'database_error' => false,
            ];
        } catch (DatabaseException $exception) {
            $message = 'Login lookup failed: ' . $exception->getMessage();
            log_message('error', $message);
            error_log($message);

            if (defined('STDERR')) {
                fwrite(STDERR, $message . PHP_EOL);
            }

            return [
                'user' => null,
                'database_error' => true,
            ];
        }
    }

    public function login()
    {
        if (session('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function faq()
    {
        if (session('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/faq');
    }

    public function attemptLogin()
    {
        $email = (string) $this->request->getPost('email');
        $lookup = $this->lookupUserByEmail($email);
        $user   = $lookup['user'];

        if ($lookup['database_error']) {
            return redirect()->to(site_url('login'))->withInput()->with('error', 'Sistem login sedang bermasalah. Coba lagi nanti.');
        }

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->to(site_url('login'))->withInput()->with('error', 'Email atau password salah.');
        }

        session()->set([
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'user_email' => $user['email'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
