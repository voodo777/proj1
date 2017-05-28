<?php

namespace App\Http\Controllers;

use App\Community;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Monitor;
use App\History;
use App\commIn;
use App\commOut;
use DB;

class CommunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $communities = Community::all();
        $monitors = Monitor::all();
        /** @var User $user */
        $user = Auth::user();
        return view('communty', [
            'comms' => $communities,
            'monitors' => $monitors,
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
        $postTime = (int)$request->get('postTime');$postTimeTemp = $request->get('postTime');

        if ($gidIn<1 || strlen($gidIn)!=strlen($gidInTemp)) {$err=true;$returned="Некорректный ID рекламируемого сообщества";}
        if ($gidOut<1 || strlen($gidOut)!=strlen($gidOutTemp)) {$err=true;$returned="Некорректный ID сообщества, где должна выйти реклама";}
        if (strlen($nameIn)<1 || strlen($nameIn)>100) {$err=true;$returned="Некорректное название рекламируемого сообщества";}
        if (strlen($nameOut)<1 || strlen($nameOut)>100) {$err=true;$returned="Некорректное название сообщества, где должна выйти реклама";}
        if (($countIn<1 && $countInTemp!='0') || strlen($countIn)!=strlen($countInTemp)) {$err=true;$returned="Некорректное количество подписчиков рекламируемого сообщества";}
        if (($countOut<1 && $countOutTemp!='0') || strlen($countOut)!=strlen($countOutTemp)) {$err=true;$returned="Некорректное количество подписчиков сообщества, где должна выйти реклама";}
        $photoInHttpSearch=strripos('3'.substr($photoIn, 0, 5), 'http');$photoOutHttpSearch=strripos('3'.substr($photoOut, 0, 5), 'http');
        if ($photoInHttpSearch!=1) {$err=true;$returned="Некорректная ссылка на аватар рекламируемого сообщества";}
        if ($photoOutHttpSearch!=1) {$err=true;$returned="Некорректная ссылка на аватар сообщества, где должна выйти реклама";}
        if ($typeIn!='page' && $typeIn!='group' && $typeIn!='event') {$err=true;$returned="Некорректный тип рекламируемого сообщества";}
        if ($typeOut!='page' && $typeOut!='group' && $typeOut!='event') {$err=true;$returned="Некорректный тип сообщества, где должна выйти реклама";}
        if (($isClosedIn==0 && $isClosedInTemp!="0") || strlen($isClosedInTemp)!=1) {$isClosedIn=-1;}
        if (($isClosedOut==0 && $isClosedOutTemp!="0") || strlen($isClosedOutTemp)!=1) {$isClosedOut=-1;}
        if (($typeIn=="page" && $isClosedIn!=0) || (($typeIn=="group" || $typeIn=="event") && ($isClosedIn!=0 && $isClosedIn!=1))) {$err=true;$returned="Некорректный тип закрытости рекламируемого сообщества";}
        if (($typeOut=="page" && $isClosedOut!=0) || (($typeOut=="group" || $typeOut=="event") && ($isClosedOut!=0 && $isClosedOut!=1))) {$err=true;$returned="Некорректный тип закрытости сообщества, где должна выйти реклама";}
        if ($verifiedIn!='0' && $verifiedIn!='1') {$err=true;$returned="Некорректный статус верификации рекламируемого сообщества";}
        if ($verifiedOut!='0' && $verifiedOut!='1') {$err=true;$returned="Некорректный статус верификации сообщества, где должна выйти реклама";}
        if ($postTime<time() || strlen($postTime)!=strlen($postTimeTemp)) {$err=true;$returned="Некорректное время выхода поста";}

        if ($err) {return $returned;}
        else {
            //Если проверки не выявили ошибок в передаваемых запросом данных, пишем данные в бд

            //Добавляем монитор.
            //Сейчас делаем проверку на наличие такого же монитора ($gidIn, $gidOut) ближе часа от заводимого
            //При дальнейшем расширении сервиса на разных юзеров, проверку перепиливаем на конкретного юзера (а точнее, на владельца кампании, где создаётся этот монитор)

            $currentPostTimes=DB::select('select postTime from monitors where gidIn = ?', [$gidIn]);
            $err=false;
            foreach ($currentPostTimes as $a) {
                foreach ($a as $b) {
//                    echo $b.'</br>';echo abs(($b-$postTime)/(60*60)).'</br>';echo '</br>';
                    //Если монитор заводится ближе часа к уже существующему
                    if (abs(($b-$postTime)/(60*60))<1) {
                        $err=true;
                        break;
                    }
                }
                if ($err) {break;}
            }
//==========УБРАТЬ==========
//            $err=false;
//==========УБРАТЬ==========
            if ($err) {
                return 'Нельзя создавать монитор ближе часа к уже существующему монитору с аналогичными сообществами';
            }
            else {
                $mon = new Monitor();
                $mon->status = Monitor::STATUS_AWAITING;
                $mon->gidIn = $gidIn;
                $mon->gidOut = $gidOut;
                $mon->countIn = $countIn;
                $mon->countOut = $countOut;
                $mon->postTime = $postTime;
                $mon->save();

                CommunityController::updateHistory ($gidIn, $nameIn, $photoIn, $verifiedIn, $typeIn, $isClosedIn);
                CommunityController::updateHistory ($gidOut, $nameOut, $photoOut, $verifiedOut, $typeOut, $isClosedOut);
                CommunityController::updateCommIn ($gidIn, $postTime);
                CommunityController::updateCommOut ($gidOut);

                return 'Монитор успешно добавлен';
            }
        }

    }

    function updateCommOut ($gidOut) {
        $currentCommOut=DB::select('select id from commOut where gid = ?', [$gidOut]);
        if (count($currentCommOut)==0) {
            $a = new commOut();
            $a->gid = $gidOut;
            $a->save();
        }
    }

    function updateCommIn ($gidIn, $postTime) {
        $currentCommIn=DB::select('select newestPostDate, archiveStatus from commIn where gid = ?', [$gidIn]);
        if (count($currentCommIn)==0) {
            $a = new commIn();
            $a->gid = $gidIn;
            $a->archiveStatus = false;
            $a->newestPostDate = $postTime;
            $a->save();
        }
        else {
            $upd=false;
            if ($currentCommIn[0]->archiveStatus) {
                DB::update('update commIn set archiveStatus = FALSE WHERE gid=?', [$gidIn]);
                $upd=true;
            }
            if($currentCommIn[0]->newestPostDate < $postTime) {
                DB::update('update commIn set newestPostDate = ? WHERE gid=?', [$postTime, $gidIn]);
                $upd=true;
            }
            if ($upd) {
                DB::update('update commIn set updated_at = ? WHERE gid=?', [date('Y-m-d H:i:s'), $gidIn]);
            }
        }
    }

    function updateHistory ($gid, $name, $photo, $verified, $type, $isClosed) {
        //history - ищем запись под gidIn и gidOut, где statusActual = true. Если нашлось - сверяем все параметры с уже имеющимися.
        //Если что-то не совпадает, меняем в найденной записи statusActual на false, и создаём новую запись с принятыми параметрами и statusActual = true
        $currentHistory=DB::select('select name, photo, verified, type, is_closed, id from history where gid = ? AND statusActual = ?', [$gid, true]);
        if (count($currentHistory)!=0) {
            if ($currentHistory[0]->name != $name || $currentHistory[0]->photo != $photo || $currentHistory[0]->verified != $verified || $currentHistory[0]->type != $type || $currentHistory[0]->is_closed != $isClosed) {
                //Сначала меняем statusActual этой записи на false и устанавливаем дату обновления на актуальную
                DB::update('update history set statusActual = FALSE WHERE id = ?', [$currentHistory[0]->id]);
                DB::update("update history set updated_at = ? WHERE id = ?", [date('Y-m-d H:i:s'), $currentHistory[0]->id]);
                //Потом делаем новую запись
                CommunityController::saveHistory ($gid, $name, $photo, $verified, $type, $isClosed);
            }
        }
        else {CommunityController::saveHistory ($gid, $name, $photo, $verified, $type, $isClosed);}//Если записей с таким пабликом не существует, делаем новую
    }

    function saveHistory ($gid, $name, $photo, $verified, $type, $isClosed) {
        $his = new History();
        $his->statusActual = true;
        $his->gid = $gid;
        $his->name = $name;
        $his->photo = $photo;
        $his->verified = $verified;
        $his->type = $type;
        $his->is_closed = $isClosed;
        $his->save();
    }

}