<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Community
 * @package App
 * @property int id
 * @property boolean statusActual
 * @property int gid
 * @property string name
 * @property string photo
 * @property boolean verified
 * @property string type
 * @property int is_closed
 * @property string created_at
 * @property string updated_at
 */
class History extends Model
{
    protected $table='history';
}

//Таблица "History" - история изменений имени, верифицированности, авы. Запись сюда добавляется в том случае, если эти три поля не совпадают с переданными на этапе добавления монитора
//* id: 				внутренний номер записи
//* statusActual: 	Является ли эта запись актуальной? Для одного паблика может быть только одна (0/1)
//* gid:				айдишник паблика
//* name:				название паблика
//* photo: 			ссылка на аву паблика
//* verified:			верифицирован ли паблик (0/1)
//* type				тип сообщества (group, page, event)
//* is_closed			закрытость сообщества (0, 1, 2)
//* dateIn: 			Дата внесения записи
//* dateOut:			Дата, когда эта запись перестаёт быть "самой свежей"