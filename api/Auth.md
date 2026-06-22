You are a senior Laravel developer.

Build a complete authentication and authorization system using Laravel with JWT (access token + refresh token) and RBAC.

---

## Tech Requirements

* Framework: Laravel (latest stable)
* Authentication: JWT (use tymon/jwt-auth for access token)
* Database: MySQL
* Architecture: Clean, scalable, production-ready

---

## 1. Authentication Design

Implement TWO tokens:

### Access Token

* JWT token (using tymon/jwt-auth)
* Short-lived (15 minutes)
* Used for API authentication

### Refresh Token

* Random secure string (NOT JWT)
* Long-lived (7–30 days)
* Stored in database (hashed)
* Used to generate new access tokens

---

## 2. Database إضاف (IMPORTANT)

Create additional table: refresh_tokens

Fields:

* id
* user_id
* token (hashed)
* expires_at
* revoked (boolean, default false)
* created_at
* updated_at

---

## 3. Authentication APIs

### Register API

POST /api/register

* Fields: name, email, password, password_confirmation
* Validate input
* Hash password using bcrypt
* Assign default role: "member"
* Generate:

  * access_token
  * refresh_token
* Store refresh_token (hashed) in DB
* Response:

```json
{
  "access_token": "...",
  "refresh_token": "...",
  "token_type": "bearer",
  "expires_in": 900
}
```

---

### Login API

POST /api/login

* Validate credentials
* Generate:

  * access_token
  * refresh_token
* Store refresh_token in DB
* Return tokens

---

### Refresh Token API

POST /api/refresh

* Input: refresh_token

* Steps:

  1. Hash incoming token
  2. Find in DB
  3. Check:

     * not expired
     * not revoked
  4. Generate new access_token

* Optional (recommended):

  * Rotate refresh token:

    * revoke old one
    * issue new refresh_token

* Response:

```json
{
  "access_token": "...",
  "refresh_token": "...",
  "expires_in": 900
}
```

---

### Logout API

POST /api/logout

* Require access_token (auth:api)
* Revoke current refresh_token in DB
* Invalidate access_token (JWT logout)
* Return success message

---

### Profile API

GET /api/me

* Require access_token (auth:api)
* Return user info with roles and permissions

---

## 4. RBAC (Role-Based Access Control)

### Database Schema

Create tables:

* users
* roles (id, name)
* permissions (id, name)
* role_user (user_id, role_id)
* permission_role (permission_id, role_id)

---

### Model Relationships

User:

* belongsToMany Roles
* has method:

```php
hasPermission(string $permission): bool
```

Role:

* belongsToMany Permissions

Permission:

* basic model

---

### Default Role

* Create role: "member"
* Assign automatically on register


## 6. JWT Integration

* Use guard: api
* Generate JWT access token on login/register
* Protect routes with auth:api
* Support token invalidation



## 8. Seeders

Roles:

* member
* admin

Permissions:

* create_post
* edit_post
* delete_post
* view_post

Assignments:

* admin → all permissions
* member → view_post only

---


System must be fully functional and testable via Postman.
