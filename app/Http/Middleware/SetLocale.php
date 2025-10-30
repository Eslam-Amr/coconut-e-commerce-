<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected array $availableLocales = ['en', 'ar'];
    protected string $defaultLocale = 'en';
    
    public function handle(Request $request, Closure $next): Response
    {
        // dd($request->header('Accept-Language'));
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);
    // dd($locale);
        return $next($request);
    }
    
    protected function resolveLocale(Request $request): string
    {
        $user = auth()->user() ?? auth('admin')->user();
        // dd($user);
        if ($user && $user->language) {
            return $this->validateLocale($user->language);
        }
    
        $locale = $request->header('Accept-Language')
            ?? $request->header('X-Locale')
            ?? $request->query('lang')
            ?? app()->getLocale()
            ?? $this->defaultLocale;
        // dd($locale);
        return $this->validateLocale($locale);
    }
    
    protected function validateLocale(?string $locale): string
    {
        return in_array($locale, $this->availableLocales, true)
            ? $locale
            : $this->defaultLocale;
    }
}
