<?php

namespace App\Providers\Filament;

use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->colors([
                'primary' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\Filament\App\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->databaseNotifications(position: DatabaseNotificationsPosition::Topbar)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        // Injection des balises PWA dans le HEAD de Filament
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn (): string => Blade::render('
                <link rel="manifest" href="/manifest.json">
                <meta name="theme-color" content="#1d4ed8">
                <link rel="apple-touch-icon" href="/apple-touch-icon.png">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
            '),
        );

        // Injection du script Service Worker à la fin du BODY
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render('
                <script>
                    if ("serviceWorker" in navigator) {
                        window.addEventListener("load", () => {
                            navigator.serviceWorker.register("/sw.js")
                                .then(reg => console.log("SW Filament enregistré !"))
                                .catch(err => console.log("Erreur SW Filament", err));
                        });
                    }
                </script>
            '),
        );
    }
}
