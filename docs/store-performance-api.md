# Store Performance API Documentation

## Endpoint

```
GET /api/v3/report/store-performance
```

**Authentication:** `auth:api` (Laravel Passport OAuth2 token required)

---

## Request Parameters

| Parameter    | Type   | Required | Valid Values                                                    |
|-------------|--------|----------|------------------------------------------------------------------|
| `filter`     | string | No       | `all` (default), `today`, `last_7_days`, `this_month`, `last_month`, `custom` |
| `start_date` | date   | If custom| `Y-m-d` format. Required when `filter=custom`                    |
| `end_date`   | date   | If custom| `Y-m-d` format. Required when `filter=custom`. Must be >= start_date |

**Examples:**
```
GET /api/v3/report/store-performance
GET /api/v3/report/store-performance?filter=last_7_days
GET /api/v3/report/store-performance?filter=custom&start_date=2026-01-01&end_date=2026-02-27
```

**Validation Errors (HTTP 422):**
```json
{
  "status": false,
  "message": "Validation errors",
  "errors": {
    "filter": ["Invalid filter. Allowed values: all, today, last_7_days, this_month, last_month, custom"],
    "start_date": ["start_date is required when using custom filter"]
  }
}
```

---

## Response Structure

```json
{
  "status": true,
  "data": {
    "summary": {
      "cancellation_rate": { ... },
      "delivered_timely": { ... },
      "return_rate": { ... },
      "returning_customers": { ... },
      "conversion_rate": { ... },
      "total_visitors": 6436,
      "unique_visitors": 1400,
      "abandoned_carts": 43
    },
    "graphs": {
      "cancellation_trend": [ ... ],
      "on_time_delivery_trend": [ ... ],
      "return_trend": [ ... ],
      "returning_customers_trend": [ ... ],
      "conversion_rate_trend": [ ... ],
      "orders_trend": [ ... ],
      "revenue_trend": [ ... ]
    }
  }
}
```

---

## Summary Labels Explained

### 1. Cancellation Rate

**UI Label:** "Cancellation Rate"
**What it shows:** Percentage of total orders that were cancelled.

```json
{
  "rate": 1.0,
  "cancelled_orders": 2,
  "total_orders": 200
}
```

| Field              | Type  | Description                                           |
|--------------------|-------|-------------------------------------------------------|
| `rate`             | float | `(cancelled_orders / total_orders) * 100`, rounded to 1 decimal |
| `cancelled_orders` | int   | Orders with `order_status = 'cancelled'` in date range |
| `total_orders`     | int   | All orders in date range (any status)                  |

**Formula:**
```
rate = (cancelled_orders / total_orders) * 100
```

---

### 2. Delivered Timely (On-Time Delivery Performance)

**UI Label:** "Delivered Timely"
**What it shows:** Percentage of shipped orders that were delivered within the promised delivery window.

```json
{
  "rate": 98.5,
  "on_time_orders": 170,
  "total_delivered_orders": 180,
  "change_percentage": 1.5,
  "trend": "up"
}
```

| Field                    | Type   | Description                                                    |
|--------------------------|--------|----------------------------------------------------------------|
| `rate`                   | float  | `(on_time_orders / total_delivered_orders) * 100`, 2 decimals  |
| `on_time_orders`         | int    | Orders where `shipped_date <= created_at + max(delivery_time)` |
| `total_delivered_orders` | int    | Orders with `order_status='shipped'` AND `shipped_date IS NOT NULL` in range |
| `change_percentage`      | float  | Absolute difference vs previous period rate                    |
| `trend`                  | string | `"up"`, `"down"`, or `"neutral"` compared to previous period  |

**Business Logic:**
1. `order_shippings.delivery_time` stores the estimated window, e.g. `"3-5 Days"`
2. Extract the **maximum** number: `"3-5 Days"` → `5`
3. Calculate expected delivery date: `expected_date = store_orders.created_at + 5 days`
4. If `shipped_date <= expected_date` → **On-Time**
5. If `shipped_date > expected_date` → **Late**

**Filters applied:**
- `order_status = 'shipped'`
- `shipped_date IS NOT NULL`
- `shipped_date` within selected date range

**Previous Period Comparison:**
- Automatically compares with an equal-length previous period
- Example: If viewing Jan 1-31 (31 days), compares with Dec 1-31
- `change_percentage` = `|current_rate - previous_rate|`
- `trend` = direction of change

---

### 3. Return Rate

**UI Label:** "Return Rate"
**What it shows:** Percentage of orders that were refunded.

```json
{
  "rate": 2.4,
  "returned_orders": 3,
  "total_orders": 125
}
```

