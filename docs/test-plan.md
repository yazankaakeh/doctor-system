# OMCS — Test Plan and Test Cases

**Project:** Online Medical Consultation System (OMCS)
**Author:** Yazan Kaakeh (2524815)
**Module:** SWE6010
**Version / Date:** 1.0 — April 2026
**Status:** Evidence of testing, for supervisor review

---

## 1. Purpose and Scope

This document describes how OMCS was tested and records the cases used as evidence. Testing covers functional requirements (authentication, booking, payment, consultation, records, admin) and key non-functional requirements (security, performance, multilingual RTL). Penetration testing and enterprise-scale load testing are out of scope.

## 2. Test Approach

| Layer | Tool | Purpose |
|-------|------|---------|
| Static analysis | PHPStan (Larastan) level 0 | Undefined classes, missing returns, hard bugs |
| Style | Laravel Pint | PSR-12 enforcement |
| Security | `composer audit` | Known vendor vulnerabilities |
| Translations | `artisan translations:integrity-check` | Same key set in en / ar / tr |
| Unit | PHPUnit 12 + SQLite `:memory:` | Enums, models, repositories, actions |
| Feature | PHPUnit HTTP kit | Route + middleware + view end-to-end |
| Manual / UAT | Browser on local + VPS | Full user journeys (video, payment, RTL) |

All automated jobs run on every push via GitHub Actions (`.github/workflows/ci.yml`). The build is red if any job fails.

## 3. Test Environment

**Dev:** Windows + Laragon, PHP 8.4, MySQL 8, Redis, Laravel 12, Livewire 3, Reverb, Jitsi at `meet.yupcrm.com`.
**CI:** `ubuntu-latest`, PHP 8.4, SQLite `:memory:`, Vite stubbed via `$this->withoutVite()`.

## 4. Exit Criteria

- All PHPUnit tests pass (currently **86 passed, 168 assertions**).
- Pint, PHPStan, `composer audit`, translations check all green.
- Every manual case below marked **Passed** or **Passed with observation**.
- No open critical or high defects.

---

## 5. Automated Test Case Summary

| Class | Tests | Type | Result |
|-------|-------:|------|:------:|
| `Tests\Unit\ExampleTest` | 1 | Unit | Pass |
| `AdminManagement\ActiveAdminEnumTest` | 7 | Unit | Pass |
| `AdminManagement\AdminModelTest` | 7 | Unit | Pass |
| `AdminManagement\AuditLogModelTest` | 9 | Unit | Pass |
| `AdminManagement\AuditLogTraitTest` | 9 | Unit | Pass |
| `AdminManagement\DoctorRepositoryTest` | 6 | Unit | Pass |
| `AdminManagement\RoleRepositoryTest` | 7 | Unit | Pass |
| `Tests\Feature\ExampleTest` | 1 | Feature | Pass |
| `AdminManagement\AuditLogTest` | 12 | Feature | Pass |
| `AdminManagement\RoleManagementTest` | 15 | Feature | Pass |
| `AdminManagement\UserManagementTest` | 12 | Feature | Pass |
| **Total** | **86** | — | **Pass** |

### 5.1 Representative unit tests (selected)

| ID | Test | What it verifies |
|----|------|------------------|
| UT-01 | ActiveAdminEnum values | Enum maps Active→1, Deactive→0 |
| UT-09 | Admin password is hashed | Stored password is bcrypt, never plaintext |
| UT-11 | `is_active` cast to enum | Eloquent cast honours the enum type |
| UT-17 | AuditLog payload cast | JSON column round-trips as array |
| UT-24 | `auditLogs()` relation | Trait adds MorphMany to the host model |
| UT-34 | DoctorRepository::store | Persists doctor + attaches selected role |
| UT-40 | RoleRepository::store | Persists role + syncs permissions |

### 5.2 Representative feature tests (selected)

| ID | Test | What it verifies |
|----|------|------------------|
| FT-01 | Guest cannot access audit log | Middleware redirects to login |
| FT-07 | `getPayload` returns JSON | `{status, html, payload}` envelope |
| FT-16 | Admin creates role | Role + permissions persisted via HTTP |
| FT-20–23 | Role validation | Rules enforce name, uniqueness, permissions |
| FT-30 | Admin creates doctor | Doctor row created, role attached |
| FT-32 | Admin toggles doctor status | `is_active` flips via HTTP |
| FT-34 | Unique email validation | Duplicate email rejected on create |
| FT-36 | Age validation | Age outside 18–100 rejected |

---

## 6. Manual / UAT Test Cases

All cases passed on the final test cycle (April 2026).

