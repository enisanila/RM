<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = "pasien";
    protected $fillable = ["no_rm","nama","tmp_lahir","tgl_lahir","jk","alamat_lengkap"
    ,"kelurahan","kecamatan","kabupaten","kodepos","agama","status_menikah","pendidikan"
    ,"pekerjaan","kewarganegaraan","no_hp","cara_bayar","no_bpjs","deleted_at","alergi"
    ,"general_uncent"];

    function getGeneralUncent(){
      return $this->general_uncent != null ? asset('images/pasien/'.$this->general_uncent) : null;
    }

    function rekamGigi(){
       return RekamGigi::where('pasien_id',$this->id)->get();
    }


    function isRekamGigi(){
        return RekamGigi::where('pasien_id',$this->id)->get()->count() > 0 ? true : false;
     }

     

}
