<p align="center">
    <img src="https://github.com/Rafie93/rekam-medis/blob/master/public/images/logo.png" >
    <img src="https://github.com/Rafie93/rekam-medis/blob/master/public/images/logo-text.png" >
</p>


## About SI-Shina

Adalah Aplikasi Sistem Rekam Medis Pasien untuk klinik medishina yang mempunyai 2 poli yaitu :
- Poli Umum.
- Poli Gigi.

## Hak Akses

Hak Akses meliputi
1. Admin
2. Pendaftaran
3. Dokter
4. Apotek

Sistem meliputi :
1. Dashboard
2. Pasien
3. Rekam Medis
4. Apotek
    - Permintaan Resep
    - Pengeluaran Obat
5. Pembayaran
6. Master data
    - Petugas
    - Dokter
    - Obat
    - Tindakan
    - ICD X (10469 Entry)

## Flow
Pendaftaran -> Dokter -> Apotek -> Pembayaran (Jika Umum) -> Done

## Poli Umum & Gigi
Input Pendaftaran & Anamnesa => Subject => Object => Assessment => Plan

<p align="center"><a href="#" target="_blank">
    <img src="https://github.com/Rafie93/rekam-medis/blob/master/public/ss/dashboard.png" width="600"></a></p>


## Poli Gigi
Poli Gigi Support Pengisian Odontogram dan riwayat odontoragm pasien.
Odontogram adalah suatu gambar peta mengenai keadaan gigi di dalam mulut yang merupakan bagian yang tak terpisahkan dari rekam medis Kedokteran Gigi 

<p align="center"><a href="#" target="_blank">
    <img src="https://github.com/Rafie93/rekam-medis/blob/master/public/ss/odontogram.png" width="600"></a></p>


## Installation
1. Clone Repo (https://github.com/Rafie93/rekam-medis.git)
2. Move Directory repo
3. composer Install
4. php artisan migrate
5. php artisan serve

ENJOYED


## License
deploy sistem by (https://github.com/Rafie93)
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

        return view('rekam.detail-rekam',compact('pasien','rekams','rekamLatest','poli'));
    }

    function store(Request $request){
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
        // $dokter = Dokter::where('poli',$request->poli)->first();
        // if($dokter){
        //     $request->merge([
        //         'dokter_id' => $dokter->id
        //     ]);
        // }
        $request->merge([
            'no_rekam' => "REG#".date('Ymd').$request->pasien_id,
            'petugas_id' => auth()->user()->id
        ]);
        Rekam::create($request->all());
        return redirect()->route('rekam.detail',$request->pasien_id)
                        ->with('sukses','Pendaftaran Berhasil,
                         Silakan lakukan pemeriksaan dan teruskan ke dokter terkait');

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

    public function rekam_status(Request $request, $id, $status)
    {
        $rekam->update([
            'status' => $status
        ]);

        $waktu = Carbon::parse($rekam->created_at)->format('d/m/Y H:i:s');
        if($status==2){
            $dokter = Dokter::find($rekam->dokter_id);
            $user = User::find($dokter->user_id);
            $message = "Pasien ".$rekam->pasien->nama.", silahkan diproses";
            Notification::send($user, new RekamUpdateNotification($rekam,$message));
            $link = Route('rekam.detail',$rekam->pasien_id);
            event(new StatusRekamUpdate($user->id,$rekam->no_rekam,$message,$link,$waktu));

        }else  if($status==3){
            $user = User::where('role',4)->get();
            $message = "Obat a\n Pasien ".$rekam->pasien->nama.", silahkan diproses";
            Notification::send($user, new RekamUpdateNotification($rekam,$message));
            foreach ($user as $key => $item) {
                $link = Route('rekam.detail',$rekam->pasien_id);
                // StatusRekamUpdate::dispatch($item->id,$rekam->no_rekam,$message,$link,$waktu);
                event(new StatusRekamUpdate($item->id,$rekam->no_rekam,$message,$link,$waktu));

            }
        }else  if($status==4){
            $user = User::where('role',2)->get();
            $message = "Pembayaran a\n Pasien ".$rekam->pasien->nama.", silahkan diproses";
            Notification::send($user, new RekamUpdateNotification($rekam,$message));
            foreach ($user as $key => $item) {
                $link = Route('rekam.detail',$rekam->pasien_id);
                // StatusRekamUpdate::dispatch($item->id,$rekam->no_rekam,$message,$link,$waktu);
                event(new StatusRekamUpdate($item->id,$rekam->no_rekam,$message,$link,$waktu));
            }
        }

        return redirect()->route('rekam.detail',$rekam->pasien_id)
                ->with('sukses','Status Rekam medis selesai diperbaharui ');
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
#   R M  
 