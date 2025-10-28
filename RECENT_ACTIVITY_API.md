# Recent Activity API

## Endpoint

**GET** `/api/provider/dashboard/recent-activity`

## Description

Returns recent appointment-related activities for a provider, showing the latest interactions and status changes.

## Authentication

-   Requires Bearer token
-   User must have `provider` role

## Query Parameters

-   `limit` (optional): Number of activities to return (1-50, default: 10)

## Example Requests

### Get Recent Activity (Default)

```
GET /api/provider/dashboard/recent-activity
Authorization: Bearer {token}
```

### Get More Activities

```
GET /api/provider/dashboard/recent-activity?limit=20
Authorization: Bearer {token}
```

## Response Format

```json
{
    "success": true,
    "data": {
        "activities": [
            {
                "id": "appointment_123",
                "type": "appointment",
                "message": "New appointment booked by Sarah Johnson",
                "time": "2 minutes ago"
            },
            {
                "id": "completed_124",
                "type": "completion",
                "message": "Appointment completed with Mike Chen",
                "time": "5 hours ago"
            },
            {
                "id": "confirmed_125",
                "type": "confirmation",
                "message": "Appointment confirmed with Lisa Wang",
                "time": "1 day ago"
            },
            {
                "id": "cancelled_126",
                "type": "cancellation",
                "message": "Appointment cancelled by John Doe",
                "time": "2 days ago"
            }
        ],
        "count": 4,
        "period": "Last 7 days"
    }
}
```

## Response Fields

-   `activities`: Array of recent activities
    -   `id`: Unique identifier for the activity
    -   `type`: Activity type (`appointment`, `confirmation`, `completion`, `cancellation`)
    -   `message`: Human-readable activity description
    -   `time`: Human-readable time ago (e.g., "2 minutes ago", "5 hours ago")
-   `count`: Number of activities returned
-   `period`: Time period covered ("Last 7 days")

## Activity Types

1. **appointment**: New appointment bookings (pending status)
2. **confirmation**: Appointments that were confirmed
3. **completion**: Appointments that were completed
4. **cancellation**: Appointments that were cancelled

## Time Format

The `time` field uses human-readable formats:

-   "Just now" - Less than 1 minute
-   "X minutes ago" - Less than 1 hour
-   "X hours ago" - Less than 24 hours
-   "X days ago" - Less than 7 days
-   "MMM DD, YYYY" - Older than 7 days

## Data Source

-   Shows activities from the last 7 days
-   Based on appointment creation and status changes
-   Sorted by most recent first
-   Only includes appointments belonging to the authenticated provider

## Error Responses

### Provider Not Found (404)

```json
{
    "success": false,
    "message": "Provider not found"
}
```

### Invalid Limit (422)

```json
{
    "success": false,
    "message": "The limit must be between 1 and 50."
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "Failed to fetch recent activity",
    "error": "Detailed error message"
}
```

## Usage Notes

-   Activities are generated from appointment records
-   Only shows activities from the last 7 days
-   Real user names are included in activity messages
-   Activities are automatically sorted by recency
-   Empty result if no recent appointments exist

## Integration Example

```javascript
// Fetch recent activity
const response = await axios.get("/api/provider/dashboard/recent-activity", {
    params: { limit: 15 },
    headers: {
        Authorization: `Bearer ${token}`,
    },
});

const activities = response.data.data.activities;
```
