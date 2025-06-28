<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
// use App\Livewire\Dashboard;
use App\Livewire\UserDashboard;
use App\Livewire\UserClass;
use App\Livewire\OurClass;
use App\Livewire\TeacherClass;
use App\Livewire\TeacherTask;
use App\Livewire\TeacherExam;
use App\Livewire\UpgradeRole;
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


Route::middleware(['auth'])->group(function () {

    Route::get('/user-dashboard', UserDashboard::class)->name('user-dashboard');
    Route::get('/user-class', UserClass::class)->name('user-class');
    Route::get('/our-class', OurClass::class)->name('our-class');
    Route::get('/teacher-class', TeacherClass::class)->name('teacher-class');
    Route::get('/teacher-task', TeacherTask::class)->name('teacher-task');
    Route::get('/teacher-exam', TeacherExam::class)->name('teacher-exam');
    Route::get('/upgrade-role', UpgradeRole::class)->name('upgrade-role');
    
    
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__ . '/auth.php';
