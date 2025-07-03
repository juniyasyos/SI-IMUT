<?php

namespace App\Filament\Resources\ImutDataResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ImutProfile;
use Illuminate\Support\Facades\Gate;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ImutDataResource;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{Action, CreateAction, EditAction, DeleteAction, DeleteBulkAction, BulkActionGroup};
use Illuminate\Support\Facades\Auth;

class ProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'profiles';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version')
            ->columns([
                TextColumn::make('version')
                    ->label('Versi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('indicator_type')
                    ->label('Tipe Indikator')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'process' => 'info',
                        'output' => 'warning',
                        'outcome' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('responsible_person')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->limit(50),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(function ($livewire) {
                        $owner = $livewire->ownerRecord;
                        return Auth::user()?->can('create_imut::profile') && $owner->created_by === Auth::id();
                    })
                    ->url(fn($livewire) => ImutDataResource::getUrl('create-profile', [
                        'imutDataSlug' => $livewire->ownerRecord->slug,
                    ])),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                // ViewAction::make()
                // ->slideOver()
                // ->form([
                //     TextInput::make('version')->disabled(),
                //     TextInput::make('indicator_type')->disabled(),
                //     TextInput::make('responsible_person')->disabled(),
                // ]),
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->visible(true)
                    ->url(fn($record, $livewire) => ImutDataResource::getUrl('edit-profile', [
                        'imutDataSlug' => $livewire->ownerRecord->slug,
                        'record' => $record->slug,
                    ])),
                \Filament\Tables\Actions\ReplicateAction::make()
                    ->using(function (Model $record) {
                        $newRecord = $record->replicate();
                        $originalVersion = $record->version;

                        $newVersion = "Copy dari $originalVersion";
                        $newRecord->version = $newVersion;

                        $slugBase = \Illuminate\Support\Str::slug($newVersion); // slugify version
                        $uuid = \Illuminate\Support\Str::uuid()->toString();

                        $newRecord->slug = "{$slugBase}-{$uuid}";

                        $newRecord->push();

                        return $newRecord;
                    })
                    ->visible(! fn(?Model $record) => $record && $record->imutData->created_by !== Auth::id())
                    ->successNotificationTitle('Imut Profile successfully replicated'),

                DeleteAction::make()
                    ->visible(! Auth::user()?->can('delete_imut::profile')),
                RestoreAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
                ForceDeleteAction::make()->visible(fn(Model $record) => method_exists($record, 'trashed') && $record->trashed()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            (Auth::user()?->can('delete_imut::profile'))
                                || Auth::user()?->can('force_editable_imut::profile')
                        ),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                ]),
            ])
            ->paginated(true)
            ->defaultPaginationPageOption(10);
    }
}