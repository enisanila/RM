<?php

namespace App\Http\Controllers;

use App\Events\StatusRekamUpdate;
use App\Models\Dokter;
use App\Models\KondisiGigi;
use App\Models\Pasien;
use App\Models\PengeluaranObat;
use App\Models\Poli;
use App\Models\Rekam;
use App\Models\RekamGigi;
use App\Models\Tindakan;
use App\Notifications\RekamUpdateNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Notification;

class RekamController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role_display();
        $rekams = Rekam::latest()
                    ->select('rekam.*')
                    ->leftJoin('pasien', function($join) {
                        $join->on('rekam.pasien_id', '=', 'pasien.id');
                    })
                    ->when($request->keyword, function ($query) use ($request) {
                        $query->where('rekam.tgl_rekam', 'LIKE', "%{$request->keyword}%")
                                ->orwhere('rekam.cara_bayar', 'LIKE', "%{$request->keyword}%")
                                ->orwhere('pasien.nama', 'LIKE', "%{$request->keyword}%")
                                ->orwhere('pasien.no_bpjs', 'LIKE', "%{$request->keyword}%")
                                ->orwhere('pasien.no_rm', 'LIKE', "%{$request->keyword}%");
                    })
                    ->when($role, function ($query) use ($role,$user){
                        if($role=="Dokter"){
                            $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                            $query->where('dokter_id', '=', $dokterId);
                        }
                    })
                    //->when($request->tab, function ($query) use ($request) {
                       // if(auth()->user()->role_display()=="Dokter" && $request->tab==5){
                           // $query->whereIn('status', [3,4,5]);
//}else{
                           // if($request->tab==5){
                               // $query->whereIn('status',[4,5]);
                            //}else{
                               // $query->where('status', '=', "$request->tab");
                          //  }
                       // }
                  //  })
                    ->paginate(10);
        return view('rekam.index',compact('rekams'));
    }

    public function add(Request $request)
    {
        $poli = Poli::all();
        return view('rekam.add',compact('poli'));
    }

    public function edit(Request $request,$id)
    {
        $poli = Poli::all();
        $data = Rekam::find($id);
        return view('rekam.edit',compact('data','poli'));
    }

   
    public function detail(Request $request,$pasien_id)
    {
        $pasien = Pasien::find($pasien_id);
        
        $rekamLatest = Rekam::latest()
                                ->where('pasien_id',$pasien_id)
                                ->first();

        $rekams = Rekam::latest()
                    ->where('pasien_id',$pasien_id)
                    ->when($request->keyword, function ($query) use ($request) {
                        $query->where('tgl_rekam', 'LIKE', "%{$request->keyword}%");
                    })
                    ->when($request->poli, function ($query) use ($request) {
                        $query->where('poli', 'LIKE', "%{$request->poli}%");
                    })
                    ->paginate(5);
                    
        if($rekamLatest){
           auth()->user()->notifications->where('data.no_rekam',$rekamLatest->no_rekam)->markAsRead();
        //   dd($data);
        }
        $poli = Poli::where('status',1)->get();
        $rekam = $rekamLatest;

        // kirim juga variabel $rekam ke view
        return view('rekam.detail-rekam', compact('pasien','rekams','rekamLatest','poli','rekam'));

        return view('rekam.detail-rekam',compact('pasien','rekams','rekamLatest','poli'));
    }

   public function store(Request $request)
{
    // 1. Validasi input
    $this->validate($request, [
        'tgl_rekam'   => 'required',
        'pasien_id'   => 'required',
        'pasien_nama' => 'required',
        'keluhan'     => 'required',
        'poli'        => 'required',
        'file'        => 'required|mimes:doc,docx,pdf,jpg,png|max:2048',
        'cara_bayar'  => 'required',
        'dokter_id'   => 'required',
    ]);

    // 2. Pastikan pasien ada
    $pasien = Pasien::find($request->pasien_id);
    if (!$pasien) {
        return redirect()->back()->withInput()
            ->withErrors(['pasien_id' => 'Data Pasien Tidak Ditemukan']);
    }

    // 3. Simpan file dulu
    $fileName = null;
    if ($request->hasFile('file')) {
        $fileName = time().'_'.$request->file('file')->getClientOriginalName();
        $request->file('file')->move(storage_path('app/rekam_medis'), $fileName);
    }

    // 4. Baru simpan ke database
    $rekam = new Rekam();
    $rekam->no_rekam   = "REG#" . date('YmdHis') . $request->pasien_id;
    $rekam->tgl_rekam  = $request->tgl_rekam;
    $rekam->pasien_id  = $request->pasien_id;
    $rekam->keluhan    = $request->keluhan;
    $rekam->poli       = $request->poli;
    $rekam->cara_bayar = $request->cara_bayar;
    $rekam->dokter_id  = $request->dokter_id;
    $rekam->petugas_id = auth()->user()->id;
    $rekam->file       = $fileName; 
    $rekam->save();

    // 5. Redirect
    return redirect()->route('rekam.detail', $request->pasien_id)
        ->with('sukses', 'Rekam berhasil disimpan');
}


public function download($id)
{
    $rekam = Rekam::findOrFail($id);

    $filePath = storage_path('app/rekam_medis/' . $rekam->file);

    if (file_exists($filePath)) {
        return response()->download($filePath, $rekam->file);
    } else {
        return back()->with('error', 'File tidak ditemukan.');
    }
}




    function update(Request $request,$id){
        $this->validate($request,[
            'tgl_rekam' => 'required',
            'pasien_id' => 'required',
            'pasien_nama' => 'required',
            'keluhan' => 'required',
            'poli' => 'required',
            'cara_bayar' => 'required',
            'dokter_id' => 'required'
        ]);
        $pasien = Pasien::where('id',$request->pasien_id)->first();
        if(!$pasien){
            return redirect()->back()->withInput($request->input())
                                ->withErrors(['pasien_id' => 'Data Pasien Tidak Ditemukan']);
        }
        
        $rekam = Rekam::find($id);
        $rekam->update($request->all());
        return redirect()->route('rekam.detail',$request->pasien_id)
                        ->with('sukses','Berhasil diperbaharui,
                         Silakan lakukan pemeriksaan dan teruskan ke dokter terkait');

    }

   

    public function delete(Request $request,$id)
    {
        Rekam::find($id)->delete();
        PengeluaranObat::where('rekam_id',$id)->update([
            'deleted_at'=> Carbon::now()
        ]);
        return redirect()->route('rekam')->with('sukses','Data berhasil dihapus');
    } 

   
}
