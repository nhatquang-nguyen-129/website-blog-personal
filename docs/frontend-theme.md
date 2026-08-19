# Giao diện frontend (theme Substack: trắng + cam)

Ghi lại các quyết định thiết kế đã áp dụng cho frontend, để không phải suy luận lại từ code khi chỉnh sửa sau này.

## Màu sắc & typography

- Toàn bộ màu sắc là CSS custom properties trong [`src/app/(frontend)/globals.css`](../src/app/\(frontend\)/globals.css) (`:root` cho light mode, `[data-theme='dark']` cho dark mode) — không hardcode màu trực tiếp trong component.
- Nền trắng (`--background`), chữ gần đen ấm (`--foreground`), **cam** làm màu nhấn duy nhất (`--primary`, dùng cho nút, link, category label).
- Font serif (Source Serif 4, load qua `next/font/google` trong `layout.tsx`, biến `--font-source-serif`) dùng cho tiêu đề bài viết và nội dung bài viết (`prose`). Font sans (Geist) dùng cho UI/menu/meta text — đúng phong cách Substack.
- Cấu hình prose/typography nằm ở `tailwind.config.mjs` (phần `theme.extend.typography`).

## Các component đã đổi so với template gốc

- `Header/Component.client.tsx` + `Header/Nav`: từ header trong suốt nổi trên hero → thanh header trắng dính (sticky), có nút "Đăng ký" màu cam.
- `Footer/Component.tsx`: từ nền đen → nền sáng tối giản.
- `components/Card`, `components/CollectionArchive`: từ lưới card có khung → danh sách bài viết kiểu Substack (title serif, excerpt, meta, thumbnail bên phải).
- `heros/PostHero`: từ hero ảnh nền tối full-bleed → header bài viết nền trắng, ảnh cover được đóng khung.
- `heros/HighImpact`: đã bỏ `-mt-[10.4rem]` (mẹo margin âm dựa vào header trong suốt cũ) vì header giờ luôn chiếm chỗ bình thường trong layout.
- `components/Logo/Logo.tsx`: từ logo SVG của Payload → wordmark chữ.

## Việc còn là placeholder — cần đổi trước khi public

- **Tên blog**: chuỗi `"Blog Cá Nhân"` hardcode trong `src/components/Logo/Logo.tsx`. Đổi 1 chỗ này khi có tên thật.
- **Nút "Đăng ký"** trong `Header/Nav/index.tsx`: hiện trỏ tới `/contact` vì site chưa có tính năng subscribe/newsletter thật. Khi có form thật (form-builder hoặc dịch vụ email ngoài), cập nhật lại `href`.
- **Favicon**: vẫn dùng `public/favicon.ico`/`favicon.svg` mặc định của template — chưa đổi theo roadmap ở [CLAUDE.md](../CLAUDE.md#việc-cần-làm-tiếp-roadmap-tùy-biến).

## Kiểm tra sau khi đổi theme

`pnpm dev` không tự bắt lỗi typography/màu sắc — nếu sửa `globals.css` hoặc `tailwind.config.mjs`, nên chạy `pnpm build` một lần để chắc Turbopack build production không lỗi (từng có bug Turbopack + `postcss.config.js` ở Next 16.3.0, đã fix bằng cách nâng lên 16.3.1).
