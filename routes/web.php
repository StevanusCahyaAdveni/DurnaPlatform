<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
// use App\Livewire\Dashboard;
use App\Livewire\UserDashboard;
use App\Livewire\UserClass;
use App\Livewire\UserClassDetail;
use App\Livewire\OurClass;
use App\Livewire\OurClassPreview;
use App\Livewire\TeacherClass;
use App\Livewire\TeacherTask;
use App\Livewire\TeacherExam;
use App\Livewire\UpgradeRole;
use App\Livewire\UserTaskAnswer;
use App\Livewire\TeacherTaskAnswer;
use App\Livewire\AiChat;
// use App\Models\ClassTaskAnswer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::group(['middleware' => 'auth'], function () {
    // Route::get('/dashboard', Dashboard::class);
});

Route::post('/ckeditor/upload-image', [TeacherTask::class, 'uploadCkeditorImage'])
    ->name('ckeditor.upload-image');

Route::middleware(['auth'])->group(function () {
    // Dashboar 
    Route::get('/user-dashboard', UserDashboard::class)->name('user-dashboard');

    // User Class And Submit Tast or Exam
    Route::get('/user-class', UserClass::class)->name('user-class');
    Route::get('/user-class-detail/{id}', UserClassDetail::class)->name('user-class-detail');

    // User Searching and Join Class
    Route::get('/our-class', OurClass::class)->name('our-class');
    Route::get('/our-class-preview/{id}', OurClassPreview::class)->name('our-class-preview');

    // Teacher Create And Manage Class, Task and Exam
    Route::get('/teacher-class', TeacherClass::class)->name('teacher-class');
    Route::get('/teacher-task', TeacherTask::class)->name('teacher-task');
    Route::get('/teacher-exam', TeacherExam::class)->name('teacher-exam');
    Route::get('/teacher-task-answer/{id}', TeacherTaskAnswer::class)->name('teacher-task-answer');

    // AI Chat
    Route::get('/ai-chat', AiChat::class)->name('ai-chat');

    // User create and manage tasks answer
    Route::get('/user-task-answer/{id}', UserTaskAnswer::class)->name('user-task-answer');

    // User
    Route::get('/upgrade-role', UpgradeRole::class)->name('upgrade-role');
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__ . '/auth.php';
