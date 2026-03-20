# Notes - Round 2

## Key change

Pulled pricing out of `daily_inventory` into its own table (`rate_plan_daily_rates`). Inventory now only tracks availability. Pricing is per rate plan, per date, per occupancy — occupancy is a row now, not a column. Adding a 4th or 5th adult tier is just inserting rows, no migration.

## Rate plans

Each room type has its own plans (Standard → EP, CP; Deluxe → CP, MAP). Prices are all-inclusive per plan — CP for 2 adults = ₹3,300/night includes room + breakfast. Keeps the search query simple.

## Discounts

Now linked to specific rate plans via FK. Same search shows EP at 5% off, CP at 10% off. `rate_plan_id` is nullable so global discounts still work if needed.

Early bird threshold is 7 days — stored in `min_days_before` column, configurable without code changes.

## Migrations

Incremental, not fresh rebuild. Created new tables first, then dropped old pricing columns from daily_inventory, then extended discount_rules. Each step reversible.

## Occupancy

4-adult search still shows Standard Room but with "exceeds max occupancy" message instead of hiding it. Felt like better UX. The check is in search logic, not validation — validation allows 1-4, per-room filtering is business logic.