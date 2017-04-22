<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    public function getInfoFromContact()
    {
        $result = file_get_contents('https://api.vk.com/method/users.get?user_id=' . $this->vk);

        return json_decode($result);
    }
}
