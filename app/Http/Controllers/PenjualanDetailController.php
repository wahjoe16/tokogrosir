<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $produk = Product::orderBy('nama_produk')->get();
        $member = Member::orderBy('nama')->get();
        $diskon = Setting::first()->diskon ?? 0;

        // cek apakah ada transaksi yang berjalan
        if ($id_penjualan = session('id_penjualan')) {
            $penjualan = Penjualan::find($id_penjualan);
            $memberSelected = $penjualan->member ?? new Member();
            return view('penjualan_detail.index', compact('id_penjualan', 'produk', 'member', 'diskon', 'memberSelected', 'penjualan'));
        } else {
            if (auth()->user()->level == 0) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();
        // return $detail;

        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $key => $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">' . $item->produk['kode_produk'] . '</span>';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['stok'] = $item->produk['stok'];
            $row['harga_jual'] = 'Rp. ' . format_uang($item->harga_jual);
            $row['jumlah'] = '<input type="number" name="jumlah_" class="form-control input-sm quantity" data-id="' . $item->id_penjualan_detail  . '" value="' . $item->jumlah . '">';
            $row['diskon'] = $item->diskon . '%';
            $row['subtotal'] = format_uang($item->subtotal);
            $row['aksi'] = '<div class="btn-group">
                            <button onclick="deleteData(`' . route('transaksi_detail.destroy', $item->id_penjualan_detail) . '`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                          </div>';
            $data[] = $row;

            $total += $item->harga_jual * $item->jumlah;
            $total_item += $item->jumlah;
        }

        $data[] = [
            'kode_produk' => '
                <div class="total hide">' . $total . '</div> 
                <div class="total_item hide">' . $total_item . '</div>',
            'nama_produk' => '',
            'stok' => '',
            'harga_jual' => '',
            'jumlah' => '',
            'diskon' => '',
            'subtotal' => '',
            'aksi' => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $produk = Product::where('id_produk', $request->id_produk)->first();
        if (!$produk) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PenjualanDetail();
        $detail->id_penjualan = $request->id_penjualan;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_jual = $produk->harga_jual;
        $detail->jumlah = 1;
        $detail->diskon = 0;
        $detail->subtotal = $produk->harga_jual;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function loadform($diskon = 0, $total = 0, $diterima = 0)
    {
        $bayar = $total - ($diskon / 100 * $total);
        $kembali = ($diterima != 0) ? $diterima - $bayar : 0;
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar) . ' Rupiah'),
            'kembalirp' => format_uang($kembali),
            'Kembali_terbilang' => ucwords(terbilang($kembali) . ' Rupiah'),
        ];

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->harga_jual * $request->jumlah;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }
}
