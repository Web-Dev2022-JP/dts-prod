<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\Modal;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ModalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestedDocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthenticatedSessionController::class, 'create']);

Route::middleware(['auth', 'verified', 'roleguard'])->group(function(){
    Route::get('/administrator', [AdministratorController::class,'dashboard'])->name('administrator.dashboard');
    Route::get('/request-documents-admin', [RequestedDocumentController::class,'showIncomingRequestAdmin'])->name('administrator.dashboard.incoming.request');
    Route::get('/request-my-documents', [RequestedDocumentController::class,'myRequestAdmin'])->name('administrator.dashboard.my.request');
    Route::get('/monitor', [RequestedDocumentController::class,'monitor'])->name('administrator.dashboard.monitor');
    Route::post('/request-documents-update', [RequestedDocumentController::class,'updateIncomingRequest'])->name('administrator.dashboard.incoming.request.update');
    Route::get('/offices', [OfficeController::class,'showOffices'])->name('administrator.dashboard.offices');
    Route::post('/offices-add', [OfficeController::class,'addOffices'])->name('administrator.dashboard.offices.add');
    Route::get('/offices-user/{office_id}', [OfficeController::class,'showOfficesUser'])->name('administrator.dashboard.offices.user');
    Route::post('/user-add', [OfficeController::class,'addUsers'])->name('administrator.dashboard.user.add');
    Route::post('/updates', [RequestedDocumentController::class,'update'])->name('request.documents.update');
    Route::post('/get-logs',[RequestedDocumentController::class, 'getLogs']);
    Route::post('/request-documents-forward', [RequestedDocumentController::class,'forwardIncomingRequest'])->name('administrator.dashboard.incoming.request.forward');
    Route::get('/departments-with-users/{id}', [RequestedDocumentController::class,'departmentAndUsers']);
    Route::get('/create-reports', [AdministratorController::class,'reportsPdf'])->name('reportsPdf');
    Route::post('/generate-reports', [RequestedDocumentController::class,'generateReports'])->name('generate.reports');
    Route::post('/cancel-reports', [RequestedDocumentController::class,'cancelReports'])->name('cancel.reports');
    Route::post('/archived-office', [OfficeController::class, 'destroy'])->name('delete.office');
});

// Route::get('/department', [DepartmentController::class,'dashboard'])->middleware(['auth', 'verified'])->name('departments.dashboard');

Route::middleware(['auth', 'verified','roleguard','check.archived'])->group(function(){
    Route::get('/my-documents', [RequestedDocumentController::class,'myRequest'])->name('departments.dashboard.my.request');
    Route::get('/department', [DepartmentController::class,'dashboard'])->name('departments.dashboard');
    Route::get('/department-show', [OfficeController::class,'showDepartment'])->name('departments.dashboard.department');
    Route::get('/request-documents', [RequestedDocumentController::class,'showIncomingRequest'])->name('departments.dashboard.incoming');
    Route::post('/documents', [RequestedDocumentController::class,'create'])->name('request.documents');
    Route::get('/progress', [DepartmentController::class,'documentProgress'])->name('document.progress');
    Route::get('/get-barcode', [RequestedDocumentController::class,'barcodePrinting'])->name('barcode.print');
    Route::post('/recieved-document', [RequestedDocumentController::class,'recievedDocument'])->name('recieved.document');
    Route::get('/dept', [DepartmentController::class,'getDept'])->name('get.Dept');
});

Route::middleware('auth')->group(function () {
    // notification
    Route::get('/notification',[NotificationController::class,'getNotification'])->name('notify');
    Route::post('/notification-update',[NotificationController::class,'updateNotification'])->name('notify.update');
    // on
    Route::get('/on',[ReportController::class,'getOnGoingDocuments'])->name('on');

    // logs
    Route::get('/logs',[AdministratorController::class,'history'])->name('history');
    Route::get('/history',[DepartmentController::class,'history'])->name('history.department');
    //check if already processed
    Route::post('/already-processed',[LogController::class, 'alreadyProcessed'])->name('already.process');
    // events
    Route::post('/events',[EventController::class,'pushEvents'])->name('events.push');
    Route::post('/delete-event',[EventController::class,'deleteEvents'])->name('events.delete');
    Route::get('/fetch-events',[EventController::class,'FetchEvents'])->name('events.fetch');
    Route::post('/update-events',[EventController::class,'updateEvents'])->name('events.update');
    Route::post('/edit-events',[EventController::class,'EditEvents'])->name('events.edit');


    Route::post('/account-manage', [AdministratorController::class, 'accounts'])->name('account.manage');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profiledept', [ProfileController::class, 'editDept'])->name('profile.edit.dept');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
