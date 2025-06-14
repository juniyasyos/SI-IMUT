<?php

namespace App\Filament\Forms;

use App\Filament\Resources\ImutDataResource;
use App\Models\RegionType;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class ImutBenchmarkingForm
{
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

                        $schema = array_merge([
                            TextInput::make('region_name')
                                ->placeholder($regionType->type === 'provinsi' ? 'Contoh: Jawa Barat' : 'Contoh: RS Harapan Sehat')
                                ->required(),
                        ], $schema);

                        array_unshift($headers, Header::make('region_name')->label($regionType->type)->width('200px'));

                        return Tab::make(ucfirst($regionType->type))
                            ->schema([
                                TableRepeater::make("{$regionType->type}_benchmarkings")
                                    ->label('')
                                    ->streamlined()
                                    ->relationship('benchmarkings', fn ($query) => $query->where('region_type_id', $regionType->id))
                                    ->headers($headers)
                                    ->schema($schema)
                                    ->defaultItems(1)
                                    ->cloneable(false)
                                    ->reorderable(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->columnSpan('full')
                                    ->extraActions([
                                        Action::make('exportData')
                                            ->icon('heroicon-m-inbox-arrow-down')
                                            ->action(function (TableRepeater $component): void {
                                                Notification::make('export_data')
                                                    ->success()
                                                    ->title('Data exported.')
                                                    ->send();
                                            }),
                                    ]),
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
                                    ->url(fn () => ImutDataResource::getUrl('bencmarking'))
                                    ->openUrlInNewTab(),
                            ])
                                ->label('Aksi'),
                        ])

                    )
                        ->toArray()
                ),
        ];
    }
}
