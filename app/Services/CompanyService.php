<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyService
{
    /**
     * Create a Company for a freshly registered User, assign company-owner role.
     */
    public function createForUser(User $user, string $companyName): Company
    {
        return DB::transaction(function () use ($user, $companyName) {
            $company = Company::create([
                'name' => $companyName,
                'slug' => $this->generateUniqueSlug($companyName),
                'email' => $user->email,
                'status' => 'pending',
            ]);

            $user->update(['company_id' => $company->id]);
            $user->assignRole('company-owner');

            return $company;
        });
    }

    protected function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}