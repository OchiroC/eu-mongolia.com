<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\EmbassyController as AdminEmbassyController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\GuideController as AdminGuideController;
use App\Http\Controllers\Admin\JobController as AdminJobController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProfessionalController as AdminProfessionalController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\RideController as AdminRideController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\EmbassyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\RideController;
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

// Заавар / Гарын авлага (нийтэд)
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{guide:slug}', [GuideController::class, 'show'])->name('guides.show');

// Ажлын байр (нийтэд)
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

// Асуулт хариулт (нийтэд)
Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');

// Элчин сайдын яам / яаралтай тусламж (нийтэд)
Route::get('/embassy', [EmbassyController::class, 'index'])->name('embassy.index');

// Хамтдаа аялах (нийтэд)
Route::get('/rides', [RideController::class, 'index'])->name('rides.index');

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

    // Хамтдаа аялах (нийтэд харагдах show-аас дээгүүр)
    Route::get('/rides/new', [RideController::class, 'create'])->name('rides.create');
    Route::post('/rides', [RideController::class, 'store'])->name('rides.store');
    Route::get('/my/rides', [RideController::class, 'myRides'])->name('rides.my');
    Route::get('/rides/{ride}/edit', [RideController::class, 'edit'])->name('rides.edit');
    Route::put('/rides/{ride}', [RideController::class, 'update'])->name('rides.update');
    Route::post('/rides/{ride}/close', [RideController::class, 'close'])->name('rides.close');
    Route::delete('/rides/{ride}', [RideController::class, 'destroy'])->name('rides.destroy');

    // Асуулт хариулт (нийтэд харагдах show-аас дээгүүр)
    Route::get('/questions/ask', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('/questions/{question:slug}/answers', [AnswerController::class, 'store'])->name('answers.store');
    Route::post('/answers/{answer}/vote', [AnswerController::class, 'vote'])->name('answers.vote');
    Route::post('/answers/{answer}/accept', [AnswerController::class, 'accept'])->name('answers.accept');
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');

    // Ажлын байр — өөрийн зар (нийтэд харагдах show-аас дээгүүр)
    Route::get('/jobs/new', [JobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::get('/my/jobs', [JobController::class, 'myJobs'])->name('jobs.my');
    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::post('/jobs/{job}/close', [JobController::class, 'close'])->name('jobs.close');
    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

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

// Ажлын байрны дэлгэрэнгүй (динамик параметр тул доор)
Route::get('/jobs/{job:slug}', [JobController::class, 'show'])->name('jobs.show');

// Асуултын дэлгэрэнгүй (динамик параметр тул доор)
Route::get('/questions/{question:slug}', [QuestionController::class, 'show'])->name('questions.show');

// Аяллын дэлгэрэнгүй (динамик параметр тул доор)
Route::get('/rides/{ride}', [RideController::class, 'show'])->name('rides.show');

// Админ хэсэг — admin + ажилтны дүрүүд (editor/moderator/organizer/advertiser)
Route::middleware(['auth', 'role:admin|editor|moderator|organizer|advertiser'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Засварлагчийн зураг upload (бүх ажилтан)
        Route::post('media', [MediaController::class, 'store'])->name('media.store');

        // Сэтгүүлч (editor) + admin: Мэдээ, Guide
        Route::middleware('role:admin|editor')->group(function () {
            Route::resource('posts', AdminPostController::class)->except('show');
            Route::resource('guides', AdminGuideController::class)->except('show');
        });

        // Модератор (moderator) + admin: сэтгэгдэл, гомдол, асуулт
        Route::middleware('role:admin|moderator')->group(function () {
            Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
            Route::post('comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
            Route::post('comments/{comment}/spam', [AdminCommentController::class, 'spam'])->name('comments.spam');
            Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');

            Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::post('reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])->name('reports.dismiss');
            Route::post('reports/{report}/hide', [AdminReportController::class, 'hide'])->name('reports.hide');
            Route::delete('reports/{report}/listing', [AdminReportController::class, 'destroyListing'])->name('reports.destroy-listing');

            Route::get('questions', [AdminQuestionController::class, 'index'])->name('questions.index');
            Route::delete('questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');
        });

        // Эвент зохион байгуулагч (organizer) + admin
        Route::middleware('role:admin|organizer')->group(function () {
            Route::get('events/{event}/sales', [AdminEventController::class, 'sales'])->name('events.sales');
            Route::resource('events', AdminEventController::class)->except('show');
            Route::get('check-in', [CheckInController::class, 'index'])->name('check-in');
            Route::post('check-in', [CheckInController::class, 'verify'])->name('check-in.verify');
        });

        // Сурталчлагч (advertiser) + admin: баннер
        Route::middleware('role:admin|advertiser')->group(function () {
            Route::resource('banners', AdminBannerController::class)->except('show');
            Route::patch('banners/{banner}/status', [AdminBannerController::class, 'setStatus'])->name('banners.status');
            Route::post('banners/{banner}/pay', [AdminBannerController::class, 'pay'])->name('banners.pay');
        });

        // Зөвхөн admin
        Route::middleware('role:admin')->group(function () {
            // Хэрэглэгч (ажилтан) удирдах
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
            Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');

            // Ангилал удирдах (listing | news | professional)
            Route::get('categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('categories/{type}', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{type}/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{type}/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

            // Ажлын байр
            Route::get('jobs', [AdminJobController::class, 'index'])->name('jobs.index');
            Route::post('jobs/{job}/close', [AdminJobController::class, 'close'])->name('jobs.close');
            Route::delete('jobs/{job}', [AdminJobController::class, 'destroy'])->name('jobs.destroy');

            // Хамтдаа аялах
            Route::get('rides', [AdminRideController::class, 'index'])->name('rides.index');
            Route::post('rides/{ride}/close', [AdminRideController::class, 'close'])->name('rides.close');
            Route::delete('rides/{ride}', [AdminRideController::class, 'destroy'])->name('rides.destroy');

            // Элчин сайдын яам / тусламж
            Route::get('embassies', [AdminEmbassyController::class, 'index'])->name('embassies.index');
            Route::post('embassies', [AdminEmbassyController::class, 'store'])->name('embassies.store');
            Route::put('embassies/{embassy}', [AdminEmbassyController::class, 'update'])->name('embassies.update');
            Route::delete('embassies/{embassy}', [AdminEmbassyController::class, 'destroy'])->name('embassies.destroy');

            // Мэргэжлийн үйлчилгээ удирдах
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
    });

require __DIR__.'/auth.php';
