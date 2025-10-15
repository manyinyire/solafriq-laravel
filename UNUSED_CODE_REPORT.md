# Unused Code Analysis Report
**Generated:** October 15, 2025  
**Project:** SolaFriq Laravel Application

---

## Executive Summary

This report identifies unused or potentially unused code in the Laravel application, including controllers, methods, APIs, events, and other components that may be safely removed to reduce technical debt and improve maintainability.

---

## 🔴 High Priority - Definitely Unused

### 1. Unused API Methods in `AuthController`

The following API methods exist but have **NO routes** defined for them:

| Method | Purpose | Status |
|--------|---------|--------|
| `register()` | API user registration | ❌ Unused (web version `webRegister()` is used) |
| `login()` | API user login | ❌ Unused (web version `webLogin()` is used) |
| `logout()` | API user logout | ❌ Unused (web version `webLogout()` is used) |
| `user()` | Get current user | ⚠️ Possibly used via `/api/user` route |
| `updateProfile()` | Update user profile via API | ❌ No route defined |
| `changePassword()` | Change password via API | ❌ No route defined |
| `forgotPassword()` | Send password reset link | ❌ No route defined |
| `resetPassword()` | Reset password | ❌ No route defined |
| `verifyEmail()` | API email verification | ❌ Unused (web version used) |
| `resendVerificationEmail()` | Resend verification email | ❌ Unused (web version used) |
| `deleteAccount()` | Delete user account | ❌ No route defined |

**Recommendation:** Remove these 10 unused methods from `AuthController` (lines ~29-340) or add proper API routes if they're intended for future use.

---

### 2. Unused Method in `CheckoutController`

| Method | Purpose | Status |
|--------|---------|--------|
| `success($orderId)` | Show checkout success page | ❌ No route defined |

**Recommendation:** Remove this method or add a route if needed for post-checkout success page.

---

## 🟡 Medium Priority - Review Needed

### 3. Events Usage

| Event | Used In | Status |
|-------|---------|--------|
| `OrderCreated` | `OrderProcessingService` | ✅ Used |
| `OrderUpdated` | `OrderProcessingService` | ✅ Used |

**Status:** Both events are properly used. No action needed.

---

### 4. Console Commands

| Command | Scheduled | Status |
|---------|-----------|--------|
| `GenerateMissingInvoices` | ❌ Not scheduled in `Kernel.php` | ⚠️ May be manually run |

**Recommendation:** Check if this command is run manually or via cron. If not used, consider removing it.

---

### 5. Request Classes

All Form Request classes are being used:

| Request Class | Used In | Status |
|---------------|---------|--------|
| `ConfirmPaymentRequest` | `OrderController::confirmPayment()` | ✅ Used |
| `ScheduleInstallationRequest` | `OrderController::scheduleInstallation()` | ✅ Used |
| `StoreOrderRequest` | `OrderController::store()` | ✅ Used |
| `UpdateOrderRequest` | `OrderController::update()` | ✅ Used |
| `UpdateOrderStatusRequest` | `OrderController::updateStatus()` | ✅ Used |

**Status:** All request classes are in use. No action needed.

---

### 6. Models

All models appear to be in use:

| Model | Status |
|-------|--------|
| `SystemLog` | ✅ Used in `DashboardController` |
| `CustomSystem` | ✅ Used (relationship in `User` model) |
| All other models | ✅ Used throughout the application |

**Status:** All models are in use. No action needed.

---

## 🔵 Low Priority - Architecture Issues

### 7. API Routes Organization

**Issue:** The `routes/api.php` file only contains ONE route:
```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

However, many API endpoints are defined in `routes/web.php` instead of `routes/api.php`:
- `/dashboard/stats`
- `/dashboard/recent-orders`
- `/orders-data`
- `/invoices`
- `/warranties-data`
- And many more...

**Recommendation:** Consider moving API endpoints from `web.php` to `api.php` for better organization and to follow Laravel conventions.

---

### 8. Missing API Functionality

The following functionality exists in the codebase but has no routes:

| Functionality | Impact |
|---------------|--------|
| Password Reset (API) | Users cannot reset passwords via API |
| Account Deletion (API) | Users cannot delete accounts via API |
| Profile Update (API) | Users cannot update profiles via API |
| Change Password (API) | Users cannot change passwords via API |

**Recommendation:** Either:
1. Remove the unused methods if not needed
2. Add proper routes if this functionality is required

---

## 📊 Summary Statistics

| Category | Total | Used | Unused | Usage % |
|----------|-------|------|--------|---------|
| AuthController Methods | 21 | 10 | 11 | 48% |
| CheckoutController Methods | 3 | 2 | 1 | 67% |
| Request Classes | 5 | 5 | 0 | 100% |
| Events | 2 | 2 | 0 | 100% |
| Models | 20 | 20 | 0 | 100% |

---

## 🎯 Action Items

### Immediate Actions (High Priority)

1. **Remove 11 unused methods from `AuthController`:**
   - `register()`, `login()`, `logout()`, `updateProfile()`, `changePassword()`
   - `forgotPassword()`, `resetPassword()`, `verifyEmail()`, `resendVerificationEmail()`, `deleteAccount()`
   - Keep `user()` as it's used by `/api/user` route

2. **Remove `CheckoutController::success()` method** if not needed

### Short-term Actions (Medium Priority)

3. **Review `GenerateMissingInvoices` command:**
   - Document if it's run manually
   - Schedule it in `Kernel.php` if needed
   - Remove if obsolete

### Long-term Actions (Low Priority)

4. **Reorganize API routes:**
   - Move API endpoints from `web.php` to `api.php`
   - Follow Laravel conventions for better maintainability

5. **Document missing functionality:**
   - Decide if password reset, account deletion, etc. are needed
   - Either implement routes or remove methods

---

## 💾 Estimated Code Reduction

By removing unused code:
- **~350 lines** from `AuthController`
- **~15 lines** from `CheckoutController`
- **Total: ~365 lines of unused code**

This represents approximately **5-7% reduction** in controller code, improving maintainability and reducing confusion for future developers.

---

## 🔍 How to Verify

Before removing any code, verify it's truly unused:

```bash
# Search for method usage across entire codebase
grep -r "methodName" app/ resources/ routes/

# Check if routes exist
php artisan route:list | grep "method-name"

# Run tests to ensure nothing breaks
php artisan test
```

---

## ✅ Conclusion

The codebase is generally well-maintained with most components in active use. The main issues are:

1. **11 unused API methods in AuthController** - Safe to remove
2. **1 unused method in CheckoutController** - Safe to remove
3. **API routes organization** - Architectural improvement opportunity

Removing the identified unused code will improve code quality, reduce maintenance burden, and make the codebase easier to understand for new developers.
