<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Filament\Resources\LaporanImutResource\Pages\{
    UnitKerjaReport,
    UnitKerjaImutDataReport,
    ImutDataReport,
    ImutDataUnitKerjaReport
};

class LaporanRedirectService
{
    public static function getRedirectUrlForImutData(int $laporanId): string
    {
        if (Gate::allows('view_imut_data_report_laporan::imut')) {
            return ImutDataReport::getUrl(['laporan_id' => $laporanId]);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses laporan IMUT data.');
    }

    public static function getRedirectUrlForUnitKerja(int $laporanId): string
    {
        // Jika punya akses utama, langsung redirect ke halaman utama
        if (Gate::allows('view_unit_kerja_report_laporan::imut')) {
            return UnitKerjaReport::getUrl(['laporan_id' => $laporanId]);
        }

        // Jika punya akses detail saja, maka redirect ke detail
        if (Gate::allows('view_unit_kerja_report_detail_laporan::imut')) {
            // Kita coba ambil unit kerja dari user
            $unitKerjaId = self::getUserFirstUnitKerjaId();

            if (!$unitKerjaId) {
                abort(403, 'User tidak memiliki unit kerja yang bisa digunakan untuk melihat detail.');
            }

            return UnitKerjaImutDataReport::getUrl([
                'laporan_id' => $laporanId,
                'unit_kerja_id' => $unitKerjaId,
            ]);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses laporan unit kerja.');
    }

    protected static function getUserFirstUnitKerjaId(): int
    {
        $unitKerjaId = Auth::user()?->unitKerjas()?->first()?->id;

        if (!$unitKerjaId) {
            abort(403, 'Unit kerja tidak ditemukan untuk user.');
        }

        return $unitKerjaId;
    }
}
