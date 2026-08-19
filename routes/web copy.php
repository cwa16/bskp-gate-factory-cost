<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterCostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubJobBudgetCompareController;
use App\Http\Controllers\SubJobBudgetController;
use App\Http\Controllers\SubJobController;
use App\Http\Controllers\SubJobPlanActualSummaryController;
use App\Http\Controllers\SubJobPlanController;
use App\Http\Controllers\SubJobSummaryController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/subjob', [SubJobController::class, 'index'])->name('subjob.index');
Route::post('/subjob/save', [SubJobController::class, 'save'])->name('subjob.save');
Route::post('/subjob/revise', [SubJobController::class, 'updateRevisi'])->name('subjob.revise');

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
