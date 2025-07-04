<?php

namespace App\Filament\Resources\UserResource\Schema;

use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;

class UserResourceInfolist
{
    public static function infolist(): array
    {
        return [
            // Section untuk informasi pribadi pengguna
            InfolistSection::make(__('filament-forms::users.infolist.personal_info_title'))
                ->columns(3)
                ->schema([
                    // Kolom pertama: Avatar Pengguna
                    ImageEntry::make('avatar_url')
                        ->label('')
                        ->circular()
                        ->size(120)
                        ->grow(false)
                        ->getStateUsing(fn($record) => $record->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($record->name))
                        ->columnSpan(1),

                    // Kolom kedua: Nama, NIK, Tempat Lahir, Tanggal Lahir
                    Group::make([
                        TextEntry::make('name')
                            ->label(__('filament-forms::users.fields.name'))
                            ->weight(FontWeight::Bold),
                        TextEntry::make('nik')
                            ->label(__('filament-forms::users.fields.nik'))
                            ->icon('heroicon-o-finger-print')
                            ->copyable(),
                        TextEntry::make('place_of_birth')
                            ->label(__('filament-forms::users.fields.place_of_birth'))
                            ->icon('heroicon-m-map-pin'),
                        TextEntry::make('date_of_birth')
                            ->label(__('filament-forms::users.fields.date_of_birth'))
                            ->dateTime('d M Y')
                            ->icon('heroicon-o-calendar'),
                    ])->columnSpan(1),

                    // Kolom ketiga: Gender, Status Pengguna, dan Posisi
                    Group::make([
                        TextEntry::make('gender')
                            ->label(__('filament-forms::users.fields.gender'))
                            ->icon('heroicon-s-user'),
                        TextEntry::make('status')
                            ->label(__('filament-forms::users.fields.status'))
                            ->icon('heroicon-o-check-circle')
                            ->badge()
                            ->color(fn($state) => $state === 'active' ? 'success' : ($state === 'inactive' ? 'danger' : 'gray')),
                        TextEntry::make('roles.name')
                            ->label(__('filament-forms::users.fields.roles'))
                            ->icon('heroicon-o-shield-check')
                            ->badge(),
                        TextEntry::make('position.name')
                            ->label(__('filament-forms::users.fields.position'))
                            ->icon('heroicon-o-briefcase')
                            ->badge(),
                    ])->columnSpan(1),
                ]),

            // Section untuk informasi kontak pengguna
            InfolistSection::make(__('filament-forms::users.infolist.contact_info_title'))
                ->columns(2)
                ->schema([
                    TextEntry::make('phone_number')
                        ->label(__('filament-forms::users.fields.phone_number'))
                        ->icon('heroicon-o-phone'),
                    TextEntry::make('address_ktp')
                        ->label(__('filament-forms::users.fields.address_ktp'))
                        ->icon('heroicon-o-map-pin'),
                ])
                ->visible(fn($record) => filled($record->phone_number) || filled($record->address_ktp)),

            // Section untuk informasi akun dan status pengguna
            InfolistSection::make(__('filament-forms::users.infolist.account_info_title'))
                ->columns(2)
                ->schema([
                    TextEntry::make('email')
                        ->label(__('filament-forms::users.fields.email'))
                        ->icon('heroicon-m-envelope')
                        ->copyable()
                        ->tooltip(__('filament-forms::users.infolist.copy_email')),
                    TextEntry::make('created_at')
                        ->label(__('filament-forms::users.fields.created_at'))
                        ->dateTime('d M Y')
                        ->icon('heroicon-m-calendar'),
                ]),
        ];
    }
}