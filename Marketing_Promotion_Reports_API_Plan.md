# Marketing & Promotion Reports API — Planning Document

## 1. Context & Purpose

The seller dashboard needs a **"Marketing & Promotion"** report page that shows discount/coupon campaign performance. Currently the Report package has zero marketing analytics — the `worthOfCoupons` field in existing reports is hardcoded to `0`. These new APIs will power the UI showing campaign lists, spending, revenue, units sold, product breakdowns, comparison charts, and gross profit.

---

## 2. Data Sources & How We Collect Data

### Two Discount Types, Two Data Paths

| | **Sales (automatic discounts)** | **Coupons (code-based)** |
|---|---|---|
| **Tracking table** | `order_items.sale_id` → links to `discounts.id` | `discount_usages` → links `discount_id` to `order_id` |
| **Spending calculation** | `SUM((price - sold_price) * quantity)` from `order_items` | Calculate per order from discount rules: percent → `(subtotal * value / 100)`, fixed → `discount_value` |
| **Revenue calculation** | `SUM(sold_price * quantity)` from `order_items` | `SUM(total_amount)` from `store_orders` via `discount_usages` |
| **Units sold** | `SUM(quantity)` from `order_items WHERE sale_id = X` | `SUM(quantity)` from `order_items` for orders in `discount_usages` |

### Key Tables Involved

```
discounts                  — Campaign definitions (store_id, type, value, dates, status)
discount_applicables       — Which products/categories a discount applies to (polymorphic)
discount_usages            — Coupon usage log (discount_id → order_id → user_id)
store_orders               — Orders (total_amount, shipping_amount, charge_status, order_status)
order_items                — Line items (price, sold_price, sale_id, quantity, item_id)
products                   — Product details (title, slug, product_id)
categories                 — Category names (id, categoryTitle)
```

### Order Filtering (applied everywhere)

All queries filter orders with:
- `charge_status = 'paid'`
- `order_status NOT IN ('refunded', 'cancelled')`

---

## 3. API Endpoints

### Endpoint 1: `GET /api/v3/report/marketing/campaigns`

**Purpose:** Sidebar campaign list + all-time totals

**Params:**
- `type` (optional): `sale` | `coupon` | `all` (default: `all`)

**Data collection:**
- **Current campaigns:** `SELECT * FROM discounts WHERE store_id=X AND status='active' AND state IN ('live','scheduled') AND archive=0`
- **Past campaigns:** `SELECT * FROM discounts WHERE store_id=X AND (status IN ('expired','inActive') OR state='expired') AND archive=0 ORDER BY end_time DESC`
- **All-time sale spending:** `SUM((oi.price - oi.sold_price) * oi.quantity)` from `order_items oi JOIN store_orders so ON oi.order_id = so.order_id JOIN discounts d ON oi.sale_id = d.id WHERE so.store_id=X AND so.charge_status='paid' AND sale_id > 0`
- **All-time coupon spending:** Iterate each coupon discount for the store, sum calculated spending from `discount_usages JOIN store_orders`

**Response:**
```json
{
  "status": true,
  "data": {
    "current_campaigns": [
      {
        "id": 15,
        "title": "Summer Sale",
        "type": "sale",
        "discount_value_type": "percent",
        "discount_value": 5,
        "start_time": "2026-01-01",
        "end_time": "2026-03-01",
        "state": "live"
      }
    ],
    "past_campaigns": [
      {
        "id": 12,
        "title": "Black Friday",
        "type": "coupon",
        "discount_value_type": "fixed",
        "discount_value": 10,
        "start_time": "2025-11-25",
        "end_time": "2025-11-30",
        "state": "expired"
      }
    ],
    "all_time_summary": {
      "total_discount_given": 14500.00,
      "total_revenue": 320045.50,
      "total_gross_profit": 305545.50
    }
  }
}
```

---

### Endpoint 2: `GET /api/v3/report/marketing/campaigns/{id}`

**Purpose:** Detailed metrics for a single campaign (the main detail panel)

**Data collection:**

1. **Fetch campaign:** `discounts WHERE id=:id AND store_id=X`

