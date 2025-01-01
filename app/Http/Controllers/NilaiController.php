<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function getNilaiRT()
    {
        try {
            $result = DB::select("
                SELECT 
                    nama, 
                    nisn, 
                    nama_pelajaran, 
                    skor 
                FROM nilai 
                WHERE materi_uji_id = 7 
                AND nama_pelajaran != 'Pelajaran Khusus'
                ORDER BY nama_pelajaran ASC
            ");

            $collection = collect($result);

            $transformedData = $collection->groupBy('nisn')->map(function ($items) {
                return [
                    'nama' => $items->first()->nama,
                    'nisn' => $items->first()->nisn,
                    'nilaiRt' => $items->mapWithKeys(function ($item) {
                        return [strtolower($item->nama_pelajaran) => $item->skor];
                    })->toArray(),
                ];
            })->values();


            return response()->json($transformedData, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getNilaiST()
    {
        try {
            // Menggunakan raw query untuk menghitung nilai ST sesuai aturan
            $result = DB::select("
                SELECT 
                    nama, 
                    nisn, 
                    nama_pelajaran, 
                    CASE 
                        WHEN pelajaran_id = 44 THEN skor * 41.67
                        WHEN pelajaran_id = 45 THEN skor * 29.67
                        WHEN pelajaran_id = 46 THEN skor * 100
                        WHEN pelajaran_id = 47 THEN skor * 23.81
                        ELSE skor
                    END AS skor
                FROM nilai
                WHERE materi_uji_id = 4
                ORDER BY nama_pelajaran ASC
            ");

            $collection = collect($result);

            $transformedData = $collection->groupBy('nisn')->map(function ($items) {
                return [
                    'nama' => $items->first()->nama,
                    'nisn' => $items->first()->nisn,
                    'listNilai' => $items->mapWithKeys(function ($item) {
                        return [strtolower($item->nama_pelajaran) => $item->skor];
                    })->toArray(),
                    'total' => $items->pluck('skor')->sum()
                ];
            })->sortByDesc('total')->values();


            return response()->json($transformedData, 200);
            // return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
