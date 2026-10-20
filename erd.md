# PadBro ERD Specification (Laravel + Filament + Flutter)

> Booking model uses **fixed slot granularity** (default: **60 minutes**).
> Availability is derived from:
> `court_time_slots` (templates) minus `court_booking_slots` (locked/booked).

## Conventions
- All tables include: `created_at`, `updated_at`
- All tables include Soft Deletes: `deleted_at` (nullable)
- User roles/permissions are handled via **Spatie Laravel Permission** (roles tables not included here).
- Money fields use **integer** amounts (IDR). Avoid float.

---

## Tables

## 1) users
Application users (customers + managers/admin) managed via Spatie Roles.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string | hashed |
| gender | string nullable | optional |
| phone | string nullable | |
| photo | string nullable | path/url |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | soft delete |

**Indexes**
- UNIQUE(email)

---

## 2) cities
City master data.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| photo | string nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

---

## 3) categories
Court category (e.g., indoor/outdoor).

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| photo | string nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

---

## 4) courts
Courts listing + attributes.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| about | text nullable | |
| thumbnail | string nullable | |
| category_id | bigint FK | -> categories.id |
| city_id | bigint FK | -> cities.id |
| photos | json nullable | array of image urls/paths |
| features | json nullable | array of strings |
| phone | string nullable | |
| material | string | MVP: `grass` or `cement` (consider normalizing later) |
| price | bigint | price per hour (IDR) |
| status | string | e.g. `draft`, `active` |
| address | text nullable | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

**Indexes**
- INDEX(category_id)
- INDEX(city_id)
- INDEX(status)

**FK**
- courts.category_id -> categories.id
- courts.city_id -> cities.id

---

## 5) court_time_slots
Weekly recurring availability template (manager CRUD).
This table defines *potential availability*.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| court_id | bigint FK | -> courts.id |
| day_of_week | tinyint | 0-6 (Sun-Sat) or 1-7 (define consistently) |
| start_time | time | template start |
| end_time | time | template end |
| is_active | boolean | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

**Indexes**
- INDEX(court_id)
- INDEX(day_of_week)
- Optional UNIQUE(court_id, day_of_week, start_time, end_time) to prevent duplicates

**FK**
- court_time_slots.court_id -> courts.id

---

## 6) court_bookings
Booking transaction header (customer -> manager approval).
This stores immutable snapshot values at booking time.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| court_id | bigint FK | -> courts.id |
| user_id | bigint FK | -> users.id |
| total_hours | int | usually equals count(slots) |
| total_tax_amount | bigint | snapshot |
| price_per_hour_amount | bigint | snapshot from courts.price |
| grand_total_amount | bigint | snapshot |
| status | string | payment lifecycle e.g. `pending`, `paid`, `failed` *(recommended to add `locked/expired/cancelled` later)* |
| proof_of_payment | string nullable | image path/url |
| start_at | datetime | booking start (summary) |
| end_at | datetime | booking end (summary, exclusive end recommended) |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

**Indexes**
- INDEX(court_id)
- INDEX(user_id)
- INDEX(status)
- INDEX(start_at)

**FK**
- court_bookings.court_id -> courts.id
- court_bookings.user_id -> users.id

> Note: For strict lock workflow, consider adding `locked_until` (datetime) in future iterations.

---

## 7) court_booking_slots
Reservation truth table for concurrency safety.
Each row represents a single reserved slot block (e.g. 1 hour).
This table is what prevents double-booking.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| slot_at | datetime | start datetime of slot (e.g. 2026-01-18 13:00:00) |
| court_id | bigint FK | -> courts.id (denormalized for indexing) |
| court_booking_id | bigint FK | -> court_bookings.id |
| status | string | `locked` or `booked` (recommended) |
| expires_at | datetime nullable | used only for `locked` slots |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | |

**Constraints**
- UNIQUE(court_id, slot_at)  ✅ critical

**Indexes**
- INDEX(court_booking_id)
- INDEX(court_id, slot_at)

**FK**
- court_booking_slots.court_id -> courts.id
- court_booking_slots.court_booking_id -> court_bookings.id

---

## Relationships Summary
- cities (1) -> (many) courts
- categories (1) -> (many) courts
- courts (1) -> (many) court_time_slots
- users (1) -> (many) court_bookings
- courts (1) -> (many) court_bookings
- court_bookings (1) -> (many) court_booking_slots
- courts (1) -> (many) court_booking_slots

---

## Booking Workflow Summary (High Level)
1. Manager defines templates in `court_time_slots`.
2. Customer requests availability for a date:
   - Build possible slot list from `court_time_slots` for that weekday
   - Remove reserved/locked slots from `court_booking_slots` where `expires_at > now()` or status=`booked`
3. Customer initiates booking:
   - Create `court_bookings` row (snapshot totals)
   - Insert N rows in `court_booking_slots` for each hour (slot granularity)
   - Unique(court_id, slot_at) prevents double booking
4. Payment proof uploaded:
   - Update `court_bookings.status`
   - Update `court_booking_slots.status` to `booked` (or keep locked + extend expiry, per your rule)

---
 