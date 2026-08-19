# Chạy và triển khai local

Hướng dẫn dựng source lần đầu, chạy dev server, và mô phỏng môi trường gần giống VPS trên máy local.

## Yêu cầu

- Node.js LTS (>= 20)
- pnpm 9 (`corepack prepare pnpm@9 --activate` — pnpm 10+ yêu cầu Node 22, chưa tương thích với Node 20)
- PostgreSQL local (dùng luôn từ đầu, không qua Mongo — xem [CLAUDE.md](../CLAUDE.md#tech-stack))

## 1. Lấy source lần đầu

Template gốc dùng `workspace:*` trong monorepo Payload, **không clone tay** thư mục `templates/website` — luôn dùng CLI chính thức để dependency resolve đúng phiên bản, chỉ định adapter Postgres ngay từ đầu:

```bash
npx create-payload-app@latest -n my-blog -t website --db postgres
cd my-blog
```

`.env` được CLI tự sinh sẵn với `DATABASE_URL` trỏ tới Postgres.

Nếu repo đã tồn tại (đã có `src/`), chỉ cần clone repo này và cài dependency ở bước 2.

## 2. Cài dependency

```bash
pnpm install
```

Nếu gặp lỗi `packages field missing or empty`, kiểm tra `pnpm-workspace.yaml` có khai báo `packages: ['.']` — file này chỉ tồn tại để khai báo `allowBuilds`, không phải một monorepo thật.

## 3. Cấu hình `.env`

```
PORT=3000
DATABASE_URL=postgresql://postgres:postgres@127.0.0.1:5432/blog
PAYLOAD_SECRET=<chuỗi random dài, chỉ dùng local>
NEXT_PUBLIC_SERVER_URL=http://localhost:3000
CRON_SECRET=<chuỗi random>
PREVIEW_SECRET=<chuỗi random>
```

`PORT=3000` giữ cho `pnpm dev`/`pnpm start` luôn cố định ở cổng 3000, không tự nhảy sang cổng khác nếu 3000 từng bị chiếm.

Không commit file `.env` thật lên Git — chỉ commit `.env.example`.

## 4. Chạy database local

Cài PostgreSQL qua Homebrew (macOS):

```bash
brew install postgresql@16
brew services start postgresql@16
```

Tạo role và database khớp với `DATABASE_URL` ở trên:

```bash
psql -d postgres -c "CREATE ROLE postgres WITH LOGIN SUPERUSER PASSWORD 'postgres';"
psql -d postgres -c "CREATE DATABASE blog OWNER postgres;"
```

(Nếu dùng Docker: `docker run -d -p 5432:5432 -e POSTGRES_PASSWORD=postgres postgres:16`, rồi tự tạo database `blog`.)

## 5. Chạy dev server

```bash
pnpm dev
```

- Frontend: http://localhost:3000
- Admin panel: http://localhost:3000/admin

Khi database còn trống (chưa có user nào — đúng trạng thái sau lần deploy VPS đầu tiên), `/admin` tự hiện form **"Create your first user"**. Điền email/password thật của bạn ở đây để tạo admin — không có tài khoản mặc định nào được hardcode trong code. Từ user thứ hai trở đi (hoặc sau khi user đầu đã tồn tại), `/admin` chỉ hiện màn hình đăng nhập bình thường. Sửa lại thông tin admin (đổi email/password) làm sau, trong menu **Users** của admin.

Next.js 16 tự động chèn một block "agent rules" vào `CLAUDE.md` mỗi lần `next dev` chạy. Repo này đã tắt hành vi đó (`agentRules: false` trong `next.config.ts`) để giữ `CLAUDE.md` hoàn toàn do người viết kiểm soát.

### Seed dữ liệu mẫu (tuỳ chọn)

Nếu muốn có sẵn vài bài post/trang mẫu để xem giao diện thay vì bắt đầu từ trống, sau khi đã tạo user admin đầu tiên qua `/admin`, đăng nhập rồi gọi:

```bash
curl -X POST http://localhost:3000/next/seed -b cookies.txt
```

(cần cookie phiên đăng nhập hợp lệ — đăng nhập qua `/api/users/login` rồi dùng cookie trả về, hoặc gọi trực tiếp từ trình duyệt sau khi đã đăng nhập admin).

Lệnh này **xoá sạch** toàn bộ posts/pages/categories hiện có rồi chèn lại dữ liệu mẫu — không chạy trên dữ liệu thật đang dùng.

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

## Troubleshooting

- **Admin panel trắng trang / lỗi kết nối DB**: kiểm tra `DATABASE_URL` trong `.env` và đảm bảo Postgres đang chạy (`pg_isready`).
- **Type lỗi sau khi đổi collection**: chạy `pnpm generate:types` rồi restart dev server.
- **Port 3000 bị chiếm**: đổi tạm `PORT=3001 pnpm dev` hoặc tắt tiến trình đang giữ cổng đó (`lsof -i :3000`).
- **`pnpm install` báo lỗi Node.js version với pnpm 10+**: dùng `corepack prepare pnpm@9 --activate` để pin về pnpm 9, tương thích Node 20.
