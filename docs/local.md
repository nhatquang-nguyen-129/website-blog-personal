# Chạy và triển khai local

Hướng dẫn dựng source lần đầu, chạy dev server, và mô phỏng môi trường gần giống VPS trên máy local.

## Yêu cầu

- Node.js LTS (>= 20)
- pnpm (`corepack enable` nếu chưa có pnpm)
- MongoDB local (hoặc Docker) — sẽ đổi sang Postgres trước khi deploy VPS, xem [CLAUDE.md](../CLAUDE.md#tech-stack)

## 1. Lấy source lần đầu

Template gốc dùng `workspace:*` trong monorepo Payload, **không clone tay** thư mục `templates/website` — luôn dùng CLI chính thức để dependency resolve đúng phiên bản:

```bash
npx create-payload-app@latest my-blog -t website
cd my-blog
cp .env.example .env
```

Nếu repo đã tồn tại (đã có `src/`), chỉ cần clone repo này và cài dependency ở bước 2.

## 2. Cài dependency

```bash
pnpm install
```

## 3. Cấu hình `.env`

Copy `.env.example` thành `.env` rồi điền giá trị thật (không commit `.env`):

```
DATABASE_URL=mongodb://127.0.0.1:27017/blog
PAYLOAD_SECRET=<chuỗi random dài, chỉ dùng local>
NEXT_PUBLIC_SERVER_URL=http://localhost:3000
CRON_SECRET=<chuỗi random>
PREVIEW_SECRET=<chuỗi random>
```

## 4. Chạy database local

Cách nhanh nhất — MongoDB qua Docker:

```bash
docker run -d --name blog-mongo -p 27017:27017 -v blog-mongo-data:/data/db mongo:7
```

Hoặc dùng MongoDB cài trực tiếp trên máy nếu đã có sẵn.

## 5. Chạy dev server

```bash
pnpm dev
```

- Frontend: http://localhost:3000
- Admin panel: http://localhost:3000/admin (lần đầu vào sẽ được yêu cầu tạo user admin đầu tiên)

## 6. Các lệnh thường dùng khi phát triển

```bash
pnpm generate:types   # bắt buộc chạy sau khi đổi collection/field trong payload.config.ts
pnpm lint             # kiểm tra lint
pnpm lint:fix         # tự sửa lỗi lint
pnpm build            # build production, kiểm tra trước khi deploy
```

Luôn chạy `pnpm generate:types` và commit `payload-types.ts` cùng thay đổi schema — xem [CLAUDE.md](../CLAUDE.md#lệnh-thường-dùng).

## 7. Kiểm tra build production local (mô phỏng VPS)

Trước khi deploy, nên build và chạy thử ở chế độ production ngay trên máy local:

```bash
pnpm build
pnpm start
```

Mặc định Next.js sẽ chạy ở http://localhost:3000. Đây cũng là cách VPS sẽ chạy app (qua PM2), nên nếu bước này lỗi thì deploy cũng sẽ lỗi.

## 8. Ghi chú khi chuyển sang Postgres

Trước khi deploy VPS, đổi adapter trong `src/payload.config.ts`:

```ts
// từ
import { mongooseAdapter } from '@payloadcms/db-mongodb'
// sang
import { postgresAdapter } from '@payloadcms/db-postgres'
```

Và cập nhật `DATABASE_URL` trỏ tới Postgres (local: có thể chạy `docker run -d -p 5432:5432 -e POSTGRES_PASSWORD=postgres postgres:16` để test trước). Sau khi đổi adapter, chạy lại `pnpm generate:types` và kiểm tra migration.

## Troubleshooting

- **Admin panel trắng trang / lỗi kết nối DB**: kiểm tra `DATABASE_URL` trong `.env` và đảm bảo MongoDB/Postgres đang chạy.
- **Type lỗi sau khi đổi collection**: chạy `pnpm generate:types` rồi restart dev server.
- **Port 3000 bị chiếm**: đổi qua biến môi trường `PORT=3001 pnpm dev` hoặc tắt tiến trình đang giữ cổng đó.
