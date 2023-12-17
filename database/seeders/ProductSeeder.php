<?php

namespace Database\Seeders;

use Database\Factories\PriceFactory;
use Database\Factories\ProductFactory;
use Database\Factories\ProductVariantFactory;
use Illuminate\Support\Facades\DB;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\ListField;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\Jobs\Products\GenerateVariants;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

class ProductSeeder extends AbstractSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $collection = Collection::first();

        ProductFactory::times(100)
            ->has(ProductVariantFactory::new()
                ->has(PriceFactory::new())
            , 'variants')
            ->create()
            ->each(function ($product) use ($collection) {
                $product->collections()->attach($collection->id);

                $media = $product->addMedia(base_path("database/seeders/data/images/{$product->translateAttribute('metal')}.jpg"))
                    ->preservingOriginal()
                    ->toMediaCollection('products');

                $media->setCustomProperty('primary', true);
                $media->save();
            });
    }
}
