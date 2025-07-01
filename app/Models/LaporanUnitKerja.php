<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Model untuk mengelola laporan unit kerja.
 */
class LaporanUnitKerja extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relasi ke model LaporanImut.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function laporanImut()
    {
        return $this->belongsTo(LaporanImut::class);
    }

    /**
     * Relasi ke model UnitKerja.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * Relasi ke model ImutPenilaian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imutPenilaians()
    {
        return $this->hasMany(ImutPenilaian::class, 'laporan_unit_kerja_id');
    }

    /**
     * Hook model saat disimpan atau dihapus untuk menghapus cache terkait.
     */
    protected static function booted()
    {
        static::saved(fn($laporan) => $laporan->clearCache());
        static::deleted(fn($laporan) => $laporan->clearCache());
    }

    /**
     * Menghapus cache yang berkaitan dengan laporan ini.
     */
    public function clearCache(): void
    {
        $laporanId = $this->laporan_imut_id;
        $unitKerjaId = $this->unit_kerja_id;

        Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));
        Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
    }

    /**
     * Mengambil laporan berdasarkan unit kerja dengan total nilai dan persentase.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getReportByUnitKerja(int $laporanId)
    {
        return self::query()
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->leftJoin('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->select(
                'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'laporan_unit_kerjas.laporan_imut_id',
                DB::raw('COALESCE(SUM(imut_penilaians.numerator_value), 0) as total_numerator'),
                DB::raw('COALESCE(SUM(imut_penilaians.denominator_value), 0) as total_denominator'),
                DB::raw('
                    ROUND(
                        CASE
                            WHEN SUM(imut_penilaians.denominator_value) > 0
                            THEN SUM(imut_penilaians.numerator_value) * 100.0 / NULLIF(SUM(imut_penilaians.denominator_value), 0)
                            ELSE 0
                        END, 2
                    ) as percentage
                ')
            )
            ->groupBy(
                'laporan_unit_kerjas.id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'laporan_unit_kerjas.laporan_imut_id'
            );
    }

    /**
     * Mengambil laporan berdasarkan data IMUT dengan total nilai dan persentase.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getReportByImutData(int $laporanId)
    {
        return self::query()
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->leftJoin('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->leftJoin('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->leftJoin('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->select(
                'imut_data.id as id',
                'imut_data.title as imut_data_title',
                'laporan_unit_kerjas.laporan_imut_id',
                'imut_kategori.short_name as imut_kategori',
                DB::raw('COALESCE(SUM(imut_penilaians.numerator_value), 0) as total_numerator'),
                DB::raw('COALESCE(SUM(imut_penilaians.denominator_value), 0) as total_denominator'),
                DB::raw('
                    ROUND(
                        CASE
                            WHEN SUM(imut_penilaians.denominator_value) > 0
                            THEN SUM(imut_penilaians.numerator_value) * 100.0 / NULLIF(SUM(imut_penilaians.denominator_value), 0)
                            ELSE 0
                        END, 2
                    ) as percentage
                ')
            )
            ->groupBy(
                'imut_data.id',
                'imut_data.title',
                'laporan_unit_kerjas.laporan_imut_id'
            )
            ->orderBy('imut_data.title');
    }

    /**
     * Mengambil detail laporan berdasarkan unit kerja tertentu.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getReportByUnitKerjaDetails(int $laporanId, int $unitKerjaId)
    {
        return self::query()
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId)
            ->select(
                'imut_penilaians.id as id',
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name',
                'imut_data.title as imut_data',
                'imut_kategori.short_name as imut_kategori',
                'imut_profil.version as imut_profil',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                'imut_profil.start_period',
                'imut_profil.end_period',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_penilaians.recommendations',
                'imut_penilaians.analysis',
                'imut_kategori.id as imut_kategori_id',
                DB::raw('
                    ROUND(
                        CASE
                            WHEN imut_penilaians.denominator_value > 0 THEN
                                imut_penilaians.numerator_value * 100.0 / NULLIF(imut_penilaians.denominator_value, 0)
                            ELSE 0
                        END, 2
                    ) as percentage
                ')
            );
    }

    /**
     * Mengambil detail laporan berdasarkan data IMUT tertentu.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getReportByImutDataDetails(int $laporanId = 1, int $imutDataId = 1)
    {
        return self::query()
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('laporan_unit_kerjas.laporan_imut_id', $laporanId)
            ->where('imut_profil.imut_data_id', $imutDataId)
            ->select(
                'imut_penilaians.id as id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'unit_kerja.unit_name as unit_kerja',
                'imut_data.title as imut_data',
                'imut_kategori.short_name as imut_kategori',
                'imut_kategori.id as imut_kategori_id',
                'imut_profil.version as imut_profil',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                'imut_profil.start_period',
                'imut_profil.end_period',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_penilaians.recommendations',
                'imut_penilaians.analysis',
                DB::raw('
                    ROUND(
                        CASE
                            WHEN imut_penilaians.denominator_value > 0 THEN
                                imut_penilaians.numerator_value * 100.0 / NULLIF(imut_penilaians.denominator_value, 0)
                            ELSE 0
                        END, 2
                    ) as percentage
                ')
            );
    }

    public static function getLaporanByUnitKerjaDetails(int $imutDataId, int $unitKerjaId)
    {
        return self::query()
            ->join('laporan_imuts', 'laporan_imuts.id', '=', 'laporan_unit_kerjas.laporan_imut_id')
            ->join('unit_kerja', 'laporan_unit_kerjas.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->join('imut_profil', 'imut_penilaians.imut_profil_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('imut_data.id', $imutDataId)
            ->where('laporan_unit_kerjas.unit_kerja_id', $unitKerjaId)
            ->select(
                'imut_penilaians.id as id',
                'laporan_unit_kerjas.id as laporan_unit_kerja_id',
                'laporan_unit_kerjas.laporan_imut_id',
                'laporan_unit_kerjas.unit_kerja_id',
                'laporan_imuts.name as laporan_name',
                'laporan_imuts.status as laporan_status',
                'unit_kerja.unit_name',
                'imut_data.title as imut_data',
                'imut_kategori.short_name as imut_kategori',
                'imut_profil.version as imut_profil',
                'imut_profil.target_value as imut_standard',
                'imut_profil.target_operator as imut_standard_type_operator',
                'imut_profil.start_period',
                'imut_profil.end_period',
                'imut_penilaians.numerator_value',
                'imut_penilaians.denominator_value',
                'imut_penilaians.recommendations',
                'imut_penilaians.analysis',
                'imut_kategori.id as imut_kategori_id',
                DB::raw('
                ROUND(
                    CASE
                        WHEN imut_penilaians.denominator_value > 0 THEN
                            imut_penilaians.numerator_value * 100.0 / NULLIF(imut_penilaians.denominator_value, 0)
                        ELSE 0
                    END, 2
                ) as percentage
            ')
            );
    }
}