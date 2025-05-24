<?php

namespace App\Filament\Resources\MediaCustomResource\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateMediaAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateSubFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\DeleteFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\EditCurrentFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaCustom extends ListMedia
{
    protected static string $resource = MediaResource::class;

    public ?int $folder_id = null;
    public ?Folder $folder = null;


    public function getTitle(): string|Htmlable
    {
        return $this->folder->name;
    }

    public function mount(): void
    {
        parent::mount();


        if (!request()->has('folder_id')) {
            abort(404, 'Folder ID is required');
        }

        $folder = Folder::find(request()->get('folder_id'));
        if (!$folder) {
            abort(404, 'Folder ID is required');
        } else {
            if ($folder->is_protected && !session()->has('folder_password')) {
                abort(403, 'You Cannot Access This Folder');
            }
        }

        $this->folder = $folder;
        $this->folder_id = request()->get('folder_id');
        session()->put('folder_id', $this->folder_id);
    }
}
