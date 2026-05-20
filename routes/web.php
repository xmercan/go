<?php

/**
 * GO! V1 — Web Routes
 *
 * Sözdizimi:
 *   $router->get('path', 'Namespace\Controller@method');
 *   $router->post('path', 'Namespace\Controller@method');
 *
 * Parametreli route:
 *   $router->get('projeler/{uuid}', 'Customer\ProjectController@show');
 */

use GO\Core\Router;

/** @var Router $router */

// ─── Public / Landing ─────────────────────────────────────────────────────────
$router->get('/',                    'LandingController@home');
$router->get('sektorler/{slug}',     'LandingController@sectorDetail');
$router->get('iletisim',             'LandingController@contact');
$router->get('blog',                 'LandingController@blog');
$router->get('kvkk',                 'LandingController@kvkk');
$router->get('gizlilik-politikasi',  'LandingController@privacy');
$router->get('kullanim-kosullari',   'LandingController@terms');

// ─── Müşteri Auth ─────────────────────────────────────────────────────────────
$router->get('giris',                'Customer\AuthController@showLogin');
$router->post('giris',               'Customer\AuthController@login');
$router->get('cikis',                'Customer\AuthController@logout');
$router->get('kayit',                'Customer\AuthController@showRegister');
$router->post('kayit',               'Customer\AuthController@register');
$router->get('sifremi-unuttum',      'Customer\AuthController@showForgotPassword');
$router->post('sifremi-unuttum',     'Customer\AuthController@forgotPassword');
$router->get('sifre-sifirla/{token}','Customer\AuthController@showResetPassword');
$router->post('sifre-sifirla/{token}','Customer\AuthController@resetPassword');

// ─── Müşteri Panel ────────────────────────────────────────────────────────────
$router->get('panel',                'Customer\PanelController@dashboard');
$router->get('panel/ayarlar',        'Customer\PanelController@settings');
$router->post('panel/ayarlar',       'Customer\PanelController@updateSettings');

// Projeler
$router->get('panel/projeler',           'Customer\ProjectController@index');
$router->get('panel/projeler/{uuid}',    'Customer\ProjectController@show');
$router->get('panel/projeler/{uuid}/export', 'Customer\ProjectController@export');

// Chat
$router->get('chat',                 'ChatController@index');
$router->get('chat/{uuid}',          'ChatController@project');
$router->post('chat/send',           'ChatController@send');
$router->get('chat/gecmis/{uuid}',   'ChatController@history');

// Domain
$router->get('panel/domain',         'Customer\DomainController@index');
$router->post('panel/domain/talep',  'Customer\DomainController@createRequest');

// Hosting
$router->get('panel/hosting',        'Customer\HostingController@index');
$router->post('panel/hosting/talep', 'Customer\HostingController@createRequest');

// Yazılım
$router->get('panel/yazilim',        'Customer\SoftwareController@index');
$router->post('panel/yazilim/talep', 'Customer\SoftwareController@createRequest');

// Faturalar
$router->get('panel/faturalar',                 'Customer\InvoiceController@index');
$router->get('panel/faturalar/{uuid}',          'Customer\InvoiceController@show');
$router->post('panel/faturalar/{uuid}/bildir',  'Customer\InvoiceController@notifyPayment');

// Destek
$router->get('panel/destek',                    'Customer\SupportController@index');
$router->get('panel/destek/yeni',               'Customer\SupportController@create');
$router->post('panel/destek',                   'Customer\SupportController@store');
$router->get('panel/destek/{uuid}',             'Customer\SupportController@show');
$router->post('panel/destek/{uuid}/yanit',      'Customer\SupportController@reply');

// Bildirimler (stub)
$router->get('panel/bildirimler',    'Customer\NotificationController@index');

// ─── Admin ────────────────────────────────────────────────────────────────────
$router->get('admin/giris',          'Admin\AuthController@showLogin');
$router->post('admin/giris',         'Admin\AuthController@login');
$router->get('admin/cikis',          'Admin\AuthController@logout');

$router->get('admin/dashboard',      'Admin\DashboardController@index');

// Projeler
$router->get('admin/projeler',       'Admin\ProjectController@index');
$router->get('admin/projeler/{uuid}','Admin\ProjectController@show');
$router->post('admin/projeler/{uuid}/durum', 'Admin\ProjectController@updateStatus');
$router->post('admin/projeler/{uuid}/not',   'Admin\ProjectController@addNote');

// Kullanıcılar
$router->get('admin/kullanicilar',   'Admin\UserController@index');
$router->get('admin/kullanicilar/{id}','Admin\UserController@show');
$router->post('admin/kullanicilar/{id}/durum','Admin\UserController@updateStatus');

// Faturalar
$router->get('admin/faturalar',      'Admin\InvoiceController@index');
$router->get('admin/faturalar/{uuid}','Admin\InvoiceController@show');
$router->post('admin/faturalar/olustur','Admin\InvoiceController@create');
$router->post('admin/faturalar/{uuid}/onayla','Admin\InvoiceController@approve');

// Destek
$router->get('admin/destek',         'Admin\SupportController@index');
$router->get('admin/destek/{uuid}',  'Admin\SupportController@show');
$router->post('admin/destek/{uuid}/yanit','Admin\SupportController@reply');
$router->post('admin/destek/{uuid}/kapat','Admin\SupportController@close');

// Domain / Hosting / Yazılım (listeler)
$router->get('admin/domain',         'Admin\DomainController@index');
$router->get('admin/hosting',        'Admin\HostingController@index');
$router->get('admin/yazilim',        'Admin\SoftwareController@index');

// Kanban
$router->get('admin/kanban',         'Admin\KanbanController@index');
$router->post('admin/kanban/guncelle','Admin\KanbanController@updateStatus');

// Loglar
$router->get('admin/loglar',         'Admin\LogController@index');
$router->get('admin/silinen',        'Admin\LogController@deleted');

// Ayarlar
$router->get('admin/ayarlar',        'Admin\SettingsController@index');
$router->post('admin/ayarlar/site',  'Admin\SettingsController@saveSite');
$router->post('admin/ayarlar/smtp',  'Admin\SettingsController@saveSmtp');
$router->post('admin/ayarlar/smtp-test','Admin\SettingsController@testSmtp');
$router->post('admin/ayarlar/ozellikler','Admin\SettingsController@saveFeatureFlags');

// Install
$router->get('install',              'InstallController@index');

// ─── Hata sayfaları ──────────────────────────────────────────────────────────
$router->get('404',                  'ErrorController@notFound');
$router->get('500',                  'ErrorController@serverError');
