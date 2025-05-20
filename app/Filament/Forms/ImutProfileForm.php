<?php

namespace App\Filament\Forms;

use Filament\Forms;
use App\Models\RegionType;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ImutDataResource;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Actions\Action;
use Awcodes\TableRepeater\Components\TableRepeater;
use App\Filament\Resources\RegionTypeBencmarkingResource;
use Filament\Forms\Components\{TextInput, Textarea, Select};

class ImutProfileForm
{
    /**
     * Summary of make
     * @return Forms\Components\Tabs[]
     */
    public static function make(): array
    {
        return [
            Tabs::make('Form Profil Indikator')
                ->tabs([
                    Tab::make('Informasi Dasar')->schema(self::basicInformationSchema()),
                    Tab::make('Perhitungan')->schema(self::operationalDefinitionSchema()),
                    Tab::make('Data & Analisis')->schema(self::dataAndAnalysisSchema()),
                    Tab::make('🎯 Standar Indikator')->schema(self::standardIndicatorSchema())->visible(fn(?Model $record) => $record !== null && $record->imutData->categories->is_standardized_category === true),
                    Tab::make('📍 Benchmarking')->schema(self::benchmarkingSchema())->visible(fn(?Model $record) => ($record !== null && $record->imutData->categories->is_benchmark_category === true))
                ])
                ->columnSpan(['lg' => 2])
        ];
    }


