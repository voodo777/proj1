<?php

namespace App\Http\Controllers;

use App\Community;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index()
    {
        $communities = Community::all();
        return view('communty', [
            'comms' => $communities
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
}
