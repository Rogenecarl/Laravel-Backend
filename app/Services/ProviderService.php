<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Services;
use Illuminate\Support\Facades\DB;

class ProviderService
{
    /**
     * Create a new provider with its associated services.
     *
     * @param array $providerData
     * @param array $servicesData
     * @return Provider
     */
    public function createProvider(array $providerData): Provider
    {
        return DB::transaction(function () use ($providerData) {
            // Create the provider

            $provider = Provider::create($providerData);

            // If services are provided, create them
            if (isset($providerData['services']) && is_array($providerData['services'])) {
                foreach ($providerData['services'] as $serviceData) {
                    $serviceData['provider_id'] = $provider->id;
                    Services::create($serviceData);
                }
            }

            // Load the services relationship
            $provider->load('services');

            return $provider;
        });
    }

    /**
     * Get all providers with their services.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProviders()
    {
        return Provider::with('services')->get();
    }

    /**
     * Get a specific provider with its services.
     *
     * @param int $id
     * @return Provider
     */
    public function getProvider(int $id): Provider
    {
        return Provider::with('services')->findOrFail($id);
    }


}
