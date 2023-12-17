<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\TranslatedText;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Product;
use Lunar\Models\ProductType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Lunar\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'brand_id' => Brand::inRandomOrder()->first(),
            'product_type_id' => ProductType::inRandomOrder()->first(),
            'status' => 'published',
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text($this->faker->word),
                ])),
                'description' => new Text($this->faker->paragraph),
                'product_type' => new Dropdown(collect(['Bar', 'Ingot', 'Coin'])->random()),
                'weight' => new Number(rand(1, 1000)),
                'unit' => new Dropdown(collect(['g', 'kg', 'oz'])->random()),
                'purity' => new Number(rand(9000, 9999) / 10),
                'metal' => new Dropdown(collect(['Gold', 'Silver', 'Platinum', 'Palladium'])->random()),
            ],
        ];
    }
}
