<?php

namespace App\Helpers;

class TerbilangHelper
{
    /**
     * Konversi angka nominal menjadi teks terbilang bahasa Indonesia.
     * Contoh: 3000000 => "Tiga Juta Rupiah"
     */
    public static function terbilang($nilai): string
    {
        $nilai = abs((int) $nilai);
        if ($nilai == 0) {
            return 'Nol Rupiah';
        }

        $huruf = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
        ];
        
        $temp = '';
        if ($nilai < 12) {
            $temp = ' ' . $huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilangRaw($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilangRaw((int) ($nilai / 10)) . ' Puluh' . self::terbilangRaw($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus' . self::terbilangRaw($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilangRaw((int) ($nilai / 100)) . ' Ratus' . self::terbilangRaw($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu' . self::terbilangRaw($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000)) . ' Ribu' . self::terbilangRaw($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000)) . ' Juta' . self::terbilangRaw($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000000)) . ' Milyar' . self::terbilangRaw(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000000000)) . ' Trilyun' . self::terbilangRaw(fmod($nilai, 1000000000000));
        }
        
        return trim($temp) . ' Rupiah';
    }

    private static function terbilangRaw($nilai): string
    {
        $nilai = abs((int) $nilai);
        if ($nilai == 0) return '';
        $huruf = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'
        ];
        
        $temp = '';
        if ($nilai < 12) {
            $temp = ' ' . $huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilangRaw($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilangRaw((int) ($nilai / 10)) . ' Puluh' . self::terbilangRaw($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus' . self::terbilangRaw($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilangRaw((int) ($nilai / 100)) . ' Ratus' . self::terbilangRaw($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu' . self::terbilangRaw($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000)) . ' Ribu' . self::terbilangRaw($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000)) . ' Juta' . self::terbilangRaw($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000000)) . ' Milyar' . self::terbilangRaw(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilangRaw((int) ($nilai / 1000000000000)) . ' Trilyun' . self::terbilangRaw(fmod($nilai, 1000000000000));
        }
        
        return $temp;
    }
}
