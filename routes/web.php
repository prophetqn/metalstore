<?php

use App\Http\Livewire\CheckoutPage;
use App\Http\Livewire\CheckoutSuccessPage;
use App\Http\Livewire\CollectionPage;
use App\Http\Livewire\Home;
use App\Http\Livewire\ProductPage;
use App\Http\Livewire\SearchPage;
use Illuminate\Support\Facades\Route;
use Lunar\Facades\Pricing;
use Lunar\Hub\Actions\Pricing\UpdatePrices;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Vedmant\FeedReader\Facades\FeedReader;
use willvincent\Feeds\Facades\FeedsFacade;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', Home::class);

Route::get('collections/{slug}', CollectionPage::class)->name('collection.view');

Route::get('products/{slug}', ProductPage::class)->name('product.view');

Route::get('search', SearchPage::class)->name('search.view');

Route::get('checkout', CheckoutPage::class)->name('checkout.view');

Route::get('checkout/success', CheckoutSuccessPage::class)->name('checkout-success.view');

Route::get('test', function () {
    $url = config('metalshop.rss.url');
    $metalConfigs = config('metalshop.metals');
    $unitConfigs = config('metalshop.units');
    $rssUnitExchangeRate = $unitConfigs[config('metalshop.rss.unit')]['exchange_rate'];
    $rssPrices = [];
    $rssCurrencyExchangeRatePreg = config('metalshop.rss.currency_exchange_rate_preg');
    $rssCurrency = Currency::whereCode(config('metalshop.rss.currency_code'))->first();
    $rssExchangeRate = (float) $rssCurrency->exchange_rate;

    try {
        $rss = Feed::loadRss($url);

        foreach ($rss->item as $item) {
            foreach ($metalConfigs as $metalConfig) {
                if (preg_match($metalConfig['rss_preg'], $item->description, $output)) {
                    $rssPrices[$metalConfig['name']] = (float) str_replace(',', '.', $output[2]);
                }
            }

            if (preg_match($rssCurrencyExchangeRatePreg, $item->description, $output)) {
                $newCurrencyExchangeRate = (float) str_replace(',', '.', $output[2]);

                if ($newCurrencyExchangeRate !== $rssExchangeRate) {
                    $rssExchangeRate = $newCurrencyExchangeRate;
                    Currency::whereId($rssCurrency->id)->update(['exchange_rate' => $rssExchangeRate]);
                }
            }

            echo $item->description . "<br>";
        }
    } catch (FeedException $e) {
        Log::error($e->getMessage());
    }

    foreach ($rssPrices as $name => $rssPrice) {
        Product::search($name)
            ->get()
            ->load('variants.prices.currency')
            ->each(function (Product $product) use ($name, $rssPrice, $rssUnitExchangeRate, $rssExchangeRate, $unitConfigs) {
                if ($product->translateAttribute('metal') !== $name) return;

                $productUnit = $product->translateAttribute('unit');
                $productPurity = (float) $product->translateAttribute('purity');
                $productUnitExchangeRate = $unitConfigs[$productUnit]['exchange_rate'];
                $newProductPrice = $rssPrice / $rssUnitExchangeRate * $productUnitExchangeRate * ($productPurity / 1000);

                $product->variants->each(function (ProductVariant $variant) use ($newProductPrice, $rssExchangeRate) {
                    $variant->basePrices->each(function (Price $price) use ($newProductPrice, $rssExchangeRate) {
                        $newPrice = (int) round($newProductPrice / $price->currency->exchange_rate * $rssExchangeRate);
                        $price->price = new \Lunar\DataTypes\Price($newPrice, $price->currency);
                    });

                    app(UpdatePrices::class)->execute($variant, $variant->basePrices);
                }
            );
        });
    }

    return 'a';
});
