<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;

class CollectionSeeder extends AbstractSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $collectionGroup = CollectionGroup::first();

        Collection::create([
            'collection_group_id' => $collectionGroup->id,
            'attribute_data' => [
                'name' => new TranslatedText([
                    'en' => new Text('Precious Metals'),
                ]),
                'description' => new TranslatedText([
                    'en' => new Text('Collection of Precious Metals'),
                ]),
            ],
        ]);
    }
}
