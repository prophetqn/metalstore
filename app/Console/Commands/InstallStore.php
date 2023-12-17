<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Facades\DB;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Hub\AdminHubServiceProvider;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;

class InstallStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the store';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $this->info('Installing the store...');

        $this->info('Linking the storage directory...');
        $this->call('storage:link');

        $this->info('Running the migrations...');
        $this->call('migrate');

        $this->info('Importing the default data...');
        $this->importDefaultData();

        $this->info('Seed the demo data....');
        $this->call('db:seed');

        if ($this->isHubInstalled()) {
            $this->info('Installing Admin Hub...');
            $this->call('lunar:hub:install');
        }

        $this->info('Store is now installed 🚀');
    }

    /**
     * Determines if the admin hub is installed.
     *
     * @return bool
     */
    private function isHubInstalled()
    {
        return class_exists(AdminHubServiceProvider::class);
    }

    /**
     * Import the default data
     */
    private function importDefaultData(): void
    {
        DB::transaction(function () {
            if (! Country::count()) {
                $this->info('Importing countries');
                $this->call('lunar:import:address-data');
            }

            if (! Channel::whereDefault(true)->exists()) {
                $this->info('Setting up default channel');

                Channel::create([
                    'name' => 'Webstore',
                    'handle' => 'webstore',
                    'default' => true,
                    'url' => 'http://localhost',
                ]);
            }

            if (! Language::count()) {
                $this->info('Adding default language');

                Language::create([
                    'code' => 'en',
                    'name' => 'English',
                    'default' => true,
                ]);
            }

            if (! Currency::whereDefault(true)->exists()) {
                $this->info('Adding a default currencies');

                Currency::create([
                    'code' => 'USD',
                    'name' => 'US Dollar',
                    'exchange_rate' => 1,
                    'decimal_places' => 2,
                    'default' => true,
                    'enabled' => true,
                ]);

                Currency::create([
                    'code' => 'EUR',
                    'name' => 'Euro',
                    'exchange_rate' => 1.09,
                    'decimal_places' => 2,
                    'default' => false,
                    'enabled' => true,
                ]);
            }

            if (! CustomerGroup::whereDefault(true)->exists()) {
                $this->info('Adding a default customer group.');

                CustomerGroup::create([
                    'name' => 'Retail',
                    'handle' => 'retail',
                    'default' => true,
                ]);
            }

            if (! CollectionGroup::count()) {
                $this->info('Adding an initial collection group');

                CollectionGroup::create([
                    'name' => 'Main',
                    'handle' => 'main',
                ]);
            }

            if (! TaxClass::count()) {
                $this->info('Adding a default tax class.');

                TaxClass::create([
                    'name' => 'Default Tax Class',
                    'default' => true,
                ]);
            }

            if (! Attribute::count()) {
                $this->info('Setting up initial attributes');

                $group = AttributeGroup::create([
                    'attributable_type' => Product::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'details',
                    'position' => 1,
                ]);

                $collectionGroup = AttributeGroup::create([
                    'attributable_type' => Collection::class,
                    'name' => collect([
                        'en' => 'Details',
                    ]),
                    'handle' => 'collection_details',
                    'position' => 1,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 1,
                    'name' => [
                        'en' => 'Name',
                    ],
                    'handle' => 'name',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => true,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => false,
                    ],
                    'system' => true,
                ]);

                Attribute::create([
                    'attribute_type' => Product::class,
                    'attribute_group_id' => $group->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);

                Attribute::create([
                    'attribute_type' => Collection::class,
                    'attribute_group_id' => $collectionGroup->id,
                    'position' => 2,
                    'name' => [
                        'en' => 'Description',
                    ],
                    'handle' => 'description',
                    'section' => 'main',
                    'type' => TranslatedText::class,
                    'required' => false,
                    'default_value' => null,
                    'configuration' => [
                        'richtext' => true,
                    ],
                    'system' => false,
                ]);
            }

            if (! ProductType::count()) {
                $this->info('Adding a product type.');

                $type = ProductType::create([
                    'name' => 'Stock',
                ]);

                $type->mappedAttributes()->attach(
                    Attribute::whereAttributeType(Product::class)->get()->pluck('id')
                );
            }
        });
    }
}
