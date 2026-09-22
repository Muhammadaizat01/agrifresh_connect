<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, string $lang)
    {
        if (in_array($lang, ['en', 'ms'])) {
            Session::put('locale', $lang);
            App::setLocale($lang);
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['lang'] = $lang;
            }
        }
        $previous = url()->previous();
        if ($previous && $previous !== url()->current()) {
            $urlParts = parse_url($previous);
            $query = [];
            if (!empty($urlParts['query'])) {
                parse_str($urlParts['query'], $query);
            }
            $query['lang'] = $lang;
            $newUrl = ($urlParts['scheme'] ?? 'http') . '://' . ($urlParts['host'] ?? 'localhost') . (isset($urlParts['port']) ? ':' . $urlParts['port'] : '') . ($urlParts['path'] ?? '/') . '?' . http_build_query($query);
            return redirect($newUrl);
        }
        return redirect('/?lang=' . $lang);
    }
}