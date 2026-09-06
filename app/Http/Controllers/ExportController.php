<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /**
     * id_user siswa yang terdaftar aktif di course ini. Materi dipakai lintas
     * kelas, jadi tanpa ini ekspor nilai ikut memuat siswa kelas lain.
     */
    private function studentIdsForCourse($courseId)
    {
        return DB::table('siswa_kelas')
            ->join('kelas_mp', 'kelas_mp.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_mp.id_kelas_mp', $courseId)
            ->where('siswa_kelas.aktif', 'Y')
            ->pluck('siswa_kelas.id_user')
            ->all();
    }

    public function exportNilaiMateri($courseId, $materialId, $component)
    {
        // Get material info
        $material = DB::table('materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->where('materi.id_materi', $materialId)
            ->select('materi.nama_materi', 'modul.nama_modul')
            ->first();

        $componentName = $this->getComponentName($component);
        $fileName = 'Nilai_' . str_replace(' ', '_', $componentName) . '_' . date('YmdHis') . '.xls';

        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo $this->generateExcelContent($material, $materialId, $component, $componentName, $this->studentIdsForCourse($courseId));
        exit;
    }

    private function generateExcelContent($material, $materialId, $component, $componentName, array $studentIds = null)
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; }';
        $html .= 'th, td { border: 1px solid black; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }';
        $html .= '.title { font-size: 16px; font-weight: bold; text-align: center; margin-bottom: 10px; }';
        $html .= '.info { margin-bottom: 5px; }';
        $html .= '.center { text-align: center; }';
        $html .= '.number { text-align: center; }';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';

        // Title and Info
        $html .= '<div class="title">LAPORAN NILAI SISWA</div>';
        $html .= '<div class="info">Materi: ' . $material->nama_materi . '</div>';
        $html .= '<div class="info">Modul: ' . $material->nama_modul . '</div>';
        $html .= '<div class="info">Komponen: ' . $componentName . '</div>';
        $html .= '<div class="info">Tanggal Export: ' . date('d-m-Y H:i:s') . '</div>';
        $html .= '<br>';

        // Table
        $html .= '<table>';

        if ($component === 'metakognisi') {
            $html .= $this->generateMetakognisiTable($materialId, $studentIds);
        } else {
            $isPRE = in_array($component, ['ruang_kolaborasi', 'refleksi_terbimbing', 'demonstrasi_konseptual', 'elaborasi_pemahaman']);

            if ($isPRE) {
                $html .= $this->generatePRETable($materialId, $component, $studentIds);
            } else {
                $html .= $this->generateRegularTable($materialId, $component, $studentIds);
            }
        }

        $html .= '</table>';
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    private function generateMetakognisiTable($materialId, array $studentIds = null)
    {
        $html = '<thead>';
        $html .= '<tr>';
        $html .= '<th class="number">No</th>';
        $html .= '<th>Nama Siswa</th>';
        $html .= '<th class="center">NIS</th>';
        $html .= '<th class="center">Nilai Deklaratif</th>';
        $html .= '<th class="center">Nilai Prosedural</th>';
        $html .= '<th class="center">Nilai Kondisional</th>';
        $html .= '<th class="center">Rata-rata</th>';
        $html .= '<th class="center">Tanggal Upload</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Get data
        $answers = DB::table('jawaban_pengetahuan_metakognisi as jpm')
            ->join('eksplorasi_konsep as ek', 'jpm.id_eksplorasi_konsep', '=', 'ek.id_eksplorasi_konsep')
            ->join('users as u', 'jpm.id_user', '=', 'u.id_user')
            ->join('profile as p', 'u.id_user', '=', 'p.id_user')
            ->where('ek.id_materi', $materialId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('jpm.id_user', $studentIds);
            })
            ->select(
                'p.nama as nama_siswa',
                'p.nip_nis',
                'jpm.nilai_deklaratif',
                'jpm.nilai_prosedural',
                'jpm.nilai_kondisional',
                'jpm.created_at'
            )
            ->orderBy('p.nama')
            ->get();

        $no = 1;
        foreach ($answers as $answer) {
            $rataRata = 0;
            $count = 0;
            
            if ($answer->nilai_deklaratif !== null) {
                $rataRata += $answer->nilai_deklaratif;
                $count++;
            }
            if ($answer->nilai_prosedural !== null) {
                $rataRata += $answer->nilai_prosedural;
                $count++;
            }
            if ($answer->nilai_kondisional !== null) {
                $rataRata += $answer->nilai_kondisional;
                $count++;
            }
            
            $rataRata = $count > 0 ? round($rataRata / $count, 2) : 0;

            $html .= '<tr>';
            $html .= '<td class="number">' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($answer->nama_siswa) . '</td>';
            $html .= '<td class="center">' . ($answer->nip_nis ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_deklaratif ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_prosedural ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_kondisional ?? '-') . '</td>';
            $html .= '<td class="center">' . $rataRata . '</td>';
            $html .= '<td class="center">' . date('d-m-Y H:i', strtotime($answer->created_at)) . '</td>';
            $html .= '</tr>';
        }

        if ($answers->isEmpty()) {
            $html .= '<tr><td colspan="8" class="center">Belum ada data</td></tr>';
        }

        $html .= '</tbody>';
        return $html;
    }

    private function generatePRETable($materialId, $component, array $studentIds = null)
    {
        $tableName = $this->getTableName($component);

        $html = '<thead>';
        $html .= '<tr>';
        $html .= '<th class="number">No</th>';
        $html .= '<th>Nama Siswa</th>';
        $html .= '<th class="center">NIS</th>';
        $html .= '<th class="center">Nilai Perencanaan</th>';
        $html .= '<th class="center">Nilai Refleksi</th>';
        $html .= '<th class="center">Nilai Evaluasi</th>';
        $html .= '<th class="center">Rata-rata PRE</th>';
        $html .= '<th class="center">Nilai Jawaban Utama</th>';
        $html .= '<th class="center">Tanggal Upload</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Get data
        $answers = DB::table($tableName . ' as j')
            ->join('users as u', 'j.id_user', '=', 'u.id_user')
            ->join('profile as p', 'u.id_user', '=', 'p.id_user')
            ->leftJoin('jawaban_perencanaan_refleksi_evaluasi as pre', function($join) use ($component) {
                $join->on('j.id_user', '=', 'pre.id_user')
                     ->on('j.id_materi', '=', 'pre.id_materi')
                     ->where('pre.jenis', '=', $component);
            })
            ->where('j.id_materi', $materialId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('j.id_user', $studentIds);
            })
            ->select(
                'p.nama as nama_siswa',
                'p.nip_nis',
                'j.nilai',
                'pre.nilai_perencanaan',
                'pre.nilai_refleksi',
                'pre.nilai_evaluasi',
                'j.created_at'
            )
            ->orderBy('p.nama')
            ->get();

        $no = 1;
        foreach ($answers as $answer) {
            $rataRataPRE = 0;
            $count = 0;
            
            if ($answer->nilai_perencanaan !== null) {
                $rataRataPRE += $answer->nilai_perencanaan;
                $count++;
            }
            if ($answer->nilai_refleksi !== null) {
                $rataRataPRE += $answer->nilai_refleksi;
                $count++;
            }
            if ($answer->nilai_evaluasi !== null) {
                $rataRataPRE += $answer->nilai_evaluasi;
                $count++;
            }
            
            $rataRataPRE = $count > 0 ? round($rataRataPRE / $count, 2) : 0;

            $html .= '<tr>';
            $html .= '<td class="number">' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($answer->nama_siswa) . '</td>';
            $html .= '<td class="center">' . ($answer->nip_nis ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_perencanaan ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_refleksi ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai_evaluasi ?? '-') . '</td>';
            $html .= '<td class="center">' . $rataRataPRE . '</td>';
            $html .= '<td class="center">' . ($answer->nilai ?? '-') . '</td>';
            $html .= '<td class="center">' . date('d-m-Y H:i', strtotime($answer->created_at)) . '</td>';
            $html .= '</tr>';
        }

        if ($answers->isEmpty()) {
            $html .= '<tr><td colspan="9" class="center">Belum ada data</td></tr>';
        }

        $html .= '</tbody>';
        return $html;
    }

    private function generateRegularTable($materialId, $component, array $studentIds = null)
    {
        $tableName = $this->getTableName($component);

        $html = '<thead>';
        $html .= '<tr>';
        $html .= '<th class="number">No</th>';
        $html .= '<th>Nama Siswa</th>';
        $html .= '<th class="center">NIS</th>';
        $html .= '<th class="center">Nilai</th>';
        $html .= '<th class="center">Status</th>';
        $html .= '<th class="center">Tanggal Upload</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Get data
        $answers = DB::table($tableName . ' as j')
            ->join('users as u', 'j.id_user', '=', 'u.id_user')
            ->join('profile as p', 'u.id_user', '=', 'p.id_user')
            ->where('j.id_materi', $materialId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('j.id_user', $studentIds);
            })
            ->select(
                'p.nama as nama_siswa',
                'p.nip_nis',
                'j.nilai',
                'j.created_at'
            )
            ->orderBy('p.nama')
            ->get();

        $no = 1;
        foreach ($answers as $answer) {
            $status = $answer->nilai !== null ? 'Sudah Dinilai' : 'Belum Dinilai';

            $html .= '<tr>';
            $html .= '<td class="number">' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($answer->nama_siswa) . '</td>';
            $html .= '<td class="center">' . ($answer->nip_nis ?? '-') . '</td>';
            $html .= '<td class="center">' . ($answer->nilai ?? '-') . '</td>';
            $html .= '<td class="center">' . $status . '</td>';
            $html .= '<td class="center">' . date('d-m-Y H:i', strtotime($answer->created_at)) . '</td>';
            $html .= '</tr>';
        }

        if ($answers->isEmpty()) {
            $html .= '<tr><td colspan="6" class="center">Belum ada data</td></tr>';
        }

        $html .= '</tbody>';
        return $html;
    }

    private function getTableName($component)
    {
        $tables = [
            'mulai_dari_diri' => 'jawaban_mulai_dari_diri',
            'ruang_kolaborasi' => 'jawaban_ruang_kolaborasi',
            'refleksi_terbimbing' => 'jawaban_refleksi_terbimbing',
            'demonstrasi_konseptual' => 'jawaban_demonstrasi_konseptual',
            'elaborasi_pemahaman' => 'jawaban_elaborasi_pemahaman'
        ];
        
        return $tables[$component] ?? '';
    }

    private function getComponentName($component)
    {
        $names = [
            'mulai_dari_diri' => 'Mulai Dari Diri',
            'ruang_kolaborasi' => 'Ruang Kolaborasi',
            'refleksi_terbimbing' => 'Refleksi Terbimbing',
            'demonstrasi_konseptual' => 'Demonstrasi Konseptual',
            'elaborasi_pemahaman' => 'Elaborasi Pemahaman',
            'metakognisi' => 'Pengetahuan Metakognisi'
        ];
        
        return $names[$component] ?? $component;
    }
}