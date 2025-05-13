<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Login extends BaseLogin
{
    use HasCustomLayout;

    protected static string $view = 'vendor.filament-panels.pages.auth.login';

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Ganti pencarian user dari email ke nik
        $user = User::where('nik', $data['nik'])->first();

        // if ($user && is_null($user->password)) {
        //     throw ValidationException::withMessages([
        //         'data.nik' => 'This account was created using social login. Please login with Google.',
        //     ]);
        // }

        if ($user && in_array($user->status, ['inactive', 'suspended'])) {
            // Tentukan isi pesan dan tipe notifikasi berdasarkan status
            $statusMessage = match ($user->status) {
                'inactive' => 'Akun Anda belum diaktifkan. Silakan hubungi administrator.',
                'suspended' => 'Akun Anda sedang ditangguhkan karena pelanggaran atau alasan lain.',
            };

            $notificationType = match ($user->status) {
                'inactive' => 'warning',
                'suspended' => 'danger',
            };

            // Tampilkan notifikasi sesuai status
            Notification::make()
                        ->title('Akses Ditolak')
                        ->body($statusMessage)
                ->$notificationType()
                    ->persistent()
                    ->send();

            throw ValidationException::withMessages([
                'data.nik' => $statusMessage,
            ]);
        }

        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (!$user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'nik' => '0000.00000',
            'password' => 'adminpassword',
            'remember' => true,
        ]);
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNIKFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNIKFormComponent(): Component
    {
        return TextInput::make('nik')
            ->label(__('NIK'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'nik' => $data['nik'],
            'password' => $data['password'],
        ];
    }
}
