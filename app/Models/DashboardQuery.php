<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class DashboardQuery 
{
    public function totalObat()
    {
        return  Obat::count();
    }
    public function totalObatKeluar(){
        return PengeluaranObat::count();
    }
    public function totalObatKeluarSum(){
        return PengeluaranObat::sum('jumlah');
    }
    public function obatHariini(){
        return PengeluaranObat::whereDate('created_at',date('Y-m-d'))->count();
    }
   
    public function perikaHariini()
    {
        $user = auth()->user();
        $role = $user->role_display();
        return Rekam::whereDate('tgl_rekam',date('Y-m-d'))
                        ->when($role, function ($query) use ($role,$user){
                            if($role=="Dokter"){
                                $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                                $query->where('dokter_id', '=', $dokterId);
                            }
                        })
                        ->count();
    }
    public function pasienAntri(){
        $user = auth()->user();
        $role = $user->role_display();
        return Rekam::whereDate('tgl_rekam',date('Y-m-d'))
                        ->when($role, function ($query) use ($role,$user){
                            if($role=="Dokter"){
                                $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                                $query->where('dokter_id', '=', $dokterId);
                            }
                        })
                        
                        ->count();
    }
    public function perikaBulanini()
    {
        $user = auth()->user();
        $role = $user->role_display();
        return Rekam::whereMonth('tgl_rekam',date('m'))
                    ->whereYear('tgl_rekam',date('Y'))
                    ->when($role, function ($query) use ($role,$user){
                        if($role=="Dokter"){
                            $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                            $query->where('dokter_id', '=', $dokterId);
                        }
                    })
                    ->count();
    }
    public function perikaTahunini()
    {
        $user = auth()->user();
        $role = $user->role_display();
        return Rekam::whereYear('tgl_rekam',date('Y'))
                    ->when($role, function ($query) use ($role,$user){
                        if($role=="Dokter"){
                            $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                            $query->where('dokter_id', '=', $dokterId);
                        }
                    })
                    ->count();
    }
    public function totalPeriksa()
    {
        $user = auth()->user();
        $role = $user->role_display();
        return Rekam::when($role, function ($query) use ($role,$user){
            if($role=="Dokter"){
                $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                $query->where('dokter_id', '=', $dokterId);
            }
        })->count();
    }
    public function totalPasien()
    {
        
        return Pasien::whereNull('deleted_at')->count();
    }
    public function totalDoktor()
    {
        return Dokter::where('status',1)->count();
    }

    public function diagnosaBulanan()
{
    // otomatis ambil bulan dan tahun sekarang
    $filterBulan = date('Y-m');

    $data = DB::select('
        SELECT aa.*, ic.name_id 
        FROM (
            SELECT diagnosa_name, COUNT(diagnosa_name) AS total
            FROM (
                SELECT a.diagnosa AS diagnosa_name
                FROM rekam_diagnosa a
                LEFT JOIN rekam r ON r.id = a.rekam_id
                WHERE a.diagnosa IS NOT NULL
                AND r.tgl_rekam LIKE "%' . $filterBulan . '%"

                UNION ALL

                SELECT rg.diagnosa AS diagnosa_name
                FROM rekam_gigi rg
                WHERE rg.created_at LIKE "%' . $filterBulan . '%"
            ) sc
            GROUP BY diagnosa_name
        ) aa
        LEFT JOIN icds ic ON ic.code = aa.diagnosa_name
        ORDER BY total DESC 
        LIMIT 10
    ');

    return $data;
}


public function diagnosaYearly()
{
    $filter = date('Y-');

    $data = DB::select('
        SELECT aa.*, ic.name_id 
        FROM (
            SELECT diagnosa_name, COUNT(diagnosa_name) AS total
            FROM (
                SELECT a.diagnosa AS diagnosa_name
                FROM rekam_diagnosa a
                LEFT JOIN rekam r ON r.id = a.rekam_id
                WHERE a.diagnosa IS NOT NULL
                AND r.tgl_rekam LIKE "%' . $filter . '%"

                UNION ALL

                SELECT rg.diagnosa AS diagnosa_name
                FROM rekam_gigi rg
                WHERE rg.created_at LIKE "%' . $filter . '%"
            ) sc
            GROUP BY diagnosa_name
        ) aa
        LEFT JOIN icds ic ON ic.code = aa.diagnosa_name
        ORDER BY total DESC 
        LIMIT 10
    ');

    return $data;
}

    function rekam_day(){
        $user = auth()->user();
        $role = $user->role_display();

        return Rekam::latest()
                ->whereDate('tgl_rekam',date('Y-m-d'))
                ->when($role, function ($query) use ($role,$user){
                    if($role=="Dokter"){
                        $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                        $query->where('dokter_id', '=', $dokterId);
                    }
                })
                ->get();
    }

    function rekam_day2(){
        $user = auth()->user();
        $role = $user->role_display();

        return Rekam::orderBy('id','asc')
                ->whereDate('tgl_rekam',date('Y-m-d'))
                ->when($role, function ($query) use ($role,$user){
                    if($role=="Dokter"){
                        $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                        $query->where('dokter_id', '=', $dokterId);
                    }
                })
                ->get();
    }

    function rekam_antrian(){
        $user = auth()->user();
        $role = $user->role_display();

        return Rekam::orderBy('id','desc')
                ->whereDate('tgl_rekam',date('Y-m-d'))
         
                ->when($role, function ($query) use ($role,$user){
                    if($role=="Dokter"){
                        $dokterId = Dokter::where('user_id',$user->id)->where('status',1)->first()->id;
                        $query->where('dokter_id', '=', $dokterId);
                    }
                })
                ->get();
    }
}
