<?php

namespace App\Console\Commands;

use App\Community;
use App\Helpers\VkHelper;
use App\User;
use Illuminate\Console\Command;

class ShowHelloWorld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:hello';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'Hello World!'.PHP_EOL;
        echo date('l jS \of F Y h:i:s A').PHP_EOL;


//        $vk = new VkHelper();
//        dd($vk->makeAuthLink());
    }

    public function getInfoFromContactAboutPublic($publicId)
    {
        $result = file_get_contents('https://api.vk.com/method/groups.getById?group_ids=' . $publicId);


        echo 1;
        return json_decode($result);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function howToCreateNewRecord()
    {
        $user = new User();
        $user->name = 'sdf';
        $user->email = 'sdf';
        $user->password = 'ssdasdf';
        $user->remember_token = 'ssdasdf';
        $user->vk = 123;
        $user->save();
    }

    public function showHowStoreLogicInModel() {

        $vk = new VkHelper();
        $list = $vk->api('groups.getById', [
            'group_ids' => [1,2,3,4],
        ]);
        dd($list);
    }

}
