<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Provider;
use App\Models\Services;
use App\Models\OperatingHour;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


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
     * @param string $id
     * @return Provider
     */
    public function getProvider(string $id): Provider
    {
        return Provider::with('services', 'operatingHours', 'documents')->findOrFail($id);
    }


    public function searchProviders(array $filters)
    {
        // Start with a base query for verified providers and eager-load services
        $query = Provider::query()
            ->where('status', 'verified')
            ->with('services', 'operatingHours');

        // Conditionally apply filters only if they are present in the request
        $query->when($filters['category_id'] ?? null, function (Builder $query, $categoryId) {
            $query->where('category_id', $categoryId);
        });

        $query->when($filters['search_term'] ?? null, function (Builder $query, $searchTerm) {
            // Convert the search term to lowercase once
            $lowerSearchTerm = strtolower($searchTerm);

            $query->where(function (Builder $q) use ($lowerSearchTerm) {
                // Use the DB::raw() to apply the LOWER function to the columns
                $q->where(DB::raw('LOWER(healthcare_name)'), 'like', "%{$lowerSearchTerm}%")
                    ->orWhere(DB::raw('LOWER(address)'), 'like', "%{$lowerSearchTerm}%")
                    ->orWhereHas('services', function (Builder $q) use ($lowerSearchTerm) {
                        $q->where(DB::raw('LOWER(name)'), 'like', "%{$lowerSearchTerm}%");
                    });
            });
        });

        // Add more ->when() clauses for your other filters (availability, distance, etc.)

        // Finally, execute the query and return the results
        return $query->get();
    }

    public function getSearchSuggestions(string $searchTerm)
    {
        // Don't run a search on an empty string
        if (empty($searchTerm)) {
            return collect();
        }

        $lowerSearchTerm = strtolower($searchTerm);

        return Provider::query()
            ->where('status', 'verified')
            ->select(['id', 'healthcare_name', 'address', 'city'])

            // ** THE FIX IS HERE **
            // Eager load the 'services' relationship, BUT only load the ones
            // that actually match the search term.
            ->with([
                'services' => function ($query) use ($lowerSearchTerm) {
                    $query->where(DB::raw('LOWER(name)'), 'like', "%{$lowerSearchTerm}%")
                        ->select(['provider_id', 'name']); // Only select the columns we need
                }
            ])

            ->where(function (Builder $query) use ($lowerSearchTerm) {
                $query->where(DB::raw('LOWER(healthcare_name)'), 'like', "%{$lowerSearchTerm}%")
                    ->orWhere(DB::raw('LOWER(address)'), 'like', "%{$lowerSearchTerm}%")
                    ->orWhereHas('services', function (Builder $serviceQuery) use ($lowerSearchTerm) {
                        $serviceQuery->where(DB::raw('LOWER(name)'), 'like', "%{$lowerSearchTerm}%");
                    });
            })
            ->limit(10)
            ->get();
    }

    public function getProvidersAsGeoJSON(): array
    {
        // 1. Fetch only verified providers and select all necessary columns for the map popup
        $providers = Provider::query()
            ->where('status', 'verified')
            ->select([
                'id', 
                'healthcare_name', 
                'description',
                'address', 
                'city',
                'province',
                'latitude', 
                'longitude', 
                'category_id',
                'cover_photo',
                'email',
                'phone_number'
            ])
            ->get();

        // 2. Map the collection of providers into a GeoJSON Feature array
        $features = $providers->map(function ($provider) {
            // Ensure longitude and latitude are correctly cast to floats
            $longitude = (float) $provider->longitude;
            $latitude = (float) $provider->latitude;

            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$longitude, $latitude],
                ],
                'properties' => [
                    // 'properties' holds the data you'll want to show in a popup
                    'id' => $provider->id,
                    'name' => $provider->healthcare_name,
                    'healthcare_name' => $provider->healthcare_name,
                    'description' => $provider->description,
                    'address' => $provider->address,
                    'city' => $provider->city,
                    'province' => $provider->province,
                    'categoryId' => $provider->category_id, // Useful for frontend filtering
                    'cover_photo' => $provider->cover_photo ? asset("storage/{$provider->cover_photo}") : null,
                    'email' => $provider->email,
                    'phone_number' => $provider->phone_number,
                ],
            ];
        })->all(); // Convert the Laravel Collection to a plain array

        // 3. Wrap the features in a GeoJSON FeatureCollection object
        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}

