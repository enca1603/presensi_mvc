<?php

namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    protected function validate(array $data, array $rules): Validator
    {
        $validator = Validator::make($data);
        $validator->validate($rules);
        return $validator;
    }

    protected function requireCsrf(): void
    {
        $token = $this->request->input('_csrf_token');
        if (!Csrf::verify($token)) {
            Session::flash('error', 'Sesi tidak valid, silakan coba lagi.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    protected function back(string $fallback = '/'): void
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? $fallback);
    }
}
