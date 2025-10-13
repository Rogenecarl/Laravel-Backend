<?php

namespace App\Services;

use App\Models\OperatingHour;
use App\Http\Resources\OperatingHourResource;
use Illuminate\Support\Collection;

class OperatingHoursService
{
    /**
     * Get operating hours for a provider
     */
    public function getOperatingHours(int $providerId): Collection
    {
        return OperatingHour::where('provider_id', $providerId)
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * Update operating hours for a provider
     */
    public function updateOperatingHours(int $providerId, array $operatingHoursData): Collection
    {
        foreach ($operatingHoursData as $hourData) {
            OperatingHour::updateOrCreate(
                [
                    'provider_id' => $providerId,
                    'day_of_week' => $hourData['day_of_week']
                ],
                [
                    'start_time' => $hourData['start_time'],
                    'end_time' => $hourData['end_time'],
                    'is_closed' => $hourData['is_closed']
                ]
            );
        }

        return $this->getOperatingHours($providerId);
    }
}