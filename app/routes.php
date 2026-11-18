<?php
declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\FormController;
use App\Controllers\SiteController;
use App\Controllers\TrackController;
use App\Core\Http;
use App\Core\Router;
use App\Core\Session;

$path = Http::path();
$isAdmin = $path === '/admin' || str_starts_with($path, '/admin/');

Http::securityHeaders($isAdmin || str_starts_with($path, '/t/') || str_starts_with($path, '/track'));

$router = new Router();

if ($isAdmin) {
    Session::start('admin');
    $router->get('/admin', fn () => Http::redirect('/admin/inbox'));
    $router->get('/admin/login', [Admin\AuthController::class, 'loginForm']);
    $router->post('/admin/login', [Admin\AuthController::class, 'login']);
    $router->get('/admin/mfa', [Admin\AuthController::class, 'mfaForm']);
    $router->post('/admin/mfa', [Admin\AuthController::class, 'mfa']);
    $router->get('/admin/mfa/setup', [Admin\AuthController::class, 'setupForm']);
    $router->post('/admin/mfa/setup', [Admin\AuthController::class, 'setup']);
    $router->post('/admin/logout', [Admin\AuthController::class, 'logout']);
    $router->get('/admin/account/password', [Admin\AuthController::class, 'passwordForm']);
    $router->post('/admin/account/password', [Admin\AuthController::class, 'password']);

    $router->get('/admin/inbox', [Admin\InboxController::class, 'index']);
    $router->post('/admin/enquiries/{id}/status', [Admin\InboxController::class, 'status']);
    $router->post('/admin/enquiries/{id}/convert', [Admin\InboxController::class, 'convert']);
    $router->post('/admin/enquiries/{id}/delete', [Admin\InboxController::class, 'delete']);

    $router->get('/admin/shipments', [Admin\ShipmentController::class, 'index']);
    $router->get('/admin/shipments/new', [Admin\ShipmentController::class, 'createForm']);
    $router->post('/admin/shipments/new', [Admin\ShipmentController::class, 'create']);
    $router->get('/admin/shipments/{id}', [Admin\ShipmentController::class, 'show']);
    $router->get('/admin/shipments/{id}/edit', [Admin\ShipmentController::class, 'edit']);
    $router->post('/admin/shipments/{id}', [Admin\ShipmentController::class, 'update']);
    $router->post('/admin/shipments/{id}/stage', [Admin\ShipmentController::class, 'stage']);
    $router->post('/admin/shipments/{id}/photos', [Admin\ShipmentController::class, 'upload']);
    $router->post('/admin/shipments/{id}/link', [Admin\ShipmentController::class, 'newLink']);
    $router->post('/admin/shipments/{id}/link/revoke', [Admin\ShipmentController::class, 'revokeLink']);
    $router->post('/admin/shipments/{id}/delete', [Admin\ShipmentController::class, 'delete']);
    $router->get('/admin/photos/{id}', [Admin\ShipmentController::class, 'photo']);
    $router->post('/admin/photos/{id}/delete', [Admin\ShipmentController::class, 'deletePhoto']);

    $router->get('/admin/staff', [Admin\StaffController::class, 'index']);
    $router->post('/admin/staff', [Admin\StaffController::class, 'create']);
    $router->post('/admin/staff/{id}/toggle', [Admin\StaffController::class, 'toggle']);
    $router->post('/admin/staff/{id}/reset', [Admin\StaffController::class, 'reset']);
    $router->get('/admin/audit', [Admin\StaffController::class, 'audit']);
} else {
    $router->get('/', [SiteController::class, 'home']);
    $router->get('/services', [SiteController::class, 'services']);
    $router->get('/how-it-works', [SiteController::class, 'howItWorks']);
    $router->get('/about', [SiteController::class, 'about']);
    $router->get('/contact', [SiteController::class, 'contact']);
    $router->get('/privacy', [SiteController::class, 'privacy']);
    $router->get('/terms', [SiteController::class, 'terms']);

    $router->get('/quote', [FormController::class, 'quoteForm']);
    $router->post('/quote', [FormController::class, 'quote']);
    $router->get('/update', [FormController::class, 'updateForm']);
    $router->post('/update', [FormController::class, 'update']);
    $router->get('/thanks', [FormController::class, 'thanks']);

    if (customer_tracking()) {
        $router->get('/track', [TrackController::class, 'form']);
        $router->post('/track', [TrackController::class, 'lookup']);
        $router->get('/track/view', [TrackController::class, 'view']);
        $router->get('/track/photo/{id}', [TrackController::class, 'photo']);
        $router->get('/t/{token}', [TrackController::class, 'byToken']);
    }
}

$router->dispatch(Http::method(), $path);
