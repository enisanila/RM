<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekam extends Model
{
    protected $table = "rekam";
    protected $fillable = ["tgl_rekam","pasien_id","keluhan","poli","dokter_id","pemeriksaan",
    "no_rekam","tindakan","petugas_id","pemeriksaan_file","tindakan_file", "file_rekam"];

    function getFilePemeriksaan(){
        return $this->pemeriksaan_file != null ? asset('images/pemeriksaan/'.$this->pemeriksaan_file) : null;
    }

    function getFileTindakan(){
        return $this->tindakan_file != null ? asset('images/pemeriksaan/'.$this->tindakan_file) : null;
    }
    public function getFileRekam(){
    return $this->file_rekam != null ? asset('images/pemeriksaan'.$this->file_rekam) : null;
    }

    function gigi(){
      return  RekamGigi::where('rekam_id',$this->id)->get();
    }

    function diagnosa(){
        return  RekamDiagnosa::where('rekam_id',$this->id)->get();
      }

    function pasien(){
        return $this->belongsTo(Pasien::class);
    }

    // function diagnosis(){
    //     return $this->belongsTo(Icd::class,'diagnosa','code');
    // }

    function dokter(){
        return $this->belongsTo(Dokter::class);
    }


    
}
