<?php

namespace App\Http\Controllers;

use App\Community;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $communities = Community::all();
        /** @var User $user */
        $user = Auth::user();
        return view('communty', [
            'comms' => $communities,
            'userToken' => $user->access_token,

        ]);
    }

    /**
     * Сохранит новыую запись в communities
     */
    public function save(Request $request)
    {
        $publicId = (int)$request->get('public_id');

        $a = new Community();
        $a->vk = $publicId;
        $a->status = Community::STATUS_AWAITING;
        $a->save();

        return redirect('/communities');
    }

    //Проверка введённого значения - является ли это пабликом, и что это вообще за паблик
    public function check(Request $request)
    {
        $publicLink = $request->get('public_link');
        return $publicLink;
    }

    public function addmonitor (Request $request) {
        //Сюда попадают следующие данные:
//        gidIn
//        nameIn
//        countIn
//        photoIn
//        typeIn
//        isclosedIn
//        verifiedIn
//        И тоже самое для Out.
//        Нужно провести проверки:
//        Для всех требуется наличие (не равно пустоте)
//        gid - набор цифр (соответствие Integer)
//        name - string
//        count - больше или равно нулю
//        photoIn - присутствует подстрока http
//        type - string (page, group или event)
//        isclosed - int (0, 1 или 2, public - только 0, event - 0 или 1, group - любое)
//        verifiedIn - int, 0 или 1

        $err=false;
        $gidIn = (int)$request->get('gidIn');$gidInTemp = $request->get('gidIn');
        $gidOut = (int)$request->get('gidOut');$gidOutTemp = $request->get('gidOut');
        $nameIn = (string)$request->get('nameIn');
        $nameOut = (string)$request->get('nameOut');
        $countIn = (int)$request->get('countIn');$countInTemp = $request->get('countIn');
        $countOut = (int)$request->get('countOut');$countOutTemp = $request->get('countOut');
        $photoIn = (string)$request->get('photoIn');
        $photoOut = (string)$request->get('photoOut');
        $typeIn = (string)$request->get('typeIn');
        $typeOut = (string)$request->get('typeOut');
        $isClosedIn = (int)$request->get('isClosedIn');$isClosedInTemp = $request->get('isClosedIn');
        $isClosedOut = (int)$request->get('isClosedOut');$isClosedOutTemp = $request->get('isClosedOut');
        $verifiedIn = $request->get('verifiedIn');
        $verifiedOut = $request->get('verifiedOut');

        if ($gidIn<1 || strlen($gidIn)!=strlen($gidInTemp)) {
            $err=true;
            $returned="Некорректный ID рекламируемого сообщества";
        }
        if ($gidOut<1 || strlen($gidOut)!=strlen($gidOutTemp)) {
            $err=true;
            $returned="Некорректный ID сообщества, где должна выйти реклама";
        }
        if (strlen($nameIn)<1 || strlen($nameIn)>100) {
            $err=true;
            $returned="Некорректное название рекламируемого сообщества";
        }
        if (strlen($nameOut)<1 || strlen($nameOut)>100) {
            $err=true;
            $returned="Некорректное название сообщества, где должна выйти реклама";
        }
        if (($countIn<1 && $countInTemp!='0') || strlen($countIn)!=strlen($countInTemp)) {
            $err=true;
            $returned="Некорректное количество подписчиков рекламируемого сообщества";
        }
        if (($countOut<1 && $countOutTemp!='0') || strlen($countOut)!=strlen($countOutTemp)) {
            $err=true;
            $returned="Некорректное количество подписчиков сообщества, где должна выйти реклама";
        }
        $photoInHttpSearch=strripos('3'.substr($photoIn, 0, 5), 'http');
        $photoOutHttpSearch=strripos('3'.substr($photoOut, 0, 5), 'http');
        if ($photoInHttpSearch!=1) {
            $err=true;
            $returned="Некорректная ссылка на аватар рекламируемого сообщества";
        }
        if ($photoOutHttpSearch!=1) {
            $err=true;
            $returned="Некорректная ссылка на аватар сообщества, где должна выйти реклама";
        }
        if ($typeIn!='page' && $typeIn!='group' && $typeIn!='event') {
            $err=true;
            $returned="Некорректный тип рекламируемого сообщества";
        }
        if ($typeOut!='page' && $typeOut!='group' && $typeOut!='event') {
            $err=true;
            $returned="Некорректный тип сообщества, где должна выйти реклама";
        }

        if (($isClosedIn==0 && $isClosedInTemp!="0") || strlen($isClosedInTemp)!=1) {
            $isClosedIn=-1;
        }
        if (($isClosedOut==0 && $isClosedOutTemp!="0") || strlen($isClosedOutTemp)!=1) {
            $isClosedOut=-1;
        }
        if (($typeIn=="page" && $isClosedIn!=0) || (($typeIn=="group" || $typeIn=="event") && ($isClosedIn!=0 && $isClosedIn!=1))) {
            $err=true;
            $returned="Некорректный тип закрытости рекламируемого сообщества";
        }
        if (($typeOut=="page" && $isClosedOut!=0) || (($typeOut=="group" || $typeOut=="event") && ($isClosedOut!=0 && $isClosedOut!=1))) {
            $err=true;
            $returned="Некорректный тип закрытости сообщества, где должна выйти реклама";
        }
        if ($verifiedIn!='0' && $verifiedIn!='1') {
            $err=true;
            $returned="Некорректный статус верификации рекламируемого сообщества";
        }
        if ($verifiedOut!='0' && $verifiedOut!='1') {
            $err=true;
            $returned="Некорректный статус верификации сообщества, где должна выйти реклама";
        }


//        ЕЩЁ ПОЛУЧИТЬ СЮДА ДАТУ
//        СДЕЛАТЬ ЕЙ ПРОВЕРКУ
//        И МОЖНО ПИСАТЬ В БД




        if (!$err) {
            $returned = $verifiedIn . "  " . $verifiedOut;
        }
        return $returned;
    }



}