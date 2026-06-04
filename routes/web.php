<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProfessionalController as AdminProfessionalController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap']);
Route::get('/robots.txt', [SitemapController::class, 'robots']);

// Статик хуудсууд
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

// Нийтэд харагдах мэдээ
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post:slug}', [NewsController::class, 'show'])->name('news.show');

// Эвент (нийтэд)
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// Зар (Kleinanzeigen маягийн marketplace)
Route::get('/zar', [ListingController::class, 'index'])->name('zar.index');

// Мэргэжилтний лавлах (нийтэд)
Route::get('/professionals', [ProfessionalController::class, 'index'])->name('professionals.index');

// Баннер — клик/харагдсан тоолуур
Route::get('/banners/{banner}/click', [BannerController::class, 'click'])->name('banners.click');
Route::post('/banners/{banner}/impression', [BannerController::class, 'impression'])->name('banners.impression');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Тасалбар захиалга / төлбөр
    Route::post('/events/{event:slug}/checkout', [EventController::class, 'checkout'])->name('events.checkout');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::get('/my/tickets', [OrderController::class, 'myTickets'])->name('orders.my-tickets');

    // Зар — хэрэглэгчийн удирдлага (нийтэд харагдах show-аас дээгүүр байрлуулна)
    Route::get('/my/zar', [ListingController::class, 'myListings'])->name('zar.my');
    Route::get('/zar/new', [ListingController::class, 'create'])->name('zar.create');
    Route::post('/zar', [ListingController::class, 'store'])->name('zar.store');
    Route::get('/zar/{listing}/edit', [ListingController::class, 'edit'])->name('zar.edit');
    Route::put('/zar/{listing}', [ListingController::class, 'update'])->name('zar.update');
    Route::delete('/zar/{listing}', [ListingController::class, 'destroy'])->name('zar.destroy');
    Route::patch('/zar/{listing}/status', [ListingController::class, 'markStatus'])->name('zar.status');
    Route::post('/zar/{listing}/promote', [ListingController::class, 'promote'])->name('zar.promote');

    // Хадгалсан зар (favorite)
    Route::get('/my/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/zar/{listing}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Зар гомдол мэдүүлэх
    Route::post('/zar/{listing}/report', [ReportController::class, 'store'])->name('reports.store');

    // Мэдэгдэл уншсан болгох
    Route::post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');

    // Зурвас (хэрэглэгч хооронд)
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [\App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/zar/{listing}/message', [\App\Http\Controllers\MessageController::class, 'start'])->name('messages.start');

    // Сэтгэгдэл
    Route::post('/news/{post:slug}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/react', [CommentController::class, 'react'])->name('comments.react');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Мэргэжилтэн — өөрийн профайл (нийтэд харагдах show-аас дээгүүр)
    Route::get('/professionals/apply', [ProfessionalController::class, 'create'])->name('professionals.create');
    Route::post('/professionals', [ProfessionalController::class, 'store'])->name('professionals.store');
    Route::get('/my/professional', [ProfessionalController::class, 'mine'])->name('professionals.mine');
    Route::get('/professionals/{professional}/edit', [ProfessionalController::class, 'edit'])->name('professionals.edit');
    Route::put('/professionals/{professional}', [ProfessionalController::class, 'update'])->name('professionals.update');
    Route::post('/professionals/{professional}/promote', [ProfessionalController::class, 'promote'])->name('professionals.promote');
    Route::post('/professionals/{professional:slug}/reveal', [ProfessionalController::class, 'reveal'])->name('professionals.reveal');
});

// Зарын дэлгэрэнгүй (динамик параметр тул бусад /zar/* замуудаас доор)
Route::get('/zar/{listing:slug}', [ListingController::class, 'show'])->name('zar.show');

// Мэргэжилтний дэлгэрэнгүй (динамик параметр тул доор)
Route::get('/professionals/{professional:slug}', [ProfessionalController::class, 'show'])->name('professionals.show');

// Админ хэсэг (зөвхөн admin дүртэй)
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('posts', AdminPostController::class)->except('show');

        Route::resource('banners', AdminBannerController::class)->except('show');
        Route::patch('banners/{banner}/status', [AdminBannerController::class, 'setStatus'])->name('banners.status');
        Route::post('banners/{banner}/pay', [AdminBannerController::class, 'pay'])->name('banners.pay');

        Route::get('events/{event}/sales', [AdminEventController::class, 'sales'])->name('events.sales');
        Route::resource('events', AdminEventController::class)->except('show');

        Route::get('check-in', [CheckInController::class, 'index'])->name('check-in');
        Route::post('check-in', [CheckInController::class, 'verify'])->name('check-in.verify');

        // Модерац — зарын гомдол
        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::post('reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])->name('reports.dismiss');
        Route::post('reports/{report}/hide', [AdminReportController::class, 'hide'])->name('reports.hide');
        Route::delete('reports/{report}/listing', [AdminReportController::class, 'destroyListing'])->name('reports.destroy-listing');

        // Хэрэглэгч удирдах
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');

        // Ангилал удирдах (listing | news)
        Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories/{type}', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{type}/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{type}/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        // Засварлагчийн зураг upload
        Route::post('media', [MediaController::class, 'store'])->name('media.store');

        // Сэтгэгдлийн модерац
        Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::post('comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
        Route::post('comments/{comment}/spam', [AdminCommentController::class, 'spam'])->name('comments.spam');
        Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');

        // Мэргэжилтний лавлах удирдах
        Route::get('professionals', [AdminProfessionalController::class, 'index'])->name('professionals.index');
        Route::post('professionals/{professional}/approve', [AdminProfessionalController::class, 'approve'])->name('professionals.approve');
        Route::post('professionals/{professional}/verify', [AdminProfessionalController::class, 'verify'])->name('professionals.verify');
        Route::post('professionals/{professional}/feature', [AdminProfessionalController::class, 'feature'])->name('professionals.feature');
        Route::post('professionals/{professional}/deactivate', [AdminProfessionalController::class, 'deactivate'])->name('professionals.deactivate');
        Route::delete('professionals/{professional}', [AdminProfessionalController::class, 'destroy'])->name('professionals.destroy');

        // Сайтын тохиргоо
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });

require __DIR__.'/auth.php';
