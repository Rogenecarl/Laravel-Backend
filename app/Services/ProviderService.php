<?php

namespace App\Services;

use App\Models\Document;
use App\Models\OperatingHour;
use App\Models\Provider;
use App\Models\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;


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

            // Handle cover photo upload if present and valid
            if (isset($providerData['cover_photo']) && $providerData['cover_photo'] instanceof UploadedFile) {
                // Store file and get relative path
                $providerData['cover_photo'] = $providerData['cover_photo']->store('uploads/providers', 'public');
            } else {
                unset($providerData['cover_photo']);
            }

            // Create the provider
            $provider = Provider::create($providerData);

            // If services are provided, create them
            if (isset($providerData['services']) && is_array($providerData['services'])) {
                foreach ($providerData['services'] as $serviceData) {
                    $serviceData['provider_id'] = $provider->id;
                    Services::create($serviceData);
                }
            }

            // create operating hours if provided
            if (isset($providerData['operating_hours']) && is_array($providerData['operating_hours'])) {
                foreach ($providerData['operating_hours'] as $operatingHourData) {
                    $operatingHourData['provider_id'] = $provider->id;
                    OperatingHour::create($operatingHourData);
                }
            }

            // create documents if provided
            if (isset($providerData['documents']) && is_array($providerData['documents'])) {
                foreach ($providerData['documents'] as $documentData) {
                    if (isset($documentData['file_path']) && $documentData['file_path'] instanceof UploadedFile) {
                        // Store file and get relative path
                        $documentData['file_path'] = $documentData['file_path']->store('uploads/documents', 'public');
                    } else {
                        unset($documentData['file_path']);
                    }
                    $documentData['provider_id'] = $provider->id;
                    Document::create($documentData);
                }
            }

            // Load the services relationship
            $provider->load('services', 'operatingHours', 'documents');

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
        return Provider::with('services', 'operatingHours', 'documents')->get();
    }

    /**
     * Get a specific provider with its services.
     *
     * @param int $id
     * @return Provider
     */
    public function getProvider(int $id): Provider
    {
        return Provider::with('services', 'operatingHours', 'documents')->findOrFail($id);
    }


}