| Field             | Type  | Description                                          |
|-------------------|-------|------------------------------------------------------|
| `rate`            | float | `(returned_orders / total_orders) * 100`, 1 decimal  |
| `returned_orders` | int   | Orders with `order_status = 'refunded'` in range     |
| `total_orders`    | int   | All orders in date range                             |

**Formula:**
```
rate = (returned_orders / total_orders) * 100
```

---

### 4. Returning Customers

**UI Label:** "Returning Customers"
**What it shows:** Percentage of customers who placed more than one order from this store in the period.

```json
{
  "rate": 3.4,
  "change_percentage": 2.0,
  "trend": "down"
}
```

| Field               | Type   | Description                                                   |
|---------------------|--------|---------------------------------------------------------------|
| `rate`              | float  | `(repeat_buyers / total_unique_buyers) * 100`, 2 decimals    |
| `change_percentage` | float  | Absolute difference vs previous period rate                   |
| `trend`             | string | `"up"`, `"down"`, or `"neutral"`                              |

**Logic:**
- A "returning customer" is a `buyer_id` that appears in more than 1 order within the period
- Total customers = distinct `buyer_id` count in the period

---

### 5. Conversion Rate

**UI Label:** "Conversion Rate"
**What it shows:** Percentage of unique visitors who made a purchase.

```json
{
  "rate": 1.5,
  "change_percentage": 1.0,
  "trend": "up"
}
```

| Field               | Type   | Description                                                         |
|---------------------|--------|---------------------------------------------------------------------|
| `rate`              | float  | `(unique_buyers / unique_visitors) * 100`, 1 decimal                |
| `change_percentage` | float  | Percentage growth vs previous period: `((current - prev) / prev) * 100` |
| `trend`             | string | `"up"`, `"down"`, or `"neutral"`                                    |

**Data Sources:**
- **Unique visitors:** Algolia analytics (search impressions). Falls back to DB (orders + cart users) if Algolia unavailable
- **Unique buyers:** Distinct `buyer_id` from `store_orders` in the period

---

### 6. Total Visitors

**UI Label:** "Total Visitors"
**Type:** integer

Total search impressions from Algolia analytics for this store's products. Returns `0` if Algolia is not configured.

---

### 7. Unique Visitors

**UI Label:** "Unique Visitors"
**Type:** integer

Estimated unique visitors to this store. Uses Algolia `/2/users/count` API, proportionally scaled by the store's share of total search impressions. Falls back to counting distinct users from orders + cart interactions.

---

### 8. Abandoned Carts

**UI Label:** "Abandoned Carts"
**Type:** integer

Count of distinct users (or guest `random_key`) who added items to cart for this store but didn't complete checkout, within the selected period.

---

## Graph Data

**All graphs include every date in the selected range.** Missing dates are filled with `count: 0` (or `revenue: 0`). Graphs never return empty arrays or skip dates.

**Example (Jan 1-3 range, data only on Jan 2):**
```json
[
  { "date": "2026-01-01", "count": 0 },
  { "date": "2026-01-02", "count": 3 },
  { "date": "2026-01-03", "count": 0 }
]
```

**Consistency rule:** The sum of a graph's `count` values must always match the corresponding summary metric count (e.g. `return_trend` sum == `return_rate.returned_orders`).

### cancellation_trend
Daily count of cancelled orders (`order_status = 'cancelled'`).

### on_time_delivery_trend
Daily count of on-time delivered orders (`order_status = 'completed'`, `delivery_date <= expected`).

### return_trend
Daily count of returned/refunded orders (`order_status IN ['refunded', 'returned']`).
**Note:** Does NOT include cancelled orders — those appear in `cancellation_trend`.

### returning_customers_trend
Daily count of repeat customers (buyers who had previous orders before that date).

### conversion_rate_trend
Daily order count (conversions).

### orders_trend
Daily total order count (all statuses).

### revenue_trend
Daily revenue (`payable_amount` from paid, non-refunded/cancelled orders). Uses `revenue` key instead of `count`.
```json
[{ "date": "2026-01-20", "revenue": 1250.50 }, { "date": "2026-01-21", "revenue": 0 }]
```

---

## Error Responses

**Store not found (HTTP 404):**
```json
{ "status": false, "message": "Store not found" }
```

**Server error (HTTP 500):**
```json
{ "status": false, "message": "Failed to generate store performance report: ..." }
```

---

## PDF Export

```
GET /api/v3/report/store-performance/pdf
```

Same parameters as the JSON endpoint. Returns a downloadable PDF file (`store-performance-report.pdf`).

---

## Test Cases

Run: `vendor/bin/phpunit tests/Feature/PerformanceDeliveryRateTest.php`

### A) On-Time Delivery Rate (getPerformanceDeliveryRateData)

