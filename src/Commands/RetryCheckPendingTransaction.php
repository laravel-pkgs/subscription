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
        $transactions = SubscriptionTransaction::query()->where('created_at', '>', now()->addDays(-24))
            ->whereIn('status', [Status::PENDING, Status::INIT])->get();

        foreach ($transactions as $transaction) {
            try {

                if ($transaction->agent_type == AgentType::APP_STORE) {

                    $appStore = Str::contains($transaction->product_id, 'subscription') ? new Purchase(new AppStoreSubscription()) : new Purchase(new Appstore());
                    $appStore->retry($transaction);

                } elseif($transaction->agent_type == AgentType::GOOGLE_PLAY) {
                    $playStore = Str::contains($transaction->product_id, 'subscription') ? new Purchase(new PlayStoreSubscription()) : new Purchase(new Playstore());
                    $playStore->retry($transaction);

                } else {
                    return ;
                }

                $this->info("apply transaction ID: {$transaction->id}");
            } catch (\Exception $e) {
                $this->error("error in transaction ID: {$transaction->id} error is {$e->getMessage()}");
            }
        }
    }
}