### 6.1 Patient booking journey

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-01 | Register with email | Patient row created, email verified | Pass |
| UAT-02 | Register via Google | Social account linked, redirected to dashboard | Pass |
| UAT-03 | Browse doctors | List renders with specialties | Pass |
| UAT-04 | Pick doctor and slot | Wizard advances with pre-selection | Pass |
| UAT-05 | Booking summary | Shows doctor, date, duration, price | Pass |
| UAT-06 | PayPal payment | Booking → Confirmed, Payment row created | Pass |
| UAT-07 | Credit-card Luhn prototype | Payment row with last4 + brand | Pass |
| UAT-08 | Offline with proof upload | Booking → PendingApproval, visible to admin | Pass |
| UAT-09 | Booking notification | Both parties receive within 5 s | Pass |

### 6.2 Video consultation

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-10 | Patient joins call | Jitsi iframe renders with correct doctor name | Pass |
| UAT-11 | Doctor joins call | Iframe next to examination editor | Pass |
| UAT-12 | Frame-ancestors allows embed | No X-Frame-Options error in console | Pass |
| UAT-13 | Chat during call | Messages delivered under 1 s via Reverb | Pass |

### 6.3 Doctor medical examination

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-14 | Vital signs in range | Saved without warning | Pass |
| UAT-15 | Vital signs out of range | Out-of-range indicator shown | Pass |
| UAT-16 | Add medicines | Dose + form linked to examination | Pass |
| UAT-17 | Add medical tests | Lab + radiology tests persisted | Pass |
| UAT-18 | Upload lab report | File stored in Spatie Media | Pass |
| UAT-19 | Generate PDF prescription | PDF downloaded with clinic header | Pass |
| UAT-20 | Re-open examination | All tabs load saved data | Pass |

### 6.4 Administration

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-21 | Admin login | Dashboard loads under admin guard | Pass |
| UAT-22 | Approve offline payment | Booking flips to Confirmed | Pass |
| UAT-23 | Toggle doctor status | Deactivated doctor cannot log in | Pass |
| UAT-24 | Audit log filter | Filter by method + route works | Pass |
| UAT-25 | Assign role | Permissions reflect on next request | Pass |

### 6.5 Multilingual and RTL

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-26 | Arabic RTL layout | Entire UI flips; sidebar on right | Pass |
| UAT-27 | Examination editor RTL | Tabs render correctly, no horizontal scroll | Pass |
| UAT-28 | Turkish locale | UI translated, LTR preserved | Pass |
| UAT-29 | Translations integrity | Zero missing keys across en / ar / tr | Pass |

### 6.6 Security / GDPR

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-30 | Guest blocked from doctor portal | Redirect to login | Pass |
| UAT-31 | Patient A cannot read Patient B | 403 on crafted URL | Pass |
| UAT-32 | Authorised media only | External session cannot open file | Pass |
| UAT-33 | Audit log on download | Row created with user + target | Pass |
| UAT-34 | CSRF protection | 419 when token missing | Pass |

### 6.7 Performance smoke

| ID | Title | Expected | Status |
|----|-------|----------|:------:|
| UAT-35 | Examination editor load | First paint under 2 s on local | Pass |
| UAT-36 | Livewire latency | Roundtrip under 300 ms | Pass |
| UAT-37 | Reverb chat latency | Message under 1 s | Pass |

---

## 7. Defects Found and Fixed

| # | Area | Issue | Resolution |
|---|------|-------|------------|
| 1 | Auth | `Auth guard [admin] is not defined` | Guard detection skips undefined guards |
| 2 | Routing | `Route [home] not defined` on logout | `Route::has()` fallback |
| 3 | DB | `admins.phone` / `permissions.section` NOT NULL blocked seed | Relax migration (2026-04-18) |
| 4 | Tests | `NotificationFake` not bound to Dispatcher | Bound in base `TestCase::setUp()` |
| 5 | i18n | Stale `App\Enum\Gender` in `tr/doctor.php` | Rewrote file with integer keys |
| 6 | Audit | Timestamps overwritten | Added to `$fillable` |
| 7 | Video | `membersOnly` on public Jitsi | Self-hosted at `meet.yupcrm.com` |
| 8 | Embed | `X-Frame-Options: SAMEORIGIN` blocked iframe | CSP `frame-ancestors` per vhost |
| 9 | Admin toggle | Validated against `admins,id` | Changed to `doctors,id` |
| 10 | CI | `Vite manifest not found` in tests | `$this->withoutVite()` in base `TestCase` |
| 11 | CI | PHPUnit exit 1 despite all passing | CI runs `vendor/bin/phpunit` directly |

---

## 8. Sign-off

| Role | Name | Signature / Date |
|------|------|------------------|
| Student (author) | Yazan Kaakeh | |
| Supervisor | | |

*End of document.*
