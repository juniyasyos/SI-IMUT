<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanNotify;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;

class EditImutData extends EditRecord
{
    use CanNotify;

    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lihat_grafik')
                ->label('Lihat Grafik')
                ->icon('heroicon-s-chart-bar')
                ->color('info')
                ->url(fn ($record) => \App\Filament\Resources\ImutDataResource\Pages\ImutDataOverview::getUrl(['record' => $record->slug])),
                
            $this->getDeleteAction(),
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
            ->visible(fn () => Gate::allows('delete_imut::data', ImutData::class))
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
