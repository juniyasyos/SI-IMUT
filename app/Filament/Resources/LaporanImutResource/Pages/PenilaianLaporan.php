<?php

declare(strict_types=1);

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use App\Filament\Resources\LaporanImutResource;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\UnitKerja;
use Filament\Forms;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PenilaianLaporan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.penilaian-laporan';

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->can('view_imut_penilaian_laporan::imut');
    }

    /**
     * The LaporanImut model instance related to this page.
     */
    public ?LaporanImut $laporan = null;
    public ?ImutProfile $profile = null;
    public ?UnitKerja $unitKerja = null;
    public ?ImutData $imutData = null;

    /**
     * Form data keyed by ImutPenilaian ID.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $formData = [];

    /**
     * Mount the page, load laporan and penilaian data.
     *
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function mount(): void
    {
        $laporanId = request()->integer('laporan_id');
        $penilaianId = request()->integer('penilaian_id');

        // if (!$laporanId || !$penilaianId) {
        //     abort(404, 'Invalid request parameters.');
        // }

        // Verify laporan has the penilaian with the given ID
        $this->laporan = LaporanImut::
            // ->whereHas('imutPenilaians', fn($query) => $query->where('imut_penilaians.id', $penilaianId))
            findOrFail($laporanId);
        // $this->laporan = LaporanImut::select(['id', 'name'])
        //     // ->whereHas('imutPenilaians', fn($query) => $query->where('imut_penilaians.id', $penilaianId))
        //     ->findOrFail($laporanId);

        // dd($this->laporan);

        // Fetch the specific penilaian for this laporan
        $penilaian = ImutPenilaian::with('profile')
            ->where('id', $penilaianId)
            // ->where('laporan_unit_kerja_id', $laporanId)
            ->firstOrFail();

        // dd($this->laporan, $penilaian);

        $this->profile = $penilaian->profile;
        $this->unitKerja = $penilaian->laporanUnitKerja->unitKerja;
        $this->imutData = $this->profile->imutData;

        $this->formData = [
            'penilaian_id' => $penilaian->id,
            'analysis' => $penilaian->analysis,
            'recommendations' => $penilaian->recommendations,
            'numerator_value' => $penilaian->numerator_value,
            'denominator_value' => $penilaian->denominator_value,
            'imut_profile_id' => $this->profile->id,
            'imut_data_id' => $this->imutData->id,

            'responsible_person' => $this->profile->responsible_person,
            'indicator_type' => $this->profile->indicator_type,
            'rationale' => $this->profile->rationale,
            'objective' => $this->profile->objective,
            'operational_definition' => $this->profile->operational_definition,
            'quality_dimension' => $this->profile->quality_dimension,
            'numerator_formula' => $this->profile->numerator_formula,
            'denominator_formula' => $this->profile->denominator_formula,
            'inclusion_criteria' => $this->profile->inclusion_criteria,
            'exclusion_criteria' => $this->profile->exclusion_criteria,
            'data_source' => $this->profile->data_source,
            'data_collection_frequency' => $this->profile->data_collection_frequency,
            'data_collection_method' => $this->profile->data_collection_method,
            'sampling_method' => $this->profile->sampling_method,
            'analysis_period_type' => $this->profile->analysis_period_type,
            'analysis_period_value' => $this->profile->analysis_period_value,
            'target_value' => $this->profile->target_value,
            'data_collection_tool' => $this->profile->data_collection_tool,
            'analysis_plan' => $this->profile->analysis_plan,
        ];

        $this->form->fill($this->formData);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Penilaian')
                ->action(fn() => $this->simpanPenilaian())
                ->requiresConfirmation()
                ->color('success'),
        ];
    }

    /**
     * Get the form schema for the penilaian fields.
     *
     * @return array<int, Forms\Components\Component>
     */
    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Penilaian IMUT')
                ->tabs([

                    // Tab Profil
                    Tab::make('Profil IMUT')
                        ->icon('heroicon-o-book-open')
                        ->schema([
                            ...self::ImutPenilaianProfileSchema(),
                            ...self::basicInformationSchemaProfile(),
                            ...self::operationalDefinitionSchemaProfile(),
                            ...self::dataAndAnalysisSchemaProfile()
                        ]),

                    // Tab Penilaian
                    Tab::make('Penilaian')
                        ->icon('heroicon-o-pencil')
                        ->schema(self::penilaianFormSchema()),
                ])
                ->columnSpanFull()
        ];
    }

    protected static function ImutPenilaianProfileSchema(): array
    {
        return [
            Section::make('Informasi Profil')
                ->disabled(fn() => !Auth::user()?->can('update_profile_penilaian_laporan::imut'))
                ->description('Pilih profil dan standar IMUT yang sesuai.')
                ->schema([
                    // Hidden field for imut_data_id
                    Hidden::make('imut_data_id'),

                    // Select: Versi Profil IMUT
                    Select::make('imut_profile_id')
                        ->label('Versi Profil IMUT')
                        ->options(function ($get) {
                            $imutDataId = $get('imut_data_id');

                            if ($imutDataId) {
                                return ImutProfile::where('imut_data_id', $imutDataId)
                                    ->get()
                                    ->mapWithKeys(fn($profile) => [
                                        $profile->id => "{$profile->version}"
                                    ])
                                    ->toArray();
                            }

                            return [];
                        })
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->required()
                        ->placeholder('Pilih versi profil')
                        ->afterStateUpdated(function ($state, callable $set) {
                            $profile = ImutProfile::find($state);

                            if ($profile) {
                                $set('imut_data_id', $profile->imut_data_id);
                                $set('responsible_person', $profile->responsible_person);
                                $set('indicator_type', $profile->indicator_type);
                                $set('rationale', $profile->rationale);
                                $set('objective', $profile->objective);
                                $set('operational_definition', $profile->operational_definition);
                                $set('quality_dimension', $profile->quality_dimension);
                                $set('numerator_formula', $profile->numerator_formula);
                                $set('denominator_formula', $profile->denominator_formula);
                                $set('inclusion_criteria', $profile->inclusion_criteria);
                                $set('exclusion_criteria', $profile->exclusion_criteria);
                                $set('data_source', $profile->data_source);
                                $set('data_collection_frequency', $profile->data_collection_frequency);
                                $set('data_collection_method', $profile->data_collection_method);
                                $set('sampling_method', $profile->sampling_method);
                                $set('analysis_period_type', $profile->analysis_period_type);
                                $set('analysis_period_value', $profile->analysis_period_value);
                                $set('target_value', $profile->target_value);
                                $set('data_collection_tool', $profile->data_collection_tool);
                                $set('analysis_plan', $profile->analysis_plan);
                            } else {
                                foreach (['imut_data_id', 'responsible_person', 'indicator_type', 'rationale', 'objective', 'operational_definition', 'quality_dimension', 'numerator_formula', 'denominator_formula', 'inclusion_criteria', 'exclusion_criteria', 'data_source', 'data_collection_frequency', 'data_collection_method', 'sampling_method', 'analysis_period_type', 'analysis_period_value', 'target_value', 'data_collection_tool', 'analysis_plan'] as $field) {
                                    $set($field, null);
                                }
                            }
                        }),

                    // Select: Standar IMUT
                    // Select::make('imut_standar_id')
                    //     ->label('Standar IMUT')
                    //     ->options(function ($get) {
                    //         $profileId = $get('imut_profile_id');

                    //         if ($profileId) {
                    //             return ImutStandard::where('imut_profile_id', $profileId)
                    //                 ->get()
                    //                 ->mapWithKeys(fn($standard) => [
                    //                     $standard->id => "{$standard->value} - {$standard->description}"
                    //                 ])
                    //                 ->toArray();
                    //         }

                    //         return [];
                    //     })
                    //     ->searchable()
                    //     ->preload()
                    //     ->reactive()
                    //     ->required()
                    //     ->placeholder('Pilih standar nilai'),

                ])
                ->columns(2),
        ];
    }

    protected static function basicInformationSchemaProfile(): array
    {
        return [
            Section::make('Informasi Dasar')
                ->description('Isi data umum indikator mutu profil.')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->placeholder('Nama lengkap penanggung jawab')
                            ->required()
                            ->readOnly()
                            ->columnSpan(1)
                            ->maxLength(255),

                        ToggleButtons::make('indicator_type')
                            ->label('Tipe Indikator')
                            ->disabled()
                            ->options([
                                'process' => 'Proses',
                                'output' => 'Output',
                                'outcome' => 'Outcome',
                            ])
                            ->icons([
                                'process' => 'heroicon-o-cog',
                                'output' => 'heroicon-o-chart-bar',
                                'outcome' => 'heroicon-o-academic-cap'
                            ])
                            ->colors([
                                'process' => 'warning',
                                'output' => 'info',
                                'outcome' => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpan(1)
                            ->helperText('Pilih jenis indikator yang sesuai.')
                    ])
                ]),

            Section::make('Deskripsi Profil Indikator')
                ->collapsed()
                ->description('Uraikan latar belakang, tujuan, dan makna indikator.')
                ->schema([
                    TextInput::make('rationale')
                        ->label('Rasional')
                        ->readOnly(),

                    TextArea::make('objective')
                        ->label('Tujuan')
                        ->readOnly(),

                    TextArea::make('operational_definition')
                        ->label('Definisi Operasional')
                        ->readOnly(),

                    TextInput::make('quality_dimension')
                        ->label('Dimensi Mutu')
                        ->readOnly(),
                ])
        ];
    }

    protected static function operationalDefinitionSchemaProfile(): array
    {
        return [
            Section::make('💡 Perhitungan Indikator')
                ->collapsed()
                ->description('Masukkan rumus dan kriteria yang digunakan untuk menghitung indikator mutu.')
                ->schema([

                    Fieldset::make('🧮 Rumus Perhitungan')
                        ->columns(1)
                        ->schema([
                            Textarea::make('numerator_formula')
                                ->label('Rumus Pembilang')
                                ->rows(3)
                                ->readOnly()
                                ->required()
                                ->placeholder('Contoh: Jumlah pasien yang menerima layanan X...')
                                ->helperText('Rumus untuk bagian atas (numerator) dari indikator.'),

                            Textarea::make('denominator_formula')
                                ->label('Rumus Penyebut')
                                ->rows(3)
                                ->readOnly()
                                ->required()
                                ->placeholder('Contoh: Jumlah total pasien yang memenuhi syarat...')
                                ->helperText('Rumus untuk bagian bawah (denominator) dari indikator.'),
                        ]),

                    Fieldset::make('📋 Kriteria Data')
                        ->columns(2)
                        ->schema([
                            TextInput::make('inclusion_criteria')
                                ->label('Kriteria Inklusi')
                                ->required()
                                ->readOnly()
                                ->placeholder('Contoh: Pasien usia ≥ 18 tahun...')
                                ->helperText('Data yang harus disertakan.'),

                            TextInput::make('exclusion_criteria')
                                ->label('Kriteria Eksklusi')
                                ->required()
                                ->readOnly()
                                ->placeholder('Contoh: Pasien tanpa rekam medis lengkap...')
                                ->helperText('Data yang harus dikecualikan dari penghitungan.'),
                        ]),
                ])
        ];
    }

    protected static function dataAndAnalysisSchemaProfile(): array
    {
        return [
            Section::make('📥 Pengumpulan & 🔍 Analisis Data')
                ->collapsed()
                ->description('Detail proses pengumpulan data, metode, dan perencanaan analisis indikator mutu.')
                ->schema([

                    // === Fieldset: Pengumpulan Data ===
                    Fieldset::make('📋 Informasi Pengumpulan')
                        ->columns(2)
                        ->schema([
                            TextInput::make('data_source')
                                ->label('Sumber Data')
                                ->placeholder('Contoh: EMR, Audit Form, Survey')
                                ->readOnly()
                                ->helperText('Sumber utama data indikator ini berasal dari mana.')
                                ->prefixIcon('heroicon-o-server'),

                            TextInput::make('data_collection_frequency')
                                ->label('Frekuensi Pengumpulan')
                                ->placeholder('Contoh: Bulanan, Mingguan')
                                ->helperText('Berapa sering data dikumpulkan.')
                                ->readOnly()
                                ->prefixIcon('heroicon-o-calendar-days'),

                            TextInput::make('data_collection_method')
                                ->label('Metode Pengumpulan')
                                ->placeholder('Contoh: Elektronik, Manual, Observasi')
                                ->readOnly()
                                ->helperText('Bagaimana proses pengumpulan data dilakukan.')
                                ->prefixIcon('heroicon-o-finger-print'),

                            TextInput::make('sampling_method')
                                ->label('Metode Sampling')
                                ->placeholder('Contoh: Total sampling, Random sampling')
                                ->readOnly()
                                ->helperText('Metode pemilihan sampel data untuk dianalisis.')
                                ->prefixIcon('heroicon-o-beaker'),
                        ]),

                    // === Fieldset: Detail Analisis ===
                    Fieldset::make('📈 Detail Analisis')
                        ->columns(2)
                        ->schema([
                            TextInput::make('analysis_period_type')
                                ->label('Tipe Periode Analisis')
                                ->placeholder('Contoh: Bulanan, Semester')
                                ->readOnly()
                                ->helperText('Jenis periode yang digunakan dalam analisis.')
                                ->prefixIcon('heroicon-o-clock'),

                            TextInput::make('analysis_period_value')
                                ->label('Nilai Periode')
                                ->numeric()
                                ->readOnly()
                                ->placeholder('Contoh: 1, 3, 6')
                                ->helperText('Angka yang menunjukkan rentang waktu (dalam bulan/minggu).')
                                ->prefixIcon('heroicon-o-adjustments-horizontal'),

                            TextInput::make('target_value')
                                ->label('🎯 Nilai Target')
                                ->numeric()
                                ->readOnly()
                                ->placeholder('Contoh: 90, 95, 100')
                                ->helperText('Target pencapaian kinerja indikator.')
                                ->prefixIcon('heroicon-o-arrow-trending-up'),
                        ]),

                    // === Alat & Rencana Analisis ===
                    Fieldset::make('🛠️ Alat & Strategi Analisis')
                        ->columns(1)
                        ->schema([
                            Textarea::make('data_collection_tool')
                                ->label('Alat Kumpul Data')
                                ->placeholder('Contoh: Kuesioner, Google Form, EMR, Form Audit')
                                ->rows(2)
                                ->readOnly()
                                ->helperText('Alat bantu atau instrumen yang digunakan dalam proses pengumpulan.'),

                            Textarea::make('analysis_plan')
                                ->label('Rencana Analisis')
                                ->placeholder('Langkah-langkah bagaimana data akan dianalisis untuk mengevaluasi indikator.')
                                ->rows(3)
                                ->readOnly()
                                ->helperText('Ceritakan secara ringkas bagaimana analisis dilakukan.'),
                        ]),
                ]),
        ];
    }

    protected static function penilaianFormSchema(): array
    {
        return [
            Forms\Components\Hidden::make('penilaian_id'),
            Section::make('Perhitungan')
                ->schema([
                    TextInput::make('numerator_value')
                        ->label('Numerator')
                        ->numeric()
                        ->placeholder('0.00')
                        ->readOnly(fn() => !Auth::user()?->can('update_numerator_denominator_laporan::imut'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state, callable $get) {
                            $denominator = $get('denominator_value') ?? 0;
                            $result = ($denominator == 0) ? 0 : round(($state / $denominator) * 100, 2);
                            $set('result_operation', $result);
                        }),

                    TextInput::make('denominator_value')
                        ->label('Denominator')
                        ->readOnly(fn() => !Auth::user()?->can('update_numerator_denominator_laporan::imut'))
                        ->numeric()
                        ->placeholder('0.00')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state, callable $get) {
                            $numerator = $get('numerator_value') ?? 0;
                            $result = ($state == 0) ? 0 : round(($numerator / $state) * 100, 2);
                            $set('result_operation', $result);
                        }),

                    TextInput::make('result_operation')
                        ->label('Result (%)')
                        ->numeric()
                        ->placeholder('0.00')
                        ->readOnly()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (callable $set, $state, callable $get) {
                            $numerator = $get('numerator_value') ?? 0;
                            $denominator = $get('denominator_value') ?? 0;

                            $result = ($denominator == 0) ? 0 : round(($numerator / $denominator) * 100, 2);

                            $set('result_operation', $result);
                        }),

                    FileUpload::make('document_upload')
                        ->label('Unggah Dokumen Pendukung')
                        ->openable()
                        ->downloadable()
                        ->maxSize(20480)
                        ->columnSpanFull()
                        ->previewable(true)
                        ->preserveFilenames()
                        ->directory('uploads/imut-documents')
                        ->disabled(fn() => !Auth::user()?->can('update_numerator_denominator_laporan::imut'))
                        ->acceptedFileTypes([
                            'application/pdf',
                            'image/*',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->helperText('File yang didukung: PDF, Word, Excel, Gambar. Maks. 20MB'),
                ])
                ->columns(3),

            Section::make('Analisis dan Rekomendasi')
                ->schema([
                    Textarea::make('analysis')
                        ->label('Analisis')
                        ->rows(4)
                        ->placeholder('Tuliskan hasil analisis...')
                        ->required()
                        ->columnSpanFull(),

                    Textarea::make('recommendations')
                        ->label('Rekomendasi')
                        ->disabled(fn() => !Auth::user()?->can('create_recommendation_penilaian_laporan::imut'))
                        ->rows(4)
                        ->placeholder('Berikan saran atau rekomendasi...')
                        ->required()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * Get the form state path.
     *
     * @return string
     */
    protected function getFormStatePath(): string
    {
        return 'formData';
    }

    /**
     * Save the updated penilaian data to the database.
     *
     * @return void
     */
    public function save(): void
    {
        foreach ($this->formData as $id => $data) {
            $penilaian = ImutPenilaian::find($id);

            if (!$penilaian) {
                continue;
            }

            $penilaian->fill($data);

            if ($penilaian->isDirty()) {
                $penilaian->save();
            }
        }

        $this->notify('success', 'All penilaian changes have been saved successfully.');
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Penilaian IMUT : ' . ($this->imutData->title ?? 'Unknown');
    }

    /**
     * Generate breadcrumbs array for navigation.
     *
     * @return array<string, string|array<string, mixed>>
     */
    public function getBreadcrumbs(): array
    {
        // dd($this->laporan);
        $laporanName = $this->laporan?->name ?? 'Detail Laporan';
        $unitKerjaName = $this->unitKerja?->unit_name ?? 'Unit Kerja';
        $imutDataTitle = $this->imutData?->title ?? 'Data IMUT';
        $profileVersion = $this->profile?->version ?? 'Versi Profil';

        return [
            LaporanImutResource::getUrl('index') => 'Daftar Laporan IMUT',
            LaporanImutResource::getUrl('edit', ['record' => $this->laporan->slug]) => $laporanName,
            "Penilaian Laporan",
            "{$unitKerjaName}",
            "{$imutDataTitle} | {$profileVersion}"
        ];
    }

    public function simpanPenilaian(): void
    {
        // Ambil data form dari state
        $data = $this->form->getState();

        // Validasi eksplisit
        $validated = Validator::make($data, [
            'penilaian_id' => ['required', 'integer', Rule::exists(ImutPenilaian::class, 'id')],
            'analysis' => ['required', 'string'],
            'recommendations' => ['required', 'string'],
            'numerator_value' => ['required', 'numeric'],
            'denominator_value' => ['required', 'numeric'],
            'document_upload' => ['nullable', 'array'],
        ])->validate();

        // Cari record berdasarkan ID
        $penilaian = ImutPenilaian::findOrFail($validated['penilaian_id']);

        // Update nilai-nilai yang dibutuhkan
        $penilaian->update([
            'analysis' => $validated['analysis'],
            'recommendations' => $validated['recommendations'],
            'numerator_value' => $validated['numerator_value'],
            'denominator_value' => $validated['denominator_value'],
            'document_upload' => $validated['document_upload'] ?? [],
        ]);

        // Notifikasi sukses
        Notification::make()
            ->title('Penilaian berhasil disimpan.')
            ->success()
            ->send();
    }
}