2. **Category label:** From `discount_applicables WHERE discount_id=:id AND excluded=0`:
   - Empty → `"Whole Inventory"`
   - Has `applicable_type='category'` → fetch category names from `categories` table
   - Has `applicable_type='product'` → `"Selected Products (N)"`

3. **Metrics (sale type):**
```sql
SELECT
  SUM((oi.price - oi.sold_price) * oi.quantity) AS spending,
  SUM(oi.sold_price * oi.quantity) AS revenue,
  SUM(oi.quantity) AS units_sold,
  COUNT(DISTINCT so.order_id) AS orders_count
FROM order_items oi
JOIN store_orders so ON oi.order_id = so.order_id
WHERE oi.sale_id = :discount_id
  AND so.store_id = :store_id
  AND so.charge_status = 'paid'
  AND so.order_status NOT IN ('refunded','cancelled')
  AND oi.sold_price IS NOT NULL
```

4. **Metrics (coupon type):**
```sql
-- Step 1: Get orders where coupon was used
SELECT so.order_id, so.total_amount, so.shipping_amount
FROM discount_usages du
JOIN store_orders so ON du.order_id = so.order_id
WHERE du.discount_id = :discount_id
  AND so.store_id = :store_id
  AND so.charge_status = 'paid'
  AND so.order_status NOT IN ('refunded','cancelled')
```
Then per order:
- `subtotal = total_amount - shipping_amount`
- If percent: `discount_amount = subtotal * discount_value / 100`
- If fixed: `discount_amount = discount_value`
- Cap at subtotal: `discount_amount = min(discount_amount, subtotal)`
- Units sold: `SUM(quantity)` from `order_items` for those order IDs

5. **Revenue change %:**
   - Campaign period: `start_time` to `end_time` (or now if still active)
   - Prior period: same duration before `start_time`
   - `change = ((campaign_revenue - prior_revenue) / prior_revenue) * 100`

6. **Gross profit:** `revenue - spending`

**Response:**
```json
{
  "status": true,
  "data": {
    "campaign": {
      "id": 15,
      "title": "Summer Sale",
      "code": null,
      "type": "sale",
      "discount_value_type": "percent",
      "discount_value": 5,
      "start_time": "2026-01-01",
      "end_time": "2026-03-01",
      "status": "active",
      "state": "live",
      "category": "Whole Inventory"
    },
    "metrics": {
      "total_discount_given": 1400.00,
      "revenue_generated": 32000.45,
      "revenue_change_percentage": 12.5,
      "total_units_sold": 450,
      "total_orders": 120,
      "gross_profit": 30600.45
    },
    "comparison": {
      "spending": 1400.00,
      "revenue": 32000.45
    }
  }
}
```

---

### Endpoint 3: `GET /api/v3/report/marketing/campaigns/{id}/products`

**Purpose:** Paginated products sold under a specific campaign

**Params:** `page` (default 1), `per_page` (default 20)

**Data collection (sale type):**
```sql
SELECT p.product_id, p.title, p.slug,
  SUM(oi.quantity) AS units_sold,
  SUM(oi.sold_price * oi.quantity) AS revenue,
  SUM((oi.price - oi.sold_price) * oi.quantity) AS discount_given
FROM order_items oi
JOIN store_orders so ON oi.order_id = so.order_id
JOIN products p ON oi.item_id = p.product_id
WHERE oi.sale_id = :discount_id
  AND so.store_id = :store_id
  AND so.charge_status = 'paid'
  AND so.order_status NOT IN ('refunded','cancelled')
GROUP BY p.product_id, p.title, p.slug
ORDER BY revenue DESC
```

**Data collection (coupon type):**
- Get `order_ids` from `discount_usages WHERE discount_id=:id`
- Then same query but `WHERE oi.order_id IN (:order_ids)` instead of `sale_id` filter

**Response:**
```json
{
  "status": true,
  "data": {
    "products": [
      {
        "product_id": 123,
        "title": "Cotton T-Shirt",
        "slug": "cotton-t-shirt",
        "units_sold": 25,
        "revenue": 625.00,
        "discount_given": 31.25
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 20,
      "total": 55
    }
  }
}
```

