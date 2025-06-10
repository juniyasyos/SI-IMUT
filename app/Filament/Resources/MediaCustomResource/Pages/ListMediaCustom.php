<?php

namespace App\Filament\Resources\MediaCustomResource\Pages;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateMediaAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateSubFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\DeleteFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\EditCurrentFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia;

class ListMediaCustom extends ListMedia
{
    protected static string $resource = MediaResource::class;

    public ?int $folder_id = null;

    public ?Folder $folder = null;

    public function getTitle(): string|Htmlable
    {
        return $this->folder->name;
    }

    // public function mount(): void
    // {
    //     parent::mount();

    //     if (! request()->has('folder_id')) {
    //         abort(404, 'Folder ID is required');
    //     }

    //     dd(Folder::find(request()->get('folder_id')));
    //     $folder = Folder::find(request()->get('folder_id'));
    //     if (! $folder) {
    //         abort(404, 'Folder ID is required');
    //     } else {
    //         if ($folder->is_protected && ! session()->has('folder_password')) {
    //             abort(403, 'You Cannot Access This Folder');
    //         }
    //     }

    //     $this->folder = $folder;
    //     $this->folder_id = request()->get('folder_id');
    //     session()->put('folder_id', $this->folder_id);
    // }

    public function mount(): void
    {
        parent::mount();

        $this->folderName = request()->route('folderName');
        $this->loadFolder();
        $this->validateFolderAccess();
        session()->put('folder_id', $this->folder->id);
    }

    protected function getHeaderActions(): array
    {
        $folder = $this->folder;

        $isOwner = config('filament-media-manager.allow_user_access', false)
            && ! empty($folder->user_id)
            && $folder->user_id === Auth::id()
            && $folder->user_type === get_class(Auth::user());

        $isAllowed = $isOwner || ! config('filament-media-manager.allow_user_access', false);

        // dd([
        //     'user_id' => Auth::id(),
        //     'user_class' => get_class(Auth::user()),
        //     'folder_user_id' => $folder->user_id,
        //     'folder_user_type' => $folder->user_type,
        //     'is_owner' => $isOwner,
        //     'is_allowed' => $isAllowed,
        //     'can_create_media' => Gate::check('create_media::custom'),
        //     'can_create_sub_folder' => Gate::check('create_sub_folder_media::custom'),
        //     'can_delete_folder' => Gate::check('delete_folder::custom'),
        //     'can_update_folder' => Gate::check('update_folder::custom'),
        // ]);

        return $isAllowed ? [
            CreateMediaAction::make($folder->id)->visible(fn () => Gate::any(['create_media::custom'])),
            // CreateSubFolderAction::make($folder->id)->visible(fn () => Gate::any(['create_sub_folder_media::custom'])), -> bug here, mush be fix
            DeleteFolderAction::make($folder->id)->visible(fn () => Gate::any(['delete_folder::custom'])),
            EditCurrentFolderAction::make($folder->id)->visible(fn () => Gate::any(['update_folder::custom'])),
        ] : [];
    }
}
