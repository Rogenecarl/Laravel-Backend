<?php

namespace App\Services;

use App\Models\Services;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    /**
     * Get all services.
     *
     * @return Collection
     */
    public function getAllServices(): Collection
    {
        return Services::all();
    }

    /**
     * Create a new service.
     *
     * @param array $serviceData
     * @return Services
     */
    public function createService(array $serviceData): Services
    {
        return Services::create($serviceData);
    }

    /**
     * Get a specific service.
     *
     * @param int $id
     * @return Services
     */
    public function getService(int $id): Services
    {
        return Services::findOrFail($id);
    }

    /**
     * Update a service.
     *
     * @param int $id
     * @param array $serviceData
     * @return Services
     */
    public function updateService(int $id, array $serviceData): Services
    {
        $service = $this->getService($id);
        $service->update($serviceData);
        return $service;
    }

    /**
     * Delete a service.
     *
     * @param int $id
     * @return bool
     */
    public function deleteService(int $id): bool
    {
        return $this->getService($id)->delete();
    }

    /**
     * Get services by provider ID.
     *
     * @param int $providerId
     * @return Collection
     */
    public function getServicesByProvider(int $providerId): Collection
    {
        return Services::where('provider_id', $providerId)->get();
    }
}
