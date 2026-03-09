<?php

use Inertia\Inertia;
use App\Http\Middleware\CheckAdmin;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\PstoController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\AssignatoreesController;
use App\Http\Controllers\ShowDateCSFFormController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SubUnitController;
use App\Http\Controllers\UnitPstoController;
use App\Http\Controllers\SurveyFormController;
use App\Http\Controllers\ServiceUnitController;
use App\Http\Controllers\SubUnitPstoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // 'canRegister' => Route::has('register'),
        // 'laravelVersion' => Application::VERSION,
        // 'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/services/csf/regions', [SurveyFormController::class, 'regions_index'])->name('regions_index');
Route::get('/services/csf/services', [SurveyFormController::class, 'services_index'])->name('services_index');
Route::get('/services/csf/service_units', [SurveyFormController::class, 'service_units_index'])->name('service_units_index');
Route::get('/services/csf/unit/sub-units', [SurveyFormController::class, 'getUnitSubUnits'])->name('getUnitSubUnits');
Route::get('/services/csf/sub-unit/pstos', [SurveyFormController::class, 'getSubUnitPSTO'])->name('getSubUnitPSTO');
Route::get('/services/csf/sub-unit/types', [SurveyFormController::class, 'getSubUnitTypes'])->name('getSubUnitTypes');
Route::get('/services/csf', [SurveyFormController::class, 'index'])->name('csf_form');
Route::get('/form/csf/msg', [SurveyFormController::class, 'msg_index'])->name('msg_index');
Route::get('captcha/{config?}', '\Mews\Captcha\CaptchaController@getCaptcha')->middleware('web');
// Route::post('/captcha/verify', [SurveyFormController::class, 'verifyCaptcha']);
Route::post('/csf_submission', [SurveyFormController::class, 'store']);


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::middleware([CheckAdmin::class])->group(function () {
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts');
        Route::post('/accounts/add', [AccountController::class, 'store']);
        Route::post('/accounts/update', [AccountController::class, 'update']);
        Route::post('/accounts/reset-password', [AccountController::class, 'resetPassword']);   
        Route::get('/libraries', function () {
            return Inertia::render('Libraries/Services/Index');
        })->name('libraries');
        Route::get('/regions', [RegionController::class, 'index'])->name('regions');
        Route::post('/regions/add', [RegionController::class, 'store']);
        Route::post('/regions/update', [RegionController::class, 'update']);
        Route::get('/pstos', [PstoController::class, 'index'])->name('pstos');
        Route::post('/pstos/add', [PstoController::class, 'store']);
        Route::post('/pstos/update', [PstoController::class, 'update']);
        Route::post('/pstos/delete', [PstoController::class, 'destroy']);
        Route::get('/unit-pstos', [UnitPstoController::class, 'index'])->name('unitPstos');
        Route::get('/sub-unit-pstos', [SubUnitPstoController::class, 'index'])->name('subunitPstos');
        Route::post('/unit-pstos/assign', [UnitPstoController::class, 'assignUnitPSTOs']);
        Route::post('/sub-unit-pstos/assign', [SubUnitPstoController::class, 'assignSubUnitPSTOs']);
        Route::get('/assignatorees', [AssignatoreesController::class, 'index'])->name('assignatorees');
        Route::post('/assignatorees/add', [AssignatoreesController::class, 'store']);
        Route::post('/assignatorees/update', [AssignatoreesController::class, 'update']);
        Route::post('/assignatorees/delete', [AssignatoreesController::class, 'destroy']);
        Route::get('/show-date-csf-form', [ShowDateCSFFormController::class, 'index'])->name('showdate');
        Route::post('/show-date-csf-form/update', [ShowDateCSFFormController::class, 'update']);
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', function () {
        return Inertia::render('Profile/Show');
    })->name('profile');
    Route::get('/service_units', [ServiceUnitController::class, 'index'])->name('service_units');
    Route::get('/service/units', [ServiceUnitController::class, 'getServiceUnits']);
    Route::get('/service/pstos', [ServiceUnitController::class, 'getUnitPstos']);
    Route::post('/services/add', [ServiceUnitController::class, 'store']);
    Route::post('/services/unit/add', [ServiceUnitController::class, 'storeUnit']);
    
    Route::get('/service_unit/unit', [ServiceUnitController::class , 'unit_index'])->name('units');
    Route::get('/service_unit/psto', [ServiceUnitController::class , 'psto_index'])->name('psto');
    Route::get('/service_unit/unit-psto', [ServiceUnitController::class , 'unit_psto_index'])->name('unit_psto');
    Route::get('/service_unit/sub-unit-psto', [ServiceUnitController::class , 'sub_unit_psto_index'])->name('sub_unit_psto');
    Route::get('/csi', [ReportController::class , 'index']);
    Route::get('/csi/view', [ReportController::class , 'view']);
    Route::get('/csi/all-units', [ReportController::class , 'all_units']);
   
    Route::get('/csi/generate/all-units/monthly', [ReportController::class, 'generateAllUnitReports']);
    Route::post('/csi/generate', [ReportController::class, 'generateReports']);



});



