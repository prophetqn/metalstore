<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Lunar\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'product_id' => Product::inRandomOrder()->first(),
            'purchasable' => 'always',
            'shippable' => true,
            'backorder' => 0,
            'sku' => $this->faker->regexify('[A-Z]{3}[0-9]{3}'),
            'tax_class_id' => TaxClass::getDefault(),
            'stock' => 50,
        ];
    }
}
