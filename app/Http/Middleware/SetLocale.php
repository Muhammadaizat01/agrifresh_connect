<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check URL query string (?lang=ms or ?lang=en) like htdocs
        if ($request->has('lang')) {
            $queryLang = strtolower(trim((string)$request->query('lang')));
            if (in_array($queryLang, ['en', 'ms'])) {
                Session::put('locale', $queryLang);
                App::setLocale($queryLang);
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['lang'] = $queryLang;
                }
                return $next($request);
            }
        }

        // 2. Check Laravel session or PHP native session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'ms'])) {
            App::setLocale($_SESSION['lang']);
        }

        return $next($request);
    }
}