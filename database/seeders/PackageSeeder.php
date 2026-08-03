<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name_en' => 'Basic',
                'name_ur' => 'بنیادی',
                'name_ar' => 'أساسي',
                'slug' => 'basic',
                'description_en' => 'Best for small teams getting started.',
                'price_monthly' => 9.99,
                'price_yearly' => 99.00,
                'is_trial_package' => true,
                'status' => 'active',
                'sort_order' => 1,
                'features' => [
                    ['feature_key' => 'max_users', 'feature_label_en' => 'Users', 'value' => '5'],
                    ['feature_key' => 'max_storage_gb', 'feature_label_en' => 'Storage', 'value' => '5GB'],
                ],
            ],
            [
                'name_en' => 'Standard',
                'name_ur' => 'معیاری',
                'name_ar' => 'قياسي',
                'slug' => 'standard',
                'description_en' => 'For growing businesses with more needs.',
                'price_monthly' => 24.99,
                'price_yearly' => 249.00,
                'is_trial_package' => true,
                'status' => 'active',
                'sort_order' => 2,
                'features' => [
                    ['feature_key' => 'max_users', 'feature_label_en' => 'Users', 'value' => '20'],
                    ['feature_key' => 'max_storage_gb', 'feature_label_en' => 'Storage', 'value' => '25GB'],
                ],
            ],
            [
                'name_en' => 'Professional',
                'name_ur' => 'پروفیشنل',
                'name_ar' => 'محترف',
                'slug' => 'professional',
                'description_en' => 'Advanced features for established companies.',
                'price_monthly' => 49.99,
                'price_yearly' => 499.00,
                'is_trial_package' => true,
                'status' => 'active',
                'sort_order' => 3,
                'features' => [
                    ['feature_key' => 'max_users', 'feature_label_en' => 'Users', 'value' => '50'],
                    ['feature_key' => 'max_storage_gb', 'feature_label_en' => 'Storage', 'value' => '100GB'],
                ],
            ],
            [
                'name_en' => 'Enterprise',
                'name_ur' => 'انٹرپرائز',
                'name_ar' => 'مؤسسي',
                'slug' => 'enterprise',
                'description_en' => 'Unlimited scale for large organizations.',
                'price_monthly' => 99.99,
                'price_yearly' => 999.00,
                'is_trial_package' => false,
                'status' => 'active',
                'sort_order' => 4,
                'features' => [
                    ['feature_key' => 'max_users', 'feature_label_en' => 'Users', 'value' => 'Unlimited'],
                    ['feature_key' => 'max_storage_gb', 'feature_label_en' => 'Storage', 'value' => 'Unlimited'],
                ],
            ],
        ];

        foreach ($packages as $data) {
            $features = $data['features'];
            unset($data['features']);

            $package = Package::firstOrCreate(['slug' => $data['slug']], $data);

            foreach ($features as $feature) {
                $package->features()->firstOrCreate(
                    ['feature_key' => $feature['feature_key']],
                    $feature
                );
            }
        }
    }
}