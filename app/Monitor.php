<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Community
 * @package App
 * @property int id
 * @property string addTime
 * @property int status
 * @property int crossBefore
 * @property string crossBeforeTime
 * @property int crossAfter
 * @property string crossAfterTime
 * @property int gidIn
 * @property string countIn
 * @property int gidOut
 * @property string countOut
 * @property string postTime
 * @property string created_at
 * @property string updated_at
 */
class Monitor extends Model
{
    const STATUS_AWAITING = 0;
    const STATUS_IN_WORK = 1;
    const STATUS_PARSED = 2;
    const STATUS_DELETED = 3;
    const STATUS_ERROR = 4;
}

//Таблица "Monitors"
//* id: 				id монитора (внутренний)
//* addTime: 			Время добавления монитора
//* status:	 		Статус монитора (в ожидании, в процессе, завершённый, удалённый, ошибка)
//* crossBefore: 		Пересекаемость ДО
//* crossBeforeTime: 	Время проверки пересекаемости До
//* crossAfter:		Пересекаемость ПОСЛЕ
//* crossAfterTime: 	Время проверки пересекаемости После
//
//* gidIn:			id паблика "что рекламируем"
//* countIn:			количество подписчиков в паблике "что рекламируем" на момент заведения монитора
//* gidOut:			id паблика "где рекламируем"
//* countOut:			количество подписчиков в паблике "где рекламируем" на момент заведения монитора
//* postTime:			время выхода поста
//
//* Номер кошелька, валюта, стоимость поста, подтверждение оплаты, скрин поста, время в топе, время до удаления, точное время выхода поста, текст поста, совпадение вышедшего поста с эталонным, стоимость подписчика