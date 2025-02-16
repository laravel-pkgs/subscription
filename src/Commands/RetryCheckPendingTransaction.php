<?php

namespace IICN\Subscription\Commands;


use IICN\Subscription\Constants\AgentType;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Appstore;
use IICN\Subscription\Services\Purchase\AppStoreSubscription;
use IICN\Subscription\Services\Purchase\Playstore;
use IICN\Subscription\Services\Purchase\PlayStoreSubscription;
use IICN\Subscription\Services\Purchase\Purchase;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RetryCheckPendingTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:check-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactions = SubscriptionTransaction::query()->where('created_at', '>', now()->addDays(-2))
            ->whereIn('status', [Status::PENDING, Status::INIT])->get();

        foreach ($transactions as $transaction) {
            try {


                if ($transaction->agent_type == AgentType::APP_STORE) {

                    if (Str::contains($transaction->sku_code, 'subscription')) {
                        $playStore = new Purchase(new AppStoreSubscription());
                    } else {
                        $playstore = new Purchase(new Appstore());
                    }


                } elseif($transaction->agent_type == AgentType::GOOGLE_PLAY) {

                    if (Str::contains($transaction->sku_code, 'subscription')) {
                        $playStore = new Purchase(new PlayStoreSubscription());
                    } else {
                        $playstore = new Purchase(new Playstore());
                    }

                } else {
                    return ;
                }

                $playstore->retry($transaction);
                $this->info("apply transaction ID: {$transaction}");
            } catch (\Exception $e) {
                $this->error("error in transaction ID: {$transaction}");
            }
        }
    }
}
