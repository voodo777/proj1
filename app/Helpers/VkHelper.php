<?php

namespace App\Helpers;

class VkHelper {

    const
        VK_API_URL          = 'https://api.vk.com/method/',
        LINK_SHRINKER_URL   = 'https://vk.com/cc',
        /** link to get access token with code  */
        LINK_AUTH_CODE   = 'https://oauth.vk.com/access_token';

    const
        PERM_NOTIFY = 1, //Пользователь разрешил отправлять ему уведомления.
        PERM_FRIENDS = 2, //Доступ к друзьям.
        PERM_PHOTO = 4, //Доступ к фотографиям.
        PERM_AUDIO = 8, //Доступ к аудиозаписям.
        PERM_VIDEO = 16, //Доступ к видеозаписям.
        PERM_APPS = 32, //Доступ к предложениям.
        PERM_QUESTIONS = 64, //Доступ к вопросам.
        PERM_WIKI = 128, //Доступ к wiki-страницам.
        PERM_LEFTMENU = 256, //Добавление ссылки на приложение в меню слева.
        PERM_QUICKPUBLISH = 512, //Добавление ссылки на приложение для быстрой публикации на стенах пользователей.
        PERM_STATUS = 1024, //Доступ к статусам пользователя,
        PERM_NOTES = 2048, //Доступ заметкам пользователя,
        PERM_MSG_EXTENDED = 4096, //(для Desktop-приложений) Доступ к расширенным методам работы с сообщениями,
        PERM_WALL = 8192, //Доступ к обычным и расширенным методам работы со стеной,
        PERM_ADS = 32768, //Доступ к функциям для работы с рекламным кабинетом,
        PERM_OFFLINE = 65536, //Оффлайн-доступ
        PERM_DOCS = 131072, //Доступ к документам пользователя,
        PERM_GROUPS = 262144, //Доступ к группам пользователя,
        PERM_NOTIFY_ANSWER = 524288, //Доступ к оповещениям об ответах пользователю,
        PERM_GROUP_STATS = 1048576; //Доступ к статистике групп и приложений пользователя, администратором которых он является,


    const ANTIGATE_KEY = '8bafeccc1dc81da930cb3e07f8868922';
    const FALSE_COUNTER = 3;
    const CURRENT_VK_API_VER = '5.63';

    const PAUSE = 1;

    public static $tries = 0;

    /** @var int максимальное время ожидания ответа, сек */
    public static $timeout = 25;

    public static $open_methods = [
        'wall.get'          => true,
        'groups.getById'    => true,
        'wall.getById'      => true,
        'photos.getAlbums'  => true,
        'groups.getMembers' => true,
        'wall.getComments'  => true,
        'users.get'         => true,
        'board.getTopics'   => true,
        'board.getComments' => true,
        'wall.search'       => true,
        'likes.getList'     => true,
        'friends.get'       => true,
        'groups.isMember'   => true,
    ];

    /**
     * @param $method - метод api
     * @array $request_params - параметры запроса
     * @param $request_params
     * @param bool|int $throw_exc_on_errors - выбрасывать ли исключние при ошибке запроса(нет - вернет объект-ответ)
     * @param bool $recognizeCaptcha
     *
     * @throws AccessTokenIsDead
     * @throws Exception
     * @return array|mixed
     */
    public function api($method, $request_params, $recognizeCaptcha = true) {
        if (!isset($request_params['v'])) {
            $request_params['v'] = self::CURRENT_VK_API_VER;
        }

        $url = self::VK_API_URL . $method;
        $a = VkHelper::curl_request($url, $request_params);

        $res = json_decode($a);

        if (!$res) {
//                AuditUtility::CreateEvent('exportErrors', 'articleQueue', 666, 'cant deJSON response: ' . $a);
            return array();
        }
        if (isset($res->error)) {
            throw new Exception('Error : ' . $res->error->error_msg . ' on params ' . json_encode($request_params));
        }

        return $res->response;
    }

    public function curl_request($url, $arr_of_fields, $headers = '', $uagent = '', $build_http_query = true) {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, static::$timeout);

        if (is_array($headers)) { // если заданы какие-то заголовки для браузера
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (!empty($uagent)) { // если задан UserAgent
            curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'socialboard.ru');
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if (is_array($arr_of_fields)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $build_http_query ? http_build_query($arr_of_fields) : $arr_of_fields);
        } else {
            return false;
        }

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'error in curl: ' . curl_error($ch);
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @param $message
     * @param $receiver_vk_ids
     * @param null $token
     */
    public function send_alert($message, $receiver_vk_ids, $token = null) {
        if (!is_array($receiver_vk_ids)) {
            $receiver_vk_ids = array($receiver_vk_ids);
        }
        foreach ($receiver_vk_ids as $vk_id) {
            $params = array(
                'message'      => $message . ' ' . md5(time()),
                'access_token' => $token ? : self::ALERT_TOKEN,
            );
            if (strpos($vk_id, self::CHAT_PREFIX) === false) {
                $params['uid'] = $vk_id;
            } else {
                $params['chat_id'] = trim($vk_id, self::CHAT_PREFIX);
            }

            try {
                VkHelper::api_request('messages.send', $params);
            } catch (Exception $e) {
            }
            sleep(self::PAUSE);
        }
    }

