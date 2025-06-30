<?php

namespace App\Filament\Resources\LaporanImutResource\Schema;

use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Facades\Auth;

class LaporanImutSchema
{
    public static function make(): array
    {
        return [
            Section::make('Informasi Laporan')
                ->description('Lengkapi data laporan di bawah ini.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Laporan')
                        ->required()
                        ->maxLength(255)
                        ->unique('laporan_imuts', 'name', ignoreRecord: true)
                        ->columnSpanFull()
                        ->default(function () {
                            $now = Carbon::now();

                            return 'Laporan IMUT Periode ' . $now->translatedFormat('m/Y');
                        }),

                    DatePicker::make('assessment_period_start')
                        ->label('Dimulainya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->reactive()
                        ->default(now()->format('Y-m-d')),

                    DatePicker::make('assessment_period_end')
                        ->label('Berakhirnya Periode Asesmen')
                        ->placeholder('YYYY-MM-DD')
                        ->required()
                        ->minDate(fn(callable $get) => $get('assessment_period_start'))
                        ->rule('after_or_equal:assessment_period_start')
                        ->afterStateUpdated(function (callable $set, $state) {
                            if ($state < now()->toDateString()) {
                                $set('status', 'complete');
                            } else {
                                $set('status', 'process');
                            }
                        }),

                    ToggleButtons::make('status')
                        ->label('Status Laporan')
                        ->options([
                            'process' => 'Dalam Proses',
                            'complete' => 'Selesai',
                            'coming_soon' => 'Segera Hadir',
                        ])
                        ->icons([
                            'process' => 'heroicon-o-arrow-path',
                            'complete' => 'heroicon-o-check-circle',
                            'coming_soon' => 'heroicon-o-clock',
                        ])
                        ->colors([
                            'process' => 'warning',
                            'complete' => 'success',
                            'coming_soon' => 'gray',
                        ])
                        ->disabled()
                        ->default('process')
                        ->inline()
                        ->helperText('Status laporan ditentukan otomatis berdasarkan tanggal berakhirnya periode asesmen.'),

                    Select::make('created_by')
                        ->label('Dibuat oleh')
                        ->options(User::pluck('name', 'id'))
                        ->default(fn() => Auth::id())
                        ->disabled()
                        ->columnSpanFull(),

                    Section::make('Unit Kerja')
                        ->description('Pilih unit kerja yang akan mengisi indikator mutu.')
                        ->columnSpanFull()
                        ->schema([
                            CheckboxList::make('unitKerjas')
                                ->relationship('unitKerjas', 'unit_name')
                                ->label('Unit Kerja yang Bisa Menilai')
                                ->columns(3)
                                ->required()
                                // ->disabledOn('edit')
                                ->bulkToggleable()
                                ->default(UnitKerja::pluck('id')->toArray()),
                        ]),
                ])
                ->columns(2),
        ];
    }
}
