<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Routing\UrlRoutable;

class BackupNotifiable
{
    use Notifiable;

    protected UrlRoutable $user;

    public function __construct(UrlRoutable $user)
    {
        $this->user = $user;
    }

    public function routeNotificationForDatabase()
    {
        return $this->user;
    }
}