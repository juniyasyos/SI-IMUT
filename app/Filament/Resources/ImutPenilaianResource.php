<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutPenilaianResource\Pages;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

class ImutPenilaianResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ImutPenilaian::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'update',
            'delete',
            //
            'update_numerator_denominator',
            'update_profile_penilaian',
            'create_recommendation_penilaian',
            'force_editable'
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Penilaian IMUT')
                    ->tabs([

                        // Tab Profil
                        Tab::make('Profil IMUT')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                ...self::ImutPenilaianProfileSchema(),
                                ...self::basicInformationSchemaProfile(),
                                ...self::operationalDefinitionSchemaProfile(),
                                ...self::dataAndAnalysisSchemaProfile(),
                            ]),

                        // Tab Penilaian
                        Tab::make('Penilaian')
                            ->icon('heroicon-o-pencil')
                            ->schema(self::penilaianFormSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'edit' => Pages\EditImutPenilaian::route('/'),
        ];
    }

    protected static function ImutPenilaianProfileSchema(): array
    {
        return [
            Section::make('Informasi Profil')
                ->description('Pilih profil dan standar IMUT yang sesuai.')
                ->schema([
                    // Hidden field for imut_data_id
                    Hidden::make('imut_data_id'),

                    // Select: Versi Profil IMUT
                    Select::make('imut_profil_id')
                        ->label('Versi Profil IMUT')
                        ->options(function ($get) {
                            $imutDataId = $get('imut_data_id');

                            if ($imutDataId) {
                                return ImutProfile::where('imut_data_id', $imutDataId)
                                    ->get()
                                    ->mapWithKeys(fn($profile) => [
                                        $profile->id => "{$profile->version}",
                                    ])
                                    ->toArray();
                            }

                            return [];
                        })
                        ->disabled(
                            fn($livewire) =>
                            $livewire->isLaporanPeriodClosed()
                                && ! Auth::user()?->can('update_profile_penilaian_imut::penilaian')
                                && ! Auth::user()?->can('force_editable_imut::penilaian')
                        )
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
                                $set('target_operator', $profile->target_operator);
                                $set('target_value', $profile->target_value);
                                $set('start_periode', $profile->start_periode);
                                $set('end_periode', $profile->end_periode);
                                $set('data_collection_tool', $profile->data_collection_tool);
                                $set('analysis_plan', $profile->analysis_plan);
                            } else {
                                foreach (['imut_data_id', 'responsible_person', 'indicator_type', 'rationale', 'objective', 'operational_definition', 'quality_dimension', 'numerator_formula', 'denominator_formula', 'inclusion_criteria', 'exclusion_criteria', 'data_source', 'data_collection_frequency', 'data_collection_method', 'sampling_method', 'analysis_period_type', 'analysis_period_value', 'target_value', 'data_collection_tool', 'analysis_plan'] as $field) {
                                    $set($field, null);
                                }
                            }
                        }),

                    Select::make('target_operator')
                        ->label('🎯 Target Nilai')
                        ->options(function ($get) {
                            $value = $get('target_value') ?? '-';

                            return [
                                '>=' => "≥ $value",
                                '<=' => "≤ $value",
                                '>' => "> $value",
                                '<' => "< $value",
                                '=' => "= $value",
                            ];
                        })
                        ->disabled()
                        ->dehydrated(false),
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
                                'outcome' => 'heroicon-o-academic-cap',
                            ])
                            ->colors([
                                'process' => 'warning',
                                'output' => 'info',
                                'outcome' => 'success',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpan(1)
                            ->helperText('Pilih jenis indikator yang sesuai.'),
                    ]),
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
                ]),
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
                ]),
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
            Hidden::make('penilaian_id'),
            Section::make('Perhitungan')
                ->schema([
                    TextInput::make('numerator_value')
                        ->label('Numerator')
                        ->numeric()
                        ->placeholder('0.00')
                        ->nullable()
                        ->default(0)
                        ->reactive()
                        ->readOnly(
                            fn($livewire) =>
                            $livewire->isLaporanPeriodClosed()
                                && !Auth::user()?->can('force_editable_imut::penilaian')
                                || !Auth::user()?->can('update_numerator_denominator_imut::penilaian')
                        )
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            static::updateResult($set, $get);
                        }),

                    TextInput::make('denominator_value')
                        ->label('Denominator')
                        ->numeric()
                        ->placeholder('0.00')
                        ->default(0)
                        ->nullable()
                        ->reactive()
                        ->readOnly(
                            fn($livewire) =>
                            $livewire->isLaporanPeriodClosed()
                                && !Auth::user()?->can('force_editable_imut::penilaian')
                                || !Auth::user()?->can('update_numerator_denominator_imut::penilaian')
                        )
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            static::updateResult($set, $get);
                        }),

                    TextInput::make('result_operation')
                        ->label('Result (%)')
                        ->numeric()
                        ->placeholder('0.00')
                        ->readOnly()
                        ->dehydrated(false)
                        ->afterStateHydrated(function (callable $set, callable $get) {
                            static::updateResult($set, $get);
                        }),
                    SpatieMediaLibraryFileUpload::make('document_upload')
                        ->label('Unggah Dokumen Pendukung')
                        ->collection(fn(callable $get) => $get('selected_collection') ?? 'default')
                        ->directory(fn(callable $get) => 'uploads/imut-documents/' . ($get('selected_collection') ?? 'default'))
                        ->openable()
                        ->downloadable()
                        ->maxSize(20480)
                        ->preserveFilenames()
                        ->previewable(true)
                        ->columnSpanFull()
                        ->disabled(fn($livewire) => $livewire->isLaporanPeriodClosed()
                            && !Auth::user()?->can('force_editable_imut::penilaian')
                            || ! Auth::user()?->can('update_numerator_denominator_imut::penilaian'))
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
                        ->readOnly(
                            fn($livewire) => $livewire->isLaporanPeriodClosed()
                                && !Auth::user()?->can('force_editable_imut::penilaian')
                                || ! Auth::user()?->can('update_numerator_denominator_imut::penilaian')
                        )
                        ->placeholder('Tuliskan hasil analisis...')
                        ->columnSpanFull(),

                    Textarea::make('recommendations')
                        ->label('Rekomendasi')
                        ->disabled(
                            fn() =>
                            ! Auth::user()?->can('create_recommendation_penilaian_imut::penilaian')
                                && ! Auth::user()?->can('force_editable_imut::penilaian')
                        )
                        ->rows(4)
                        ->placeholder('Berikan saran atau rekomendasi...')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function updateResult(callable $set, callable $get): void
    {
        $numerator = floatval($get('numerator_value') ?? 0);
        $denominator = floatval($get('denominator_value') ?? 0);

        $result = $denominator > 0
            ? round(($numerator / $denominator) * 100, 2)
            : 0;

        $set('result_operation', $result);
    }
}