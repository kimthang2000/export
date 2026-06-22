# Export Project

Hệ thống gồm 2 phần: **API** (backend Laravel) và **Web** (frontend React Admin Dashboard).

## API (`/api`)

Laravel REST API cung cấp:

- **Authentication** — Register, Login, Logout, Refresh Token (JWT + Refresh Token rotation)
- **Import** — Upload file CSV/XLSX, xử lý bất đồng bộ qua queue, theo dõi trạng thái & log lỗi chi tiết
- **Export** — Xuất dữ liệu ra CSV/XLSX, hỗ trợ lọc động theo từng entity, xử lý ngầm qua queue, download file kết quả
- **RBAC** — Role & Permission (đang phát triển thêm)

## Web (`/web`)

React 19 + TypeScript + Ant Design — Admin Dashboard:

- **Login / Register** — Giao diện đăng nhập, đăng ký
- **Dashboard** — Trang chính sau khi đăng nhập
- Bảo vệ route (ProtectedRoute / PublicRoute), kết nối API qua Axios

## Run

```bash
# API (Docker)
cd api && docker-compose -f docker-export/docker-compose.yaml up -d

# Web
cd web && npm install && npm run dev
```
