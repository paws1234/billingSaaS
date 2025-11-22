# Quick API Tests

## Using PowerShell

### 1. Get all plans
```powershell
(Invoke-RestMethod -Uri "http://localhost:8000/api/plans").plans
```

### 2. Login and save token
```powershell
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method POST -ContentType "application/json" -Body '{"email":"user@example.com","password":"password"}'
$token = $response.token
Write-Host "Token: $token"
```

### 3. Get user profile
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/me" -Headers @{Authorization="Bearer $token"}
```

### 4. Get subscriptions
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/subscriptions" -Headers @{Authorization="Bearer $token"}
```

### 5. Get invoices
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/invoices" -Headers @{Authorization="Bearer $token"}
```

## Using curl (Git Bash or WSL)

### 1. Get all plans
```bash
curl http://localhost:8000/api/plans | jq
```

### 2. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq
```

### 3. Save token and use it
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.token')

curl http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" | jq
```

## Using Browser/Postman

1. **GET Plans**: http://localhost:8000/api/plans
2. **POST Login**: http://localhost:8000/api/login
   - Body: `{"email":"user@example.com","password":"password"}`
3. Copy the token from response
4. **GET Profile**: http://localhost:8000/api/me
   - Header: `Authorization: Bearer YOUR_TOKEN`

## Test Data Available

- **Admin User**: admin@example.com / password
- **Regular User**: user@example.com / password
- **Plans**: Basic, Pro, Enterprise

## Database Inspection

```bash
docker compose exec app php artisan tinker
```

Then:
```php
User::all();
Plan::all();
Subscription::all();
Invoice::all();
```