---

### Endpoint 4: `GET /api/v3/report/marketing/campaigns/{id}/chart`

**Purpose:** Revenue vs Spending comparison graph data over time

**Params:** `granularity` (optional: `daily` | `monthly`, default `monthly`)

**Data collection (sale type):**
```sql
SELECT DATE_FORMAT(so.created_at, '%Y-%m') AS period,
  SUM(oi.sold_price * oi.quantity) AS revenue,
  SUM((oi.price - oi.sold_price) * oi.quantity) AS spending
FROM order_items oi
JOIN store_orders so ON oi.order_id = so.order_id
WHERE oi.sale_id = :discount_id
  AND so.store_id = :store_id
  AND so.charge_status = 'paid'
  AND so.order_status NOT IN ('refunded','cancelled')
GROUP BY DATE_FORMAT(so.created_at, '%Y-%m')
ORDER BY period
```
*(Use `%Y-%m-%d` for daily granularity)*

**Data collection (coupon type):**
```sql
SELECT DATE_FORMAT(so.created_at, '%Y-%m') AS period,
  SUM(so.total_amount) AS revenue,
  SUM(so.total_amount - COALESCE(so.shipping_amount,0)) AS subtotal_sum,
  COUNT(*) AS order_count
FROM discount_usages du
JOIN store_orders so ON du.order_id = so.order_id
WHERE du.discount_id = :discount_id
  AND so.store_id = :store_id
  AND so.charge_status = 'paid'
  AND so.order_status NOT IN ('refunded','cancelled')
GROUP BY DATE_FORMAT(so.created_at, '%Y-%m')
ORDER BY period
```
Then calculate spending per period:
- Percent: `subtotal_sum * discount_value / 100`
- Fixed: `discount_value * order_count`

**Response:**
```json
{
  "status": true,
  "data": {
    "chart_data": [
      { "period": "2026-01", "revenue": 8500.00, "spending": 350.00 },
      { "period": "2026-02", "revenue": 12000.45, "spending": 520.00 }
    ]
  }
}
```

---

### Endpoint 5: `GET /api/v3/report/marketing/summary`

**Purpose:** Overall marketing summary across all campaigns with date filter

**Params:**
- `filter`: `today` | `last_7_days` | `this_month` | `last_month` | `last_90_days` | `year` | `custom`
- `start_date` (required if filter=custom): `Y-m-d`
- `end_date` (required if filter=custom): `Y-m-d`
- `type` (optional): `sale` | `coupon` | `all`

**Data collection:**
- Aggregate sale spending across ALL discounts within date range
- Aggregate coupon spending across ALL coupons within date range
- Count total campaigns, active campaigns
- Generate period chart data grouped by month across all campaigns

**Response:**
```json
{
  "status": true,
  "data": {
    "summary": {
      "total_discount_given": 14500.00,
      "total_revenue": 320045.50,
      "total_gross_profit": 305545.50,
      "total_campaigns": 8,
      "active_campaigns": 1,
      "total_orders_with_discounts": 1240,
      "total_units_sold": 3500
    },
    "chart_data": [
      { "period": "2025-06", "revenue": 45000.00, "spending": 2200.00 }
    ]
  }
}
```

---

## 4. Files to Create / Modify

### New Files (4 Form Requests)

All follow the exact pattern of `Packages/Report/Src/Http/Requests/V3/FinanceReportRequest.php`:

| File | Validation Rules |
|------|-----------------|
| `Packages/Report/Src/Http/Requests/V3/MarketingCampaignsRequest.php` | `type: nullable\|in:sale,coupon,all` |
| `Packages/Report/Src/Http/Requests/V3/MarketingSummaryRequest.php` | `filter: nullable\|in:today,last_7_days,...`, `start_date`, `end_date`, `type` |
| `Packages/Report/Src/Http/Requests/V3/CampaignChartRequest.php` | `granularity: nullable\|in:daily,monthly` |
| `Packages/Report/Src/Http/Requests/V3/CampaignProductsRequest.php` | `page: nullable\|integer\|min:1`, `per_page: nullable\|integer\|min:1\|max:100` |