| # | Test | What it verifies |
|---|------|------------------|
| 1 | `delivery_rate_returns_zero_when_no_shipped_orders_exist` | Returns rate=0, on_time=0, total=0, trend=neutral when no data |
| 2 | `delivery_rate_on_time_when_shipped_before_expected_date` | shipped_date < expected → on-time, rate=100% |
| 3 | `delivery_rate_on_time_when_shipped_exactly_on_expected_date` | shipped_date == expected → still on-time (<=), rate=100% |
| 4 | `delivery_rate_late_when_shipped_after_expected_date` | shipped_date > expected → late, rate=0% |
| 5 | `delivery_rate_mixed_on_time_and_late_orders` | 3 on-time + 1 late = 75% |
| 6 | `delivery_rate_extracts_max_days_from_different_formats` | "2-4 Days"→4, "7-10 Days"→10 |
| 7 | `delivery_rate_excludes_non_shipped_status_orders` | pending/cancelled orders ignored |
| 8 | `delivery_rate_excludes_orders_with_null_shipped_date` | shipped_date=NULL excluded |
| 9 | `delivery_rate_only_counts_orders_within_date_range` | Out-of-range orders excluded |
| 10 | `delivery_rate_only_counts_orders_for_given_store` | Other store's orders excluded |
| 11 | `delivery_rate_handles_missing_shipping_record` | No order_shippings row → counted as not on-time |
| 12 | `delivery_rate_returns_trend_and_change_percentage` | Response has trend + change_percentage keys |

### B) Previous Period Helper (getOnTimeDeliveryRate)

| # | Test | What it verifies |
|---|------|------------------|
| 13 | `on_time_rate_returns_zero_when_no_orders` | Returns 0 for empty data |
| 14 | `on_time_rate_calculates_correctly` | 2 on-time + 1 late = 66.67% |
| 15 | `on_time_rate_uses_shipped_status_not_completed` | Only counts `shipped`, not `completed` |

### C) Cancellation Rate (getCancellationRate)

| # | Test | What it verifies |
|---|------|------------------|
| 16 | `cancellation_rate_returns_zero_when_no_orders` | Returns rate=0 for empty data |
| 17 | `cancellation_rate_calculates_correctly` | 2 cancelled / 5 total = 40% |

### D) Return Rate (getReturnRate)

| # | Test | What it verifies |
|---|------|------------------|
| 18 | `return_rate_returns_zero_when_no_orders` | Returns rate=0 for empty data |
| 19 | `return_rate_calculates_correctly` | 1 refunded / 4 total = 25% |

### E) Returning Customers (getReturningRateWithComparison)

| # | Test | What it verifies |
|---|------|------------------|
| 20 | `returning_customers_rate_zero_when_no_orders` | Returns rate=0 with trend keys |
| 21 | `returning_customers_rate_calculates_correctly` | 1 repeat buyer / 2 total = 50% |

### F) Response Structure

| # | Test | What it verifies |
|---|------|------------------|
| 22 | `delivery_rate_response_has_all_required_keys` | rate, on_time_orders, total_delivered_orders, change_percentage, trend |
| 23 | `cancellation_rate_response_has_all_required_keys` | rate, cancelled_orders, total_orders |
| 24 | `return_rate_response_has_all_required_keys` | rate, returned_orders, total_orders |

### G) Graph — Missing Dates & Data Consistency

| # | Test | What it verifies |
|---|------|------------------|
| 25 | `return_trend_sum_matches_return_rate_count` | Graph sum == summary returned_orders count |
| 26 | `cancellation_trend_sum_matches_cancellation_rate_count` | Graph sum == summary cancelled_orders count |
| 27 | `graph_fills_all_missing_dates_with_zero` | All 31 dates present for Jan, missing = 0 |
| 28 | `graph_returns_all_zeroes_when_no_data` | Empty range returns all zeroes, correct count |
| 29 | `revenue_trend_fills_missing_dates_with_zero` | Revenue graph fills gaps with revenue=0 |
| 30 | `return_trend_excludes_cancelled_orders` | Cancelled orders do NOT appear in return_trend |
| 31 | `orders_trend_sum_matches_total_orders` | Graph sum == total orders count |

---

## Architecture

```
Request → StorePerformanceRequest (validation)
        → ReportController::getStorePerformance
        → ReportService::getStorePerformance
        → ReportRepository (8 summary queries + 7 graph queries)
        → JSON Response
```

**Files:**
- Validation: `Packages/Report/Src/Http/Requests/V3/StorePerformanceRequest.php`
- Controller: `Packages/Report/Src/Http/Controllers/V3/ReportController.php:334`
- Service: `Packages/Report/Src/Services/V3/ReportService.php:424`
- Repository: `Packages/Report/Src/Repositories/Eloquent/V3/ReportRepository.php`
- Routes: `Packages/Report/Routes/api.php:42`
- Tests: `tests/Feature/PerformanceDeliveryRateTest.php`
