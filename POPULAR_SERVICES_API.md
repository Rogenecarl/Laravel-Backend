# Popular Services Statistics API

## Endpoint
**GET** `/api/provider/dashboard/popular-services-stats`

## Description
Returns statistics of services based on completed appointments, showing which services are most popular.

## Authentication
- Requires Bearer token
- User must have `provider` role

## Query Parameters
- `period` (optional): Time period for statistics
  - `week` - Current week
  - `month` - Current month (default)
  - `year` - Current year

## Example Requests

### Get Monthly Stats (Default)
```
GET /api/provider/dashboard/popular-services-stats
Authorization: Bearer {token}
```

### Get Weekly Stats
```
GET /api/provider/dashboard/popular-services-stats?period=week
Authorization: Bearer {token}
```

### Get Yearly Stats
```
GET /api/provider/dashboard/popular-services-stats?period=year
Authorization: Bearer {token}
```

## Response Format

```json
{
  "success": true,
  "data": {
    "services": [
      {
        "service": "General Consultation",
        "bookings": 45
      },
      {
        "service": "Health Checkup", 
        "bookings": 32
      },
      {
        "service": "Follow-up Visit",
        "bookings": 28
      },
      {
        "service": "Vaccination",
        "bookings": 15
      }
    ],
    "period": "month",
    "period_label": "October 2025",
    "start_date": "2025-10-01",
    "end_date": "2025-10-31",
    "total_completed_appointments": 120
  }
}
```

## Response Fields

- `services`: Array of service statistics (top 10)
  - `service`: Service name
  - `bookings`: Number of completed appointments for this service
- `period`: The requested period (week/month/year)
- `period_label`: Human-readable period label
- `start_date`: Start date of the period
- `end_date`: End date of the period
- `total_completed_appointments`: Total completed appointments in the period

## Error Responses

### Provider Not Found (404)
```json
{
  "success": false,
  "message": "Provider not found"
}
```

### Invalid Period (422)
```json
{
  "success": false,
  "message": "The selected period is invalid."
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to fetch popular services statistics",
  "error": "Detailed error message"
}
```

## Notes

- Only counts appointments with `completed` status
- Returns top 10 most popular services
- Services are sorted by booking count (descending)
- If a service has no completed appointments in the period, it won't appear in results
- The endpoint uses the provider's timezone for date calculations