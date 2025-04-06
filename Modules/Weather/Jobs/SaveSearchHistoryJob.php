<?php

namespace Modules\Weather\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Weather\Entities\SearchHistory;

class SaveSearchHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected string $city;
    protected string $country;
    protected string $region;
    protected string $userId;
    public int $tries = 3;
    public int $timeout = 60;
    public function __construct(
        string $city,
        string $country,
        string $region,
        string $userId
    ) {
        $this->city = $city;
        $this->country = $country;
        $this->region = $region;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $countHistory = SearchHistory::where('user_id', $this->userId)->count();
        if ($countHistory >= 5) {
            $d=SearchHistory::where('user_id', $this->userId)
            ->orderBy('created_at', 'asc')
            ->first();
            $d->delete();
        }
        SearchHistory::firstOrCreate([
            'user_id' => $this->userId,
            'city' => $this->city,
            'country' => $this->country,
            'region' => $this->region,
        ]);
    }
}
