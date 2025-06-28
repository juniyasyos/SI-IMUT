<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\CanNotify;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ImutDataResource\Pages\ImutDataUnitKerjaOverview;

class EditImutData extends EditRecord
{
    use CanNotify;

    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('lihat_grafik')
                    ->label('📊 IMUT DATA')
                    ->color('primary')
                    ->url(fn ($record) => \App\Filament\Resources\ImutDataResource\Pages\SummaryImutDataDiagram::getUrl(['record' => $record->slug])),

                RelationManagerAction::make('unit-kerja-relation')
                    ->slideOver()
                    ->label('🏢 Unit Kerja')
                    ->record($this->getRecord())
                    ->color('primary')
                    ->relationManager(\App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager::make()),

            ])
                ->button()
                ->label('Lihat Grafik')
                ->icon('heroicon-s-chart-bar')
                ->visible(fn () => Auth::user()?->can('view_all_data_imut::data')),

            Action::make('lihat_berdasarkan_unit_kerja')
                ->label('🏢 Lihat Grafik')
                ->color('success')
                ->visible(function () {
                    $user = Auth::user();

                    return $user->can('view_by_unit_kerja_imut::data') &&
                           ! $user->can('view_all_data_imut::data') &&
                           $user->unitKerjas->isNotEmpty();
                })
                ->url(function ($record) {
                    $user = Auth::user();
                    $unitKerja = $user->unitKerjas->first();

                    if (! $unitKerja) {
                        return '#';
                    }

                    return ImutDataUnitKerjaOverview::getUrl([
                        'record_imut_data' => $record->id,
                        'record_unit_kerja' => $unitKerja->id,
                    ]);
                }),

            $this->getDeleteAction(),
        ];
    }

    public static function canEditProfilIndikator(?Model $record = null): bool
    {
        $user = Auth::user();

        return $record?->created_by === $user?->id;
    }

    protected function getFormActions(): array
    {
        $user = Auth::user();
        $record = $this->getRecord();

        $isCreator = $record?->created_by === $user?->id;

        if (! $isCreator) {
            return [];
        }

        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.imut-datas.index') => 'IMUT Data',
            null => $this->record->title,
        ];
    }

    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // 🔔 Fungsi notifikasi fleksibel menggunakan Filament Notification
    protected function notifyUser(string $type, string $message): void
    {
        Notification::make()
            ->title($message)
            ->send();
    }

    // 🗑️ Delete action
    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label(__('filament-forms::imut-data.actions.delete.label'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn ($record) => Auth::user()?->can('delete_imut::data') || $record->creator === Auth::user()->id)
            ->modalHeading(__('filament-forms::imut-data.actions.delete.modal_heading'))
            ->modalDescription(__('filament-forms::imut-data.actions.delete.modal_description'))
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalSubmitActionLabel(__('filament-forms::imut-data.actions.delete.modal_submit_label'))
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title(__('filament-forms::imut-data.notifications.deleted.title'))
                    ->body(__('filament-forms::imut-data.notifications.deleted.body'))
            );
    }
}
