<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Community
 * @package App
 * @property int id
 * @property int vk
 * @property int status
 * @property string name
 * @property string created_at
 * @property string updated_at
 */
class Community extends Model
{
    const STATUS_AWAITING = 0;
    const STATUS_IN_WORK = 1;
    const STATUS_PARSED = 2;



}