    protected static function basicInformationSchema(): array
    {
        return [
            Section::make('Informasi Dasar')
                ->description('Isi data umum indikator mutu profil.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('version')
                            ->label('Versi')
                            ->helperText('Contoh: v1, v2.1')
                            ->required()
                            ->maxLength(10),

                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->placeholder('Nama lengkap penanggung jawab')
                            ->required()
                            ->maxLength(255),

                        ToggleButtons::make('indicator_type')
                            ->label('Tipe Indikator')
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
                            ->columnSpan(2)
                            ->helperText('Pilih jenis indikator yang sesuai.')
                    ])
                ]),

            Section::make('Deskripsi Indikator')
                ->description('Uraikan latar belakang, tujuan, dan makna indikator.')
                ->schema([
                    RichEditor::make('rationale')
                        ->label('Rasional')
                        ->hint('Mengapa indikator ini penting untuk diukur.'),

                    RichEditor::make('objective')
                        ->label('Tujuan')
                        ->hint('Apa yang ingin dicapai melalui indikator ini.'),

                    RichEditor::make('operational_definition')
                        ->label('Definisi Operasional')
                        ->hint('Penjelasan rinci istilah dalam indikator.'),

                    TextInput::make('quality_dimension')
                        ->label('Dimensi Mutu')
                        ->hint('Contoh: Efektivitas, Efisiensi, Aksesibilitas.'),
                ])
        ];
    }

    protected static function operationalDefinitionSchema(): array
    {
        return [
            Section::make('💡 Perhitungan Indikator')
                ->description('Masukkan rumus dan kriteria yang digunakan untuk menghitung indikator mutu.')
                ->schema([

                    Fieldset::make('🧮 Rumus Perhitungan')
                        ->columns(1)
                        ->schema([
                            Textarea::make('numerator_formula')
                                ->label('Rumus Pembilang')
                                ->rows(3)
                                ->required()
                                ->placeholder('Contoh: Jumlah pasien yang menerima layanan X...')
                                ->helperText('Rumus untuk bagian atas (numerator) dari indikator.'),

                            Textarea::make('denominator_formula')
                                ->label('Rumus Penyebut')
                                ->rows(3)
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
                                ->placeholder('Contoh: Pasien usia ≥ 18 tahun...')
                                ->helperText('Data yang harus disertakan.'),

                            TextInput::make('exclusion_criteria')
                                ->label('Kriteria Eksklusi')
                                ->required()
                                ->placeholder('Contoh: Pasien tanpa rekam medis lengkap...')
                                ->helperText('Data yang harus dikecualikan dari penghitungan.'),
                        ]),
                ])
        ];
    }

    protected static function dataAndAnalysisSchema(): array
    {
        return [
            Section::make('📥 Pengumpulan & 🔍 Analisis Data')
                ->description('Detail proses pengumpulan data, metode, dan perencanaan analisis indikator mutu.')
                ->schema([

                    // === Fieldset: Pengumpulan Data ===
                    Fieldset::make('📋 Informasi Pengumpulan')
                        ->columns(2)
                        ->schema([
                            TextInput::make('data_source')
                                ->label('Sumber Data')
                                ->placeholder('Contoh: EMR, Audit Form, Survey')
                                ->helperText('Sumber utama data indikator ini berasal dari mana.')
                                ->prefixIcon('heroicon-o-server'),

                            TextInput::make('data_collection_frequency')
                                ->label('Frekuensi Pengumpulan')
                                ->placeholder('Contoh: Bulanan, Mingguan')
                                ->helperText('Berapa sering data dikumpulkan.')
                                ->prefixIcon('heroicon-o-calendar-days'),

                            TextInput::make('data_collection_method')
                                ->label('Metode Pengumpulan')
                                ->placeholder('Contoh: Elektronik, Manual, Observasi')
                                ->helperText('Bagaimana proses pengumpulan data dilakukan.')
                                ->prefixIcon('heroicon-o-finger-print'),

                            TextInput::make('sampling_method')
                                ->label('Metode Sampling')
                                ->placeholder('Contoh: Total sampling, Random sampling')
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
                                ->helperText('Jenis periode yang digunakan dalam analisis.')
                                ->prefixIcon('heroicon-o-clock'),

                            TextInput::make('analysis_period_value')
                                ->label('Nilai Periode')
                                ->numeric()
                                ->placeholder('Contoh: 1, 3, 6')
                                ->helperText('Angka yang menunjukkan rentang waktu (dalam bulan/minggu).')
                                ->prefixIcon('heroicon-o-adjustments-horizontal'),

                            TextInput::make('target_value')
                                ->label('🎯 Nilai Target')
                                ->numeric()
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
                                ->helperText('Alat bantu atau instrumen yang digunakan dalam proses pengumpulan.'),

                            Textarea::make('analysis_plan')
                                ->label('Rencana Analisis')
                                ->placeholder('Langkah-langkah bagaimana data akan dianalisis untuk mengevaluasi indikator.')
                                ->rows(3)
                                ->helperText('Ceritakan secara ringkas bagaimana analisis dilakukan.'),
                        ]),
                ]),
        ];
    }

    protected static function standardIndicatorSchema(): array
    {
        return [
            Section::make('Target Standar Indikator')
                ->description('Masukkan target minimum dan maksimum indikator untuk periode tertentu.')
                ->schema([
                    TableRepeater::make('imut_standar')
                        ->label('Rentang Target')
                        ->relationship('imutStandards')
                        ->headers([
                            Header::make('start_period')->label('Mulai')->width('60px'),
                            Header::make('end_period')->label('Selesai')->width('60px'),
                            Header::make('value')->label('Target (%)')->width('60px'),
                            Header::make('value')->label('Deskripsi')->width('160px'),
                        ])
                        ->schema([
                            DatePicker::make('start_period')
                                ->label('Periode Mulai')
                                ->required()
                                ->native(false)
                                ->format('d F Y'),

                            DatePicker::make('end_period')
                                ->label('Periode Selesai')
                                ->required()
                                ->native(false)
                                ->format('d F Y'),

                            TextInput::make('value')
                                ->numeric()
                                ->step(1.0)
                                ->required()
                                ->suffix('%'),

                            TextInput::make('description')
                            // ->placeholder('Contoh: 95'),
                        ])
                        ->defaultItems(1)
                        ->minItems(1)
                        ->maxItems(10)
                        ->cloneable()
                        ->reorderable()
                        ->columnSpan('full'),
                ])
        ];
    }

    protected static function benchmarkingSchema(): array
    {
        return [
            Tabs::make('Benchmark Tabs')
                ->tabs(
                    RegionType::all()->map(function ($regionType) {
                        $headers = [
                            Header::make('year')->label('Tahun')->width('100px'),
                            Header::make('month')->label('Bulan')->width('100px'),
                            Header::make('benchmark_value')->label('Nilai Benchmark (%)')->width('180px'),
                        ];

                        $schema = [
                            TextInput::make('year')
                                ->numeric()
                                ->minValue(2000)
                                ->maxValue(now()->year + 1)
                                ->placeholder(now()->year)
                                ->required(),

                            Select::make('month')
                                ->options([
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember',
                                ])
                                ->required(),

                            TextInput::make('benchmark_value')
                                ->numeric()
                                ->step(0.01)
                                ->suffix('%')
                                ->placeholder('Contoh: 85.5')
                                ->required(),
                        ];

                        if ($regionType->type !== 'nasional') {
                            $schema = array_merge([
                                TextInput::make('region_name')
                                    ->placeholder($regionType->type === 'provinsi' ? 'Contoh: Jawa Barat' : 'Contoh: RS Harapan Sehat')
                                    ->required(),
                            ], $schema);

                            array_unshift($headers, Header::make('region_name')->label(
                                $regionType->type === 'provinsi' ? 'Nama Provinsi' : 'Nama Rumah Sakit'
                            )->width('200px'));
                        }

                        return Tab::make(ucfirst($regionType->type))
                            ->schema([
                                TableRepeater::make("{$regionType->type}_benchmarkings")
                                    ->label('')
                                    ->relationship('benchmarkings', fn($query) => $query->where('region_type_id', $regionType->id))
                                    ->headers($headers)
                                    ->schema($schema)
                                    ->defaultItems(1)
                                    ->cloneable()
                                    ->reorderable()
                                    ->columnSpan('full'),
                            ]);
                    })->push(
                        Tab::make('➕ Tambah Region Type')->schema([
                            Actions::make([
                                Action::make('create_region_type')
                                    ->icon('heroicon-m-plus')
                                    ->tooltip('Tambah Region Type baru')
                                    ->modalHeading('Tambah Region Type')
                                    ->form([
                                        TextInput::make('type')
                                            ->required()
                                            ->label('Nama Region Type')
                                            ->placeholder('Contoh: provinsi, rumah sakit, nasional'),
                                    ])
                                    ->action(function (array $data) {
                                        RegionType::create([
                                            'type' => $data['type'],
                                        ]);

                                        Notification::make()
                                            ->title('Berhasil')
                                            ->body('Region Type berhasil ditambahkan.')
                                            ->success()
                                            ->send();

                                        redirect(request()->header('Referer'));
                                    }),

                                Action::make('goto_region_type_list')
                                    ->icon('heroicon-m-list-bullet')
                                    ->tooltip('Lihat daftar semua Region Type')
                                    ->url(fn() => ImutDataResource::getUrl('bencmarking'))
                                    ->openUrlInNewTab(),
                            ])
                                ->label('Aksi')
                        ])

                    )
                        ->toArray()
                )
        ];
    }
}
