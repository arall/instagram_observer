<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use InstagramAPI\Instagram;
use App\View;

class Observe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'igspy:observe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start monitoring the stories viewers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ig = new Instagram(false);
        try {
            $loginResponse = $ig->login(env('IG_USERNAME'), env('IG_PASSWORD'));
            if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
                $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();
                // The "STDIN" lets you paste the code via terminal for testing.
                // You should replace this line with the logic you want.
                // The verification code will be sent by Instagram via SMS.
                $verificationCode = trim(fgets(STDIN));
                $ig->finishTwoFactorLogin(env('IG_USERNAME'), env('IG_PASSWORD'), $twoFactorIdentifier, $verificationCode);

                $this->comment('Logged in!');
            }
        } catch (\Exception $e) {
            echo 'Something went wrong: '.$e->getMessage()."\n";
            die();
        }

        $userId = $ig->people->getUserIdForName(env('IG_USERNAME'));

        while (true) {
            $this->comment('Grabbing user story feeed...');
            $feed = $ig->story->getUserStoryFeed($userId);
            foreach ($feed->getReel()->getItems() as $item) {
                $pk = $item->getPk();
                $this->info($pk);
                $nextMaxId = 0;
                do {
                    $viewers = $ig->story->getStoryItemViewers($pk, $nextMaxId);
                    $nextMaxId = $viewers->getNextMaxId();
                    $users = $viewers->getUsers();
                    foreach ($users as $user) {
                        $username = $user->getUsername();
                        $this->line($username);
                        View::firstOrCreate([
                            'pk' => $pk,
                            'username' => $username,
                        ]);
                    }
                } while ($nextMaxId);
            }
            $this->comment('Sleeping...');
            sleep(60);
        }
    }
}
