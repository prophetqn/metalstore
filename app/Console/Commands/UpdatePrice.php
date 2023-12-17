<?php

namespace App\Console\Commands;

use App\Notifications\PriceUpdateFailed;
use Exception;
use Feed;
use FeedException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Lunar\Hub\Actions\Pricing\UpdatePrices;
use Lunar\Hub\Models\Staff;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\DataTypes\Price as PriceData;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class UpdatePrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:price:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the store\' metal prices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rss = $this->fetchRssData();

        if (! $rss) return;

        $rssPrices = $this->getRssPrices($rss);

        if (empty($rssPrices)) {
            $this->error('Unable to find rss\'s price data.');
            $this->notifyAdmins('Unable to find rss\'s price data.');
            return;
        };

        $rssCurrencyExchangeRate = $this->getRssCurrencyExchangeRate($rss);
        $this->updatePrices($rssPrices, $rssCurrencyExchangeRate);

        if ($this->rssCurrencyChangeExchangeRate($rssCurrencyExchangeRate)) {
            $this->updateRssCurrencyExchangeRate( $rssCurrencyExchangeRate);
        }

        $this->info('Complete to update all prices.');
    }

    /**
     * Fetch all rss items.
     */
    private function fetchRssData(): Feed|false
    {
        $this->info('Fetch the rss data...');

        $url = config('metalshop.rss.url');

        try {
            return Feed::loadRss($url);
        } catch (FeedException $e) {
            report($e);
            $this->error($e->getMessage());
            $this->notifyAdmins($e->getMessage());
        }

        return false;
    }

    /**
     * Get all prices from the fetched rss items
     */
    private function getRssPrices(Feed $rss): array
    {
        $metalConfigs = config('metalshop.metals');
        $rssPrices = [];

        foreach ($rss->item as $item) {
            foreach ($metalConfigs as $metalConfig) {
                if (preg_match($metalConfig['rss_preg'], $item->description, $output)) {
                    $rssPrices[$metalConfig['name']] = (float) str_replace(',', '.', $output[2]);
                }
            }
        }

        return $rssPrices;
    }

    /**
     * Get the currency exchange rate from the fetched rss items.
     */
    private function getRssCurrencyExchangeRate(Feed $rss): ?float
    {
        $rssCurrencyExchangeRatePreg = config('metalshop.rss.currency_exchange_rate_preg');

        foreach ($rss->item as $item) {
            if (preg_match($rssCurrencyExchangeRatePreg, $item->description, $output)) {
                $rssCurrencyExchangeRate = (float) str_replace(',', '.', $output[2]);
            }
        }

        return $rssCurrencyExchangeRate;
    }

    /**
     * Check if the rss currency's exchange rate is changed.
     */
    private function rssCurrencyChangeExchangeRate(?float $exchangeRate): bool
    {
        $rssCurrencyCode = config('metalshop.rss.currency_code');
        $rssCurrency = Currency::whereCode($rssCurrencyCode)->first();

        return isset($exchangeRate) && $rssCurrency->exchange_rate !== $exchangeRate;
    }

    /**
     * Update the rss currency's exchange rate.
     */
    private function updateRssCurrencyExchangeRate(float $exchangeRate): void
    {
        $this->info('Update the rss currency\'s exchange rate...');

        $rssCurrencyCode = config('metalshop.rss.currency_code');

        Currency::whereCode($rssCurrencyCode)->update(['exchange_rate' => $exchangeRate]);
    }

    /**
     * Update all the prices.
     */
    private function updatePrices(array $rssPrices, float $rssCurrencyExchangeRate): void
    {
        $this->info('Update the prices...');

        $unitConfigs = config('metalshop.units');
        $rssUnitExchangeRate = $unitConfigs[config('metalshop.rss.unit')]['exchange_rate'];

        DB::beginTransaction();

        try {
            foreach ($rssPrices as $name => $rssPrice) {
                Product::search($name)
                    ->get()
                    ->load('variants.prices.currency')
                    ->each(function (Product $product) use ($name, $rssPrice, $rssUnitExchangeRate, $rssCurrencyExchangeRate, $unitConfigs) {
                        if ($product->translateAttribute('metal') !== $name) return;

                        $productUnit = $product->translateAttribute('unit');
                        $productPurity = (float) $product->translateAttribute('purity');
                        $productUnitExchangeRate = $unitConfigs[$productUnit]['exchange_rate'];
                        $newProductPrice = $rssPrice / $rssUnitExchangeRate * $productUnitExchangeRate * ($productPurity / 1000);

                        $product->variants->each(function (ProductVariant $variant) use ($newProductPrice, $rssCurrencyExchangeRate) {
                            $variant->basePrices->each(function (Price $price) use ($newProductPrice, $rssCurrencyExchangeRate) {
                                $newPrice = (int) round($newProductPrice / $price->currency->exchange_rate * $rssCurrencyExchangeRate);
                                $price->price = new PriceData($newPrice, $price->currency);
                            });

                            app(UpdatePrices::class)->execute($variant, $variant->basePrices);
                        }
                    );
                });
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            report($e);
            $this->error($e->getMessage());
            $this->notifyAdmins($e->getMessage());
        }
    }

    /**
     * Notify admin about the error.
     */
    private function notifyAdmins($errorMessage): void
    {
        $admins = Staff::whereAdmin(true)->get();

        Notification::send($admins, new PriceUpdateFailed($errorMessage));
    }
}
