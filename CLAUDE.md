# CLAUDE.md

File này hướng dẫn Claude Code (hoặc bất kỳ AI coding assistant nào) khi làm việc trong repo blog cá nhân này.

## Tổng quan dự án

Đây là blog cá nhân, xây trên nền **Payload Website Template** (Next.js + Payload CMS) — một mã nguồn duy nhất chứa cả frontend lẫn CMS/backend, không tách repo riêng.

Quyết định hướng đi (xem chi tiết lý do trong `docs/tech-stack-direction.md` nếu có mang theo từ project Claude):
- Muốn tự code giao diện, tự có database, tự viết plugin/tính năng mới, tự có CMS riêng — không dùng SaaS CMS bên ngoài.
- Nội dung bài viết lưu trong **database**, soạn qua **admin UI** của Payload (không dùng file Markdown + Git để viết bài).
- Hosting mục tiêu: **VPS tự quản lý** (không dùng Vercel/Netlify).
- Ưu tiên tính năng: Markdown/MDX rich text (Payload dùng Lexical richtext, xuất được ra dạng block), bình luận độc giả, SEO, đa ngôn ngữ (Việt/Anh).

## Cách lấy source ban đầu

Vì template gốc dùng `workspace:*` trong monorepo Payload, **không clone tay thư mục `templates/website`** — luôn dùng CLI chính thức để dependency được resolve đúng phiên bản:

```bash
npx create-payload-app@latest my-blog -t website
cd my-blog
cp .env.example .env
```

## Tech stack

- **Framework**: Next.js (App Router) — cả trang public lẫn `/admin` chạy chung 1 app.
- **CMS**: Payload CMS 3.x — cấu hình toàn bộ trong `src/payload.config.ts`, admin panel tự sinh từ config, không cần code UI riêng.
- **Database**: **Postgres** (`@payloadcms/db-postgres`), dùng từ đầu (đã scaffold bằng `create-payload-app ... --db postgres`) thay vì Mongo mặc định của CLI, để local khớp môi trường VPS.
- **Styling**: Tailwind CSS v4.
- **Rich text**: `@payloadcms/richtext-lexical`.
- **Plugin có sẵn**: `plugin-seo`, `plugin-search`, `plugin-redirects`, `plugin-form-builder`, `plugin-nested-docs`.
- **Bình luận độc giả**: chưa có sẵn trong template — sẽ thêm bằng Giscus (nhúng script vào block/component bài viết), không cần backend riêng cho comment.
- **Đa ngôn ngữ**: dùng tính năng localization tích hợp sẵn của Payload (khai báo `localization` trong `payload.config.ts`, locale `vi` mặc định + `en`).

## Cấu trúc thư mục chính (`src/`)

```
src/
├── app/            # Next.js App Router — route (frontend) và (payload)
├── collections/    # Định nghĩa CMS: Posts, Pages, Categories, Media, Users
├── Header/         # Global config + component cho header (Payload global)
├── Footer/         # Global config + component cho footer (Payload global)
├── blocks/         # Layout builder blocks (dùng trong Pages/Posts)
├── heros/          # Các kiểu hero section cho trang
├── fields/         # Field dùng chung, tái sử dụng giữa các collection
├── access/         # Access control (ai được đọc/sửa gì)
├── endpoints/      # Custom REST endpoint riêng
├── hooks/          # Payload hooks (beforeChange, afterChange...)
├── plugins/        # Khai báo/cấu hình các plugin Payload
├── providers/      # React context providers cho frontend
├── search/         # Cấu hình plugin-search
├── components/     # React components dùng chung cho frontend
└── utilities/      # Hàm tiện ích
```

Muốn thêm tính năng mới (vd. thêm loại nội dung, thêm field, thêm plugin riêng) → sửa/thêm trong `collections/`, `fields/`, `blocks/`, hoặc `plugins/`. Muốn đổi giao diện → sửa trong `app/` và `components/`.

## Lệnh thường dùng

```bash
pnpm install              # cài dependency
pnpm dev                  # chạy dev server tại http://localhost:3000 (admin: /admin)
pnpm build                # build production (chạy payload build)
pnpm generate:types       # sinh lại TypeScript types từ Payload config sau khi đổi collection/field
pnpm lint                 # kiểm tra lint
pnpm lint:fix             # tự sửa lỗi lint
```

Sau mỗi lần đổi `collections/` hoặc field trong `payload.config.ts`, luôn chạy lại `pnpm generate:types` để `payload-types.ts` cập nhật đúng.

## Biến môi trường (`.env`)

```
DATABASE_URL=            # connection string Postgres
PAYLOAD_SECRET=          # chuỗi bí mật để mã hoá JWT — không commit giá trị thật
NEXT_PUBLIC_SERVER_URL=  # URL public của site, vd. http://localhost:3000 lúc dev
CRON_SECRET=             # bảo vệ cron job (scheduled publish)
PREVIEW_SECRET=          # bảo vệ route live preview
```

Không commit file `.env` thật lên Git — chỉ commit `.env.example`.

## Việc cần làm tiếp (roadmap tùy biến)

1. Đổi branding: tên site thật (hiện là placeholder "Blog Cá Nhân" trong `components/Logo/Logo.tsx`), favicon, mô tả.
2. Thêm localization `vi`/`en` trong `payload.config.ts`, đánh dấu field nào cần dịch (`localized: true`).
3. Thêm Giscus vào template hiển thị bài viết (component riêng trong `components/`, nhúng vào trang chi tiết Post).
4. Kiểm tra/tinh chỉnh `plugin-seo` cho đúng nhu cầu (meta mặc định, OG image).
5. Viết quy trình deploy VPS: build → chạy Next.js bằng PM2 (vì có phần server/API, không phải site tĩnh) → reverse proxy qua Nginx/Caddy → Postgres riêng trên VPS hoặc managed DB.

## Quy ước code

- Ưu tiên sửa trong `src/collections`, `src/blocks`, `src/fields` khi thêm tính năng CMS — tránh sửa trực tiếp package `payload`/`@payloadcms/*` (đó là dependency, không phải code của mình).
- Giữ mọi logic truy cập dữ liệu qua Payload Local API (`payload.find`, `payload.create`...) với `overrideAccess: false` khi chạy trong ngữ cảnh có user, để không bỏ qua access control.
- Sau khi đổi schema (field/collection), luôn chạy `pnpm generate:types` trước khi commit.
