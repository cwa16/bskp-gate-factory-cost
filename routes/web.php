<?php

use App\Http\Controllers\BoronganController;
use App\Http\Controllers\BudgetCostPerKgController;
use App\Http\Controllers\CostPerKgController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterCostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RssProductionBudgetController;
use App\Http\Controllers\SubJobBudgetCompareController;
use App\Http\Controllers\SubJobBudgetController;
use App\Http\Controllers\SubJobController;
use App\Http\Controllers\SubJobPlanActualSummaryController;
use App\Http\Controllers\SubJobPlanController;
use App\Http\Controllers\SubJobSummaryController;
use Illuminate\Support\Facades\Route;


Route::middleware(['web'])->get('/bskp-gate-factory-cost/public', function (Request $request) {
    $token = $request->query('token');
    $appId = $request->query('app_id');

    if (! $token || ! $appId) {
        abort(400, 'Token dan App ID harus disertakan.');
    }

    $response = Http::withToken($token)->get("http://192.168.99.202/bskp-gate/public/api/profile?app_id={$appId}");

    if (! $response->ok()) {
        abort(401);
    }

    $data = $response->json();

    $user = User::firstWhere('email', $data['email']);

    if ($user) {
        Auth::guard('web')->login($user);

        return redirect('/');
    } else {
        abort(403, 'User tidak ditemukan di aplikasi ini');
    }
});

// Route::middleware('auth')->group(function () {
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/subjob', [SubJobController::class, 'index'])->name('subjob.index');
Route::post('/subjob/save', [SubJobController::class, 'save'])->name('subjob.save');
Route::post('/subjob/revise', [SubJobController::class, 'revise'])->name('subjob.revise');
Route::get('/wages-summary', [SubJobController::class, 'wagesSummary'])->name('subjob.summary');
Route::get('/sub-job/wages-export', [SubJobController::class, 'exportSummary'])->name('subjob.wages.export');

Route::get('/subjob/plan', [SubJobPlanController::class, 'index'])->name('subjob.plan.index');
Route::post('/subjob/plan/save', [SubJobPlanController::class, 'store'])->name('subjob.plan.store');
Route::post('/subjob/sync-absen', [SubJobController::class, 'syncAbsen'])->name('subjob.sync-absen');
Route::post('/subjob/update-time', [SubJobController::class, 'updateTime'])->name('subjob.update-time');
Route::get('/subjob/sync-progress', [SubJobController::class, 'syncProgress'])->name('subjob.sync-progress');
Route::post('/budget/copy', [SubJobPlanController::class, 'copyBudget'])->name('budget.copy');

Route::post('/budget-input/save-batch', [SubJobPlanController::class, 'saveBatch'])->name('budget-input.save-batch');

Route::get('/subjob/summary', [SubJobSummaryController::class, 'index'])->name('subjob.summary');

Route::get('/subjob/plan-actual-summary', [SubJobPlanActualSummaryController::class, 'index'])->name('subjob.plan-actual-summary');

Route::get('/subjob-budget', [SubJobBudgetController::class, 'index'])->name('subjob-budget.index');
Route::post('/subjob-budget/store', [SubJobBudgetController::class, 'store'])->name('subjob-budget.store');
Route::post('/subjob-budget/copy-prev', [SubJobBudgetController::class, 'copyFromPrevious'])->name('subjob-budget.copy-prev');

Route::get('/subjob-budget-vs-actual', [SubJobBudgetCompareController::class, 'index'])->name('subjob-budget.compare');
Route::get('/subjob-budget-vs-actual/export', [SubJobBudgetCompareController::class, 'export'])->name('subjob-budget.compare.export');

Route::resource('master-costs', MasterCostController::class);

// Route Input Borongan
Route::get('/borongan', [BoronganController::class, 'index'])->name('borongan.index');
Route::post('/borongan/store', [BoronganController::class, 'store'])->name('borongan.store');

Route::get('/rss-budget', [RssProductionBudgetController::class, 'index'])->name('rss-budget.index');
Route::post('/rss-budget', [RssProductionBudgetController::class, 'store'])->name('rss-budget.store');

Route::get('/cost-per-kg', [CostPerKgController::class, 'index'])->name('cost-per-kg.index');

Route::get('/budget-cpk', [BudgetCostPerKgController::class, 'index'])->name('budget-cpk.index');
Route::post('/budget-cpk', [BudgetCostPerKgController::class, 'store'])->name('budget-cpk.store');
Route::delete('/budget-cpk/{id}', [BudgetCostPerKgController::class, 'destroy'])->name('budget-cpk.destroy');

// Route untuk Potongan Gaji (Deductions)
Route::get('/deduction-wages', [App\Http\Controllers\DeductionWageController::class, 'index'])->name('deduction.index');
Route::post('/deduction-wages/update', [App\Http\Controllers\DeductionWageController::class, 'update'])->name('deduction.update');

Route::get('/cost-per-kg/export', [App\Http\Controllers\CostPerKgController::class, 'export'])->name('cost-per-kg.export');

// });

require __DIR__ . '/auth.php';
