# Blog cá nhân

Blog cá nhân xây dựng thủ công trên nền **Payload Website Template** (Next.js App Router + Payload CMS), thay vì dùng SaaS CMS hay theme dựng sẵn. Toàn bộ giao diện, plugin và tính năng được tự viết trong repo này.

Chi tiết định hướng kỹ thuật, tech stack, và quy ước code: xem [CLAUDE.md](./CLAUDE.md).

---

## Tech stack

- **Next.js** (App Router) — trang public và `/admin` chạy chung một app.
- **Payload CMS 3.x** — cấu hình trong `src/payload.config.ts`, admin panel tự sinh từ config.
- **Database** — **Postgres** (adapter `@payloadcms/db-postgres`), dùng từ đầu thay vì Mongo mặc định của CLI.
- **Tailwind CSS v4** cho styling.
- **Lexical richtext** (`@payloadcms/richtext-lexical`) cho nội dung bài viết.
- **Hosting**: VPS tự quản lý (không dùng Vercel/Netlify).

## Cấu trúc thư mục chính

```
src/
├── app/            # Route Next.js — (frontend) và (payload)
├── collections/    # Định nghĩa CMS: Posts, Pages, Categories, Media, Users
├── Header/         # Global config + component header
├── Footer/         # Global config + component footer
├── blocks/         # Layout builder blocks
├── heros/          # Các kiểu hero section
├── fields/         # Field dùng chung giữa các collection
├── access/         # Access control
├── endpoints/      # Custom REST endpoint
├── hooks/          # Payload hooks
├── plugins/        # Cấu hình plugin Payload
├── providers/      # React context providers
├── search/         # Cấu hình plugin-search
├── components/     # React components dùng chung
└── utilities/      # Hàm tiện ích
```

## Repository Scope

### ✅ Có trong repo
- Code nguồn Next.js + Payload (collections, blocks, fields, components...)
- Cấu hình build, lint, TypeScript
- `payload-types.ts` (sinh ra từ `pnpm generate:types`, được commit để CI/build không cần chạy generate lại)
- Tài liệu (`docs/`)
- `.env.example`

### ❌ Không có trong repo
- `node_modules`, build output (`.next/`, `/build`)
- File `.env` thật (secrets, connection string)
- Media upload của người dùng (`public/media`)
- Database dump

Xem [.gitignore](./.gitignore) để biết chi tiết.

## Nội dung bài viết

Bài viết được soạn qua **admin UI của Payload** (`/admin`) và lưu trong database — không dùng file Markdown + Git để viết bài. Rich text dùng Lexical, có thể xuất ra dạng block tuỳ layout.

## Đa ngôn ngữ

Dùng tính năng localization tích hợp sẵn của Payload, locale `vi` mặc định + `en`, khai báo trong `payload.config.ts`.

## Bình luận độc giả

Dùng [Giscus](https://giscus.app) nhúng vào component hiển thị bài viết — không cần backend riêng cho comment.

## Giao diện frontend

Theme trắng + cam theo phong cách Substack. Chi tiết màu sắc, font, các component đã đổi, và các chỗ còn là placeholder cần thay trước khi public: xem [docs/frontend-theme.md](./docs/frontend-theme.md).

## Local Development

Xem hướng dẫn chi tiết trong [docs/local.md](./docs/local.md).

Tóm tắt nhanh:

```bash
pnpm install
cp .env.example .env
pnpm dev
```

- Frontend: http://localhost:3000
- Admin: http://localhost:3000/admin
