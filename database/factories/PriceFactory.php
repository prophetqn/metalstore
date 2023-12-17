<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Lunar\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'customer_group_id' => null,
            'currency_id' => Currency::getDefault(),
            'priceable_type' => ProductVariant::class,
            'priceable_id' => ProductVariant::inRandomOrder()->first(),
            'price' => rand(1, 10000) * 100,
            'tier' => 1,
        ];
    }
}
