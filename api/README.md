# Export API — Laravel REST API with JWT Auth & Import/Export Module

Hệ thống REST API xây dựng trên Laravel 12, cung cấp authentication bằng JWT (access token + refresh token) kèm cơ chế RBAC, và module Import/Export chung cho phép các entity trong hệ thống dễ dàng tích hợp.

---

## Tech Stack

| Layer | Công nghệ |
|-------|-----------|
| Framework | Laravel 12 |
| Auth | JWT (tymon/jwt-auth) — access token 15 phút |
| Refresh Token | Random string 60 ký tự, hash SHA-256, DB storage, rotation |
| RBAC | roles + permissions (many-to-many) |
| Excel | openspout/openspout v4 |
| Queue | database driver (job batching) |
| Database | MySQL 5.7 |
| Container | Docker (php-fpm 8.2 + nginx + mysql) |

---

## Setup

### 1. Yêu cầu

- Docker & Docker Compose
- Cổng `8080`, `3306`, `9000` không bị chiếm

### 2. Khởi động

```bash
# Build & start containers
docker-compose -f docker-export/docker-compose.yaml up -d --build

# Cài đặt dependencies (nếu chưa có trong image)
docker exec export composer install

# Tạo file .env từ mẫu
cp .env.example .env
# Cập nhật DB_HOST=mysql, DB_DATABASE=export, DB_USERNAME=root, DB_PASSWORD=export

# Generate app key & JWT secret
docker exec export php artisan key:generate
docker exec export php artisan jwt:secret

# Chạy migration & seeder
docker exec export php artisan migrate --force
docker exec export php artisan db:seed --force

# Tạo storage symlink (nếu cần upload file)
docker exec export php artisan storage:link

# Chạy queue worker (xử lý import/export)
docker exec -d export php artisan queue:work --queue=default --tries=3 --timeout=300
```

### 3. Kiểm tra

```bash
# Register user
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Admin","email":"admin@test.com","password":"password","password_confirmation":"password"}'

# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'
```

---

## API Endpoints

### Authentication

| Method | Endpoint | Auth | Mô tả |
|--------|----------|------|-------|
| POST | `/api/register` | No | Đăng ký, trả về access_token + refresh_token |
| POST | `/api/login` | No | Đăng nhập, trả về cặp token |
| POST | `/api/refresh` | No | Refresh token (rotation) |
| POST | `/api/logout` | Bearer | Revoke refresh token + invalidate JWT |
| GET | `/api/me` | Bearer | Thông tin user kèm roles & permissions |

### Import

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/import/{module}` | Upload file CSV/XLSX để import |
| GET | `/api/import/{module}/status/{id}` | Tra cứu trạng thái import |

Request mẫu:
```bash
curl -X POST http://localhost:8080/api/import/users \
  -H "Authorization: Bearer <token>" \
  -F "file=@users.csv" \
  -F 'options={"update_existing":true}'
```

### Export

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/export/{module}` | Tạo yêu cầu export (async, queue) |
| GET | `/api/export/{module}/preview` | Xem trước 5 dòng + columns |
| GET | `/api/export/{module}/status/{id}` | Tra cứu trạng thái export |

Request mẫu:
```json
POST /api/export/users
{
  "format": "csv",
  "columns": ["id", "name", "email"],
  "filters": [
    { "field": "created_at", "operator": "between", "value": ["2025-01-01", "2025-12-31"] },
    { "field": "name", "operator": "like", "value": "John" }
  ]
}
```

### Generic — Import/Export

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/import-export/modules` | Danh sách module đã đăng ký |
| GET | `/api/import-export/{id}` | Chi tiết job (status, progress, files) |
| GET | `/api/import-export/{id}/download?type=result` | Download file kết quả |
| GET | `/api/import-export/{id}/download?type=error` | Download file log lỗi |
| GET | `/api/import-export/{id}/logs?level=error` | Paginated row-level logs |

---

## Database Schema

### import_exports
| Column | Type | Mô tả |
|--------|------|-------|
| type | enum | `import` / `export` |
| module | string | `users`, `products`, ... |
| status | enum | `pending` → `processing` → `completed` / `failed` |
| file_format | string | `csv` / `xlsx` |
| total_rows / processed_rows / failed_rows | uint | Progress tracking |
| options | json | Config đầu vào |

### import_export_files
| Column | Type | Mô tả |
|--------|------|-------|
| type | enum | `source` (file upload), `result` (file xuất), `error` (log lỗi) |
| file_path | string | Đường dẫn trên disk |
| file_size | bigint | Dung lượng |

### import_export_logs
| Column | Type | Mô tả |
|--------|------|-------|
| import_export_id | FK | Belongs to job |
| row_index | uint | Dòng số mấy trong file |
| level | enum | `info` / `warning` / `error` |
| message | text | Nội dung lỗi |
| context | json | Giá trị gốc của dòng |