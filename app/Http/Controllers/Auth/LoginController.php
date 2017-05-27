<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\VkHelper;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        $vk = new VkHelper();

        return view('auth.login', [
            'redirectUrl' => $vk->makeAuthLink(),
        ]);
    }

    public function vkRedirect(Request $request)
    {
        $vk = new VkHelper();
        $code = $request->get('code');

        $res = $vk->getTokenFromCode($code);
        if (!isset($res->access_token)) {
            redirect('/login');
        }

        $usersInfo = $vk->api('users.get', [
            'fields' => 'photo_50',
            'access_token' => $res->access_token,
        ]);
        $userInfo = $usersInfo[0];

        $user = User::where(['vk' => $userInfo->id])->first();
        if ($user === null) {
            $user = new User();
            $user->vk = $userInfo->id;
        }
        $user->name = $userInfo->first_name . ' ' . $userInfo->last_name;
        $user->photo = $userInfo->photo_50;
        $user->access_token = $res->access_token;
        $user->save();

        Auth::login($user);

        return redirect('/communities');
    }
}
