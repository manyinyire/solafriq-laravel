# API Routes Quick Reference Guide

## 📍 Base URLs

- **Web Application:** `https://yoursite.com/`
- **REST API:** `https://yoursite.com/api/v1/`
- **Admin API:** `https://yoursite.com/api/v1/admin/`

---

## 🔓 Public API Endpoints (No Authentication)

### Custom Builder
```
GET  /api/v1/custom-builder/products
POST /api/v1/custom-builder/calculate
POST /api/v1/custom-builder/validate
```

---

## 🔐 Authenticated API Endpoints (Requires Token)

**Authentication:** Add header `Authorization: Bearer {your_token}`

### User
```
GET /api/v1/user
```

### Dashboard
```
GET /api/v1/dashboard/stats
GET /api/v1/dashboard/recent-orders
GET /api/v1/dashboard/installment-summary
GET /api/v1/dashboard/warranty-summary
```

### Orders
```
GET /api/v1/orders
GET /api/v1/orders/{order}
PUT /api/v1/orders/{order}
```

### Installment Plans
```
GET    /api/v1/installment-plans
POST   /api/v1/installment-plans
GET    /api/v1/installment-plans/{id}
PUT    /api/v1/installment-plans/{id}
DELETE /api/v1/installment-plans/{id}
POST   /api/v1/installment-plans/{id}/payments/{payment}/pay
```

### Warranties
```
GET  /api/v1/warranties
GET  /api/v1/warranties/{warranty}
GET  /api/v1/warranties/{warranty}/certificate
POST /api/v1/warranties/{warranty}/claims
GET  /api/v1/warranty-claims
```

### Custom Builder (Authenticated)
```
POST /api/v1/custom-builder/save
GET  /api/v1/custom-builder/saved
POST /api/v1/custom-builder/add-to-cart
```

---

## 👑 Admin API Endpoints (Requires Admin Token)

**Authentication:** Add header `Authorization: Bearer {admin_token}`

### Dashboard
```
GET /api/v1/admin/dashboard/overview
GET /api/v1/admin/dashboard/sales-analytics
GET /api/v1/admin/dashboard/system-metrics
```

### Orders Management
```
GET  /api/v1/admin/orders
GET  /api/v1/admin/orders/{order}
PUT  /api/v1/admin/orders/{order}
PUT  /api/v1/admin/orders/{order}/accept
PUT  /api/v1/admin/orders/{order}/decline
PUT  /api/v1/admin/orders/{order}/confirm-payment
PUT  /api/v1/admin/orders/{order}/status
PUT  /api/v1/admin/orders/{order}/schedule-installation
PUT  /api/v1/admin/orders/{order}/tracking
POST /api/v1/admin/orders/{order}/notes
POST /api/v1/admin/orders/{order}/refund
POST /api/v1/admin/orders/{order}/resend-notification
GET  /api/v1/admin/orders/{order}/invoice-pdf
GET  /api/v1/admin/installations
```

### Installment Plans
```
GET /api/v1/admin/installment-plans
PUT /api/v1/admin/installment-plans/{id}
```

### Warranties
```
GET  /api/v1/admin/warranties
GET  /api/v1/admin/warranties/statistics
GET  /api/v1/admin/warranties/eligible-orders
POST /api/v1/admin/warranties/create-for-order/{order}
PUT  /api/v1/admin/warranty-claims/{claim}
```

---

## 🔑 Authentication

### Get API Token

**Login to get token:**
```bash
curl -X POST https://yoursite.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

**Use token in requests:**
```bash
curl https://yoursite.com/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## 📝 Example Requests

### Get Products (Public)
```bash
curl https://yoursite.com/api/v1/custom-builder/products
```

### Get User Info (Authenticated)
```bash
curl https://yoursite.com/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Dashboard Stats (Authenticated)
```bash
curl https://yoursite.com/api/v1/dashboard/stats \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Get Orders (Authenticated)
```bash
curl https://yoursite.com/api/v1/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Admin: Get Overview (Admin Token)
```bash
curl https://yoursite.com/api/v1/admin/dashboard/overview \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

---

## 🌐 JavaScript Examples

### Fetch Products
```javascript
fetch('https://yoursite.com/api/v1/custom-builder/products')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

### Fetch User Orders
```javascript
const token = 'YOUR_TOKEN_HERE';

fetch('https://yoursite.com/api/v1/orders', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

### Create Warranty Claim
```javascript
const token = 'YOUR_TOKEN_HERE';
const warrantyId = 123;

fetch(`https://yoursite.com/api/v1/warranties/${warrantyId}/claims`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    description: 'Solar panel not working',
    issue_type: 'malfunction'
  })
})
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.error('Error:', error));
```

---

## ⚠️ Important Notes

### Web Routes vs API Routes

**Web Routes (`/dashboard/stats`):**
- Used by Inertia.js frontend
- Session-based authentication
- CSRF protection required
- Returns Inertia responses

**API Routes (`/api/v1/dashboard/stats`):**
- Used by external consumers
- Token-based authentication (Sanctum)
- No CSRF required
- Returns JSON responses

### Both are valid and serve different purposes!

---

## 🔒 Security Headers

Always include these headers in API requests:

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## 📊 Response Format

### Success Response
```json
{
  "data": {
    "id": 1,
    "name": "Product Name",
    ...
  }
}
```

### Error Response
```json
{
  "message": "Unauthenticated.",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## 🧪 Testing API Routes

### List all API routes
```bash
php artisan route:list --path=api
```

### Test with Postman
1. Import collection
2. Set base URL: `https://yoursite.com/api/v1`
3. Add Authorization header with Bearer token
4. Test endpoints

### Test with cURL
```bash
# Get token first (login via web)
# Then use token in API calls

curl -X GET https://yoursite.com/api/v1/user \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 📚 Additional Resources

- **Laravel Sanctum Docs:** https://laravel.com/docs/sanctum
- **API Versioning:** Routes prefixed with `v1` for future compatibility
- **Rate Limiting:** Consider adding rate limits for production

---

**Quick Tip:** Use `/api/v1/` prefix for all REST API calls!
