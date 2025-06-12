<?php

namespace App\Livewire;

use App\Filament\Resources\ImutDataResource;
use App\Models\ImutData;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ImutDataOverview extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $data = [];

    public ?ImutData $imutData = null;

    public function getTitle(): string
    {
        return 'Ikhtisar Data IMUT';
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Daftar Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->imutData?->slug]) => $this->imutData?->title ?? 'Detail',
            'Ikhtisar',
        ];
    }

    public function mount(): void
    {
        $slug = request()->query('record');

        if (! $slug) {
            abort(404, 'Slug Data IMUT tidak ditemukan.');
        }

        $imutData = ImutData::with(['profiles', 'categories'])->where('slug', $slug)->first();

        if (! $imutData) {
            abort(404, 'Data IMUT tidak valid.');
        }

        $this->imutData = $imutData;

        $this->data = [
            'imutDataId' => $imutData->id,
            'title' => $imutData->title,
            'status' => $imutData->status,
            'kategori' => $imutData->categories?->name ?? '-',
            'jumlah_profil' => $imutData->profiles->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->imutData
                ->unitKerja()
                ->withPivot(['assigned_by', 'assigned_at'])
            )
            ->columns([
                TextColumn::make('unit_name')
                    ->label('Nama Unit Kerja'),
            ]);
    }

    public function render()
    {
        return view('livewire.imut-data-overview');
    }
}
