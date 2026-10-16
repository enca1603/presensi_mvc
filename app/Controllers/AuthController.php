<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Services\AuditLogService;
use App\Services\LoginThrottleService;

class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->view('auth.login', [
            'title' => 'Login',
            'error' => Session::getFlash('error'),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        if (!Csrf::verify($request->input('_csrf_token'))) {
            Session::flash('error', 'Sesi form kedaluwarsa, silakan coba lagi.');
            $this->redirect('/login');
            return;
        }

        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        if ($username === '' || $password === '') {
            Session::flash('error', 'Username dan password wajib diisi.');
            $this->redirect('/login');
            return;
        }

        if (LoginThrottleService::tooManyAttempts($username)) {
            $wait = (int) ceil(LoginThrottleService::remainingSeconds($username) / 60);
            Session::flash('error', "Terlalu banyak percobaan gagal. Coba lagi dalam {$wait} menit.");
            $this->redirect('/login');
            return;
        }

        $result = Auth::attempt($username, $password);

        if (!$result['success']) {
            AuditLogService::log('login_failed', 'auth', null, "Percobaan login gagal untuk username: {$username}");
            Session::flash('error', $result['message']);
            $this->redirect('/login');
            return;
        }

        AuditLogService::log('login', 'auth', (string) $result['user']['id'], 'Login berhasil');
        $this->redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        AuditLogService::log('logout', 'auth', (string) Auth::id(), 'Logout');
        Auth::logout();
        $this->redirect('/login');
    }
}
