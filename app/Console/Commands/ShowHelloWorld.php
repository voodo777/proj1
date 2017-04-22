<?php

namespace App\Console\Commands;

use App\Community;
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
        /** @var Community[] $awww */
        $awww = Community::where('status', '=', Community::STATUS_AWAITING)->get();
        foreach ($awww as $community) {
            $publicInfo = $this->getInfoFromContactAboutPublic($community->vk)->response[0];
            $community->name = $publicInfo->name;
            $community->status = Community::STATUS_PARSED;
            $community->save();
//            usleep(300000);
        }
    }

    public function getInfoFromContactAboutPublic($publicId)
    {
        $result = file_get_contents('https://api.vk.com/method/groups.getById?group_ids=' . $publicId);

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

        /** @var User $user */
        $user = User::find(1);
        dd($user->getInfoFromContact());
    }

}
