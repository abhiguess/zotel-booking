# NOTES — Design Decisions & Thinking

---

## Frontend: Blade + Tailwind CSS + Alpine.js

---

## How Search Works

1. User submits check_in, check_out, adults → validated via FormRequest
2. Calculate nights (date diff) and days_until_checkin (from today)
3. For each room type, fetch daily_inventory rows WHERE date >= check_in AND date < check_out
4. Availability check:
   - All dates in the range MUST have inventory rows (missing row = unavailable)
   - Every date must have (total_rooms - booked_rooms) >= 1
   - No date can be blocked (is_blocked = true)
   - available_rooms = MIN(total_rooms - booked_rooms) across all dates
   - One sold-out date kills the entire room type for that search
5. Pricing:
   - Pick price column based on adults count (price_1p / price_2p / price_3p)
   - Room Only total = SUM of nightly room rates
   - Breakfast total = SUM of (breakfast_price_pp × adults) per night
   - Resolve best discount (see below) and apply to each total
6. Return both room types with availability status, pricing for both meal plans, and nightly breakdown

---

## Overbooking Prevention

The booked_rooms counter approach requires atomic updates to prevent race conditions when two guests try to book the last room simultaneously.

Approach: 
UPDATE daily_inventory 
SET booked_rooms = booked_rooms + 1 
WHERE id = ? AND booked_rooms < total_rooms

The WHERE clause acts as an atomic guard. If affected_rows = 0, the room was taken between search and booking attempt — return a "no longer available" error.

For multi-night bookings, wrap all date updates in a single transaction with SELECT ... FOR UPDATE on all inventory rows for the date range. If any single date fails, rollback the entire transaction.

---

## How Discount Resolution Works

Two discount types exist: long_stay (based on number of nights) and last_minute (based on proximity of check-in to today).

Resolution logic:
1. Find best long_stay: query active rules WHERE type = 'long_stay' AND min_nights <= nights, pick the one with highest discount_percentage
   - Example: 3-night stay qualifies for "3+ nights = 10%". A 7-night stay qualifies for both "3+ = 10%" and "6+ = 20%" — the 20% wins.

2. Find best last_minute: query active rules WHERE type = 'last_minute' AND within_days >= days_until_checkin, pick highest discount_percentage
   - Example: check-in is tomorrow (1 day away), rule says "within 3 days = 5%" — 1 <= 3, so it qualifies.

3. Compare the two winners: pick the ONE with the higher percentage. 
   - If equal, prefer long_stay (arbitrary but consistent tie-breaker).
   - If neither qualifies, no discount.

Why best-single-wins instead of stacking:
- Stacking creates compound discounts that are hard to audit (10% + 5% = 14.5%, not 15%)
- Simpler for guests to understand ("You saved 20% with Long Stay")
- Easier to reason about revenue impact
- Industry standard for most hotel systems

The discount applies to the entire stay total (room rate + breakfast if selected), not per-night. This matches how OTAs display pricing — one clean discount on the final amount.
