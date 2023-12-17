<?php

namespace App\Http\Livewire;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\Url;

class Home extends Component
{
    use WithPagination;

    /**
     * Return a collection.
     *
     * @return void
     */
    public function getCollectionProperty()
    {
        $collection = Url::whereElementType(Collection::class);

        return $collection->first()->element;
    }

    /**
     * Return a collection.
     *
     * @return void
     */
    public function getProductsProperty()
    {
        $perPage = config('metalshop.pagination.product.home');

        return $this->collection
            ->products()
            ->with(['thumbnail', 'defaultUrl', 'variants.prices.currency', 'variants.prices.priceable'])
            ->paginate($perPage);
    }

    public function render()
    {
        return view('livewire.home');
    }
}