### Modified Files

| File | Changes |
|------|---------|
| **`Packages/Report/Routes/api.php`** | Add 5 routes inside existing `api/v3 > report > auth:api` group |
| **`Packages/Report/Src/Http/Controllers/V3/ReportController.php`** | Add 5 methods following `getShippingSummary` pattern |
| **`Packages/Report/Src/Services/V3/ReportService.php`** | Add 5 methods: orchestration + sale vs coupon branching |
| **`Packages/Report/Src/Repositories/Eloquent/V3/ReportRepository.php`** | Add ~12 query methods (campaign listing, metric calculations, chart data) |

### Models Referenced (no changes needed)

- `Packages/Discount/Src/Models/V3/Discount.php`
- `Packages/Discount/Src/Models/V3/DiscountUsage.php`
- `Packages/Discount/Src/Models/V3/DiscountApplicable.php`
- `Packages/Home/Models/V3/Store.php`
- `Packages/Order/Models/V3/StoreOrder.php`

---

## 5. Routes to Add

```php
// Marketing & Promotion Reports (inside existing api/v3 > report > auth:api group)
Route::get('/marketing/campaigns', [ReportControllerV3::class, 'getMarketingCampaigns']);
Route::get('/marketing/campaigns/{id}', [ReportControllerV3::class, 'getCampaignDetail']);
Route::get('/marketing/campaigns/{id}/products', [ReportControllerV3::class, 'getCampaignProducts']);
Route::get('/marketing/campaigns/{id}/chart', [ReportControllerV3::class, 'getCampaignChart']);
Route::get('/marketing/summary', [ReportControllerV3::class, 'getMarketingSummary']);
```

---

## 6. Implementation Order

1. Create 4 Form Request classes
2. Add ~12 repository query methods to `ReportRepository.php` (largest change, ~250 lines)
3. Add 5 service methods to `ReportService.php` (~100 lines)
4. Add 5 controller methods to `ReportController.php` (~80 lines)
5. Add 5 routes to `Routes/api.php`

---

## 7. Known Limitation

Coupon discount amounts are **not stored** on orders or `discount_usages`. We calculate them from discount rules + order subtotals. This is approximate for percentage coupons since checkout uses cart total (which may differ slightly from `total_amount - shipping_amount`). For future accuracy, a `discount_amount` column could be added to the `discount_usages` table.

---

## 8. UI to API Mapping

| UI Element | API Source |
|------------|-----------|
| Current Campaign (highlighted) | Endpoint 1 → `current_campaigns[0]` |
| Past Campaigns list | Endpoint 1 → `past_campaigns` |
| Discounts / Coupons tabs | Endpoint 1 → `type` param (`sale` / `coupon`) |
| Total Discount Given | Endpoint 2 → `metrics.total_discount_given` |
| Revenue Generated From Discounts | Endpoint 2 → `metrics.revenue_generated` |
| Revenue % change arrow | Endpoint 2 → `metrics.revenue_change_percentage` |
| From / To dates | Endpoint 2 → `campaign.start_time` / `campaign.end_time` |
| Type label (e.g. "Discount - Flat 5%") | Endpoint 2 → `campaign.type` + `campaign.discount_value_type` + `campaign.discount_value` |
| Category label | Endpoint 2 → `campaign.category` |
| Total Units Sold | Endpoint 2 → `metrics.total_units_sold` |
| Products Sold (Click To View) | Endpoint 3 → paginated product list |
| Comparison bars (Spending / Revenue) | Endpoint 2 → `comparison.spending` / `comparison.revenue` |
| Comparison Graph (Revenue vs Spending) | Endpoint 4 → `chart_data` array |
| Gross Profit | Endpoint 2 → `metrics.gross_profit` |
| Sidebar: Total Discount | Endpoint 1 → `all_time_summary.total_discount_given` |
| Sidebar: Total Revenue | Endpoint 1 → `all_time_summary.total_revenue` |
| Sidebar: Total Gross Profit | Endpoint 1 → `all_time_summary.total_gross_profit` |