    public function captcha($url) {
        //не требующие пока изменений настройки
        $domain = "antigate.com";
        $rtimeout = 5;
        $mtimeout = 120;
        $is_phrase = 0;
        $is_regsense = 0;
        $is_numeric = 0;
        $min_len = 0;
        $max_len = 0;
        $is_russian = 1;

        $try_counter = 0;
        while (true) {
            $try_counter++;
            if ($try_counter > self::FALSE_COUNTER)
                return false;
            $jp = file_get_contents($url);
            $path = 'capcha_' . md5($jp). '.jpg';
            file_put_contents($path, $jp);


            if (!file_exists($path)) {
                if (self::TESTING) echo "file $path not found\n";

                return false;
            }
            $postdata = array(
                'method'   => 'post',
                'key'      => self::ANTIGATE_KEY,
                'file'     => '@' . $path,
                'phrase'   => $is_phrase,
                'regsense' => $is_regsense,
                'numeric'  => $is_numeric,
                'min_len'  => $min_len,
                'max_len'  => $max_len,

            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://$domain/in.php");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                if (self::TESTING) echo "CURL returned error: " . curl_error($ch) . "\n";

                return false;
            }
            curl_close($ch);
            if (strpos($result, "ERROR") !== false) {
                if (self::TESTING) echo "server returned error: $result\n";

                return false;
            } else {
                $ex = explode("|", $result);
                $captcha_id = $ex[1];
                if (self::TESTING) echo "captcha sent, got captcha ID $captcha_id\n";
                $waittime = 0;
                if (self::TESTING) echo "waiting for $rtimeout seconds\n";
                sleep($rtimeout);
                while (true) {
                    $result = file_get_contents("http://$domain/res.php?key=" . self::ANTIGATE_KEY . '&action=get&id=' . $captcha_id);
                    if (strpos($result, 'ERROR') !== false) {
                        if (self::TESTING) echo "server returned error: $result\n";
                        continue(2);
                    }
                    if ($result == "CAPCHA_NOT_READY") {
                        if (self::TESTING) echo "captcha is not ready yet\n";
                        $waittime += $rtimeout;
                        if ($waittime > $mtimeout) {
                            if (self::TESTING) echo "timelimit ($mtimeout) hit\n";
                            continue(2);
                        }
                        if (self::TESTING) echo "waiting for $rtimeout seconds\n";
                        sleep($rtimeout);
                    } else {
                        $ex = explode('|', $result);
                        unlink($path);
                        if (trim($ex[0]) == 'OK') return trim($ex[1]);
                    }
                }

                return false;
            }
        }

        return false;
    }

    /**
     * получить статистику постов
     *
     * @param $externalIds
     * @param DateTime $from
     * @param DateTime $to
     * @param bool $reverse
     *
     * @return array
     */
    public static function getPostStat($externalIds, DateTime $from, DateTime $to, $reverse = true) {
        if (!is_array($externalIds)) {
            $externalIds = [$externalIds];
        }

        $params = [
            'v'            => '5.14',
            'date_from'    => $from->format('Y-m-d'),
            'date_to'      => $to->format('Y-m-d'),
            'access_token' => self::APP_TOKEN
        ];
        $res = [];
        foreach ($externalIds as $externalId) {
            $externalId = trim($externalId, '-');
            if (strpos($externalId, '_') === false)
                continue;

            list($group_id, $post_id) = explode('_', $externalId);
            $params['post_id'] = $post_id;
            $params['group_id'] = $group_id;

            $data = VkHelper::api_request('stats.getPostStats', $params, false);
            if (!$data) {
                continue;
            }
            $res[$externalId] = $reverse ? array_reverse($data) : $data;
            sleep(self::PAUSE);
        }

        return $res;
    }

    /**
     * Сократит ссылку с помощью vk.cc
     *
     * @param string $url ссылка для сокращения
     * @param string $cookies ссылка для сокращения
     *
     * @return bool|string новая ссылка
     */
    public static function urlShrinker($url, $cookies) {
        $params = [
            'act'  => 'shorten',
            'al'   => 1,
            'link' => $url
        ];

        $res = VkHelper::connect(self::LINK_SHRINKER_URL, $cookies, $params);
        return preg_match('/(https?:.*)/', $res, $matches) ? $matches[1] : false;
    }

    public function makeAuthLink(array $scopes = [])
    {
        $scopes = $scopes ?: [
            'stats',
            'groups',
            'offline',
        ];


        return 'https://oauth.vk.com/authorize?' .
            http_build_query([
                'client_id'=> env('VK_APP_ID'),
                'scope'=> $scopes,
                'redirect_uri' => $this->makeRedirectUrl(),
                'display' => 'page',
                'response_type' => 'code',
            ]);
    }

    public function getTokenFromCode(string $code)
    {
        $res = $this->curl_request(self::LINK_AUTH_CODE, [
            'client_id' => env('VK_APP_ID'),
            'client_secret' => env('VK_APP_SECRET'),
            'redirect_uri' => $this->makeRedirectUrl(),
            'code' => $code,
        ]);

        return json_decode($res);
    }

    private function makeRedirectUrl(): string
    {
        return  env('APP_URL') . '/vk_redirect';
    }
}