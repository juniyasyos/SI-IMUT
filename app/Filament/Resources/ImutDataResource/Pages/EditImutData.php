<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\Concerns\CanNotify;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ImutDataResource;

class EditImutData extends EditRecord
{
    use CanNotify;

    protected static string $resource = ImutDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAction(),
            $this->getSaveAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
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

    // 💾 Save action
    protected function getSaveAction(): Action
    {
        return Action::make('save')
            ->label('Simpan Perubahan')
            ->icon('heroicon-o-check-circle')
            ->color('primary')
            ->action(function () {
                $this->record->save();
                $this->notifyUser('success', 'Data berhasil disimpan.');
            });
    }

    // 🗑️ Delete action
    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Hapus Data')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Hapus Data ImutData')
            ->modalDescription('Menghapus ImutData ini akan memengaruhi data terkait. Data tidak akan dihapus secara permanen, melainkan dinonaktifkan (soft delete) dan masih dapat dipulihkan kembali jika diperlukan.')
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalIconColor('danger')
            ->modalSubmitActionLabel('Ya, Hapus')
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Data Dinonaktifkan')
                    ->body('ImutData dan data terkait telah dinonaktifkan (soft delete). Anda masih dapat memulihkannya melalui filter di menu list IMUT Data.')
            );
    }
}
