<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Community
 * @package App
 * @property int id
 * @property int gid
 * @property boolean archiveStatus
 * @property string newestPostDate
 * @property string created_at
 * @property string updated_at
 */
class commIn  extends Model
{

}
//
//Таблица "commIn" - уникальные рекламируемые паблики, для формирования правого меню кампаний
//* id: 				внутренний номер записи
//* gid:				айдишник паблика
//* archiveStatus: 	используется для скрытия неактуальных кампаний (0/1)
//* newestPostDate:			время выхода самого "нового" поста (для сортировки по актуальности)
//
//* прирост за каждый день, установленные значения самороста и самооттока, установленный способ расчёта стоимости подписчика, устанавливаемое количество лидов за день (для реторно)