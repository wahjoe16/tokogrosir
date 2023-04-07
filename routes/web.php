<?php

use App\Http\Controllers\KategoriController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', fn () => redirect()->route('login'));

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/kategori/data', [KategoriController::class, 'dataKategori'])->name('kategori.data');
    Route::resource('kategori', KategoriController::class);

    Route::get('/produk/data', [ProductController::class, 'dataProduk'])->name('produk.data');
    Route::post('/produk/delete-selected', [ProductController::class, 'deleteSelected'])->name('deleteSelected');
    Route::post('/produk/cetak-barcode', [ProductController::class, 'cetakBarcode'])->name('cetakBarcode');
    Route::resource('produk', ProductController::class);

    Route::get('/member/data', [MemberController::class, 'dataMember'])->name('member.data');
    Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');
    Route::resource('member', MemberController::class);

    Route::get('/supplier/data', [SupplierController::class, 'dataSupplier'])->name('supplier.data');
    Route::resource('supplier', SupplierController::class);
});
