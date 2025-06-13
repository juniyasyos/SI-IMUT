<?php

namespace App\Filament\Resources\ImutDataResource\RelationManagers;

use App\Filament\Pages\ImutDataOverview;
use App\Models\UnitKerja;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class UnitKerjaRelationManager extends RelationManager
{
    protected static string $relationship = 'unitKerja';

    public function form(Form $form): Form
    {
        return $form;
        // ->schema([
        //     Forms\Components\TextInput::make('unit_name')
        //         ->label('Nama Unit')
        //         ->required()
        //         ->maxLength(255),
        // ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_name')
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')->label('Nama Unit Kerja'),
                Tables\Columns\TextColumn::make('pivot.assignedBy.name')
                    ->label('Ditugaskan Oleh')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.assigned_at')
                    ->label('Tanggal Penugasan')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Unit Kerja')
                    ->color('primary')
                    ->recordSelect(function ($livewire) {
                        $relatedIds = $livewire->ownerRecord->unitKerja()->pluck('id')->toArray();

                        return Select::make('recordId')
                            ->label('Pilih Unit Kerja')
                            ->placeholder('Cari unit kerja...')
                            ->helperText('Pilih unit kerja yang ingin ditautkan')
                            ->options(
                                UnitKerja::whereNotIn('id', $relatedIds)
                                    ->pluck('unit_name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required();
                    })
                    ->modalHeading('Tambah Unit Kerja ke Imut Data')
                    ->modalSubmitActionLabel('Simpan')
                    ->preloadRecordSelect()
                    ->action(function (array $data, $livewire) {
                        $imut = $livewire->ownerRecord;

                        $imut->unitKerja()->attach($data['recordId'], [
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                        ]);
                    })
                    ->attachAnother(false)
                    ->recordSelectSearchColumns(['unit_name']),
            ])
            ->actions([
                Action::make('lihat_berdasarkan_unit_kerja')
                    ->label('🏢 Lihat Unit Kerja')
                    ->color('success')
                    // ->url(fn ($record) => ImutDataOverview::getUrl(['record' => $record->slug]))
                    ->openUrlInNewTab(),
                Tables\Actions\DetachAction::make()
                    ->label('Lepas')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()->label('Lepas Beberapa'),
                ]),
            ]);
    }
}