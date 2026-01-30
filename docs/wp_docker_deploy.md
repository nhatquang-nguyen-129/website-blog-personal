WordPress Manual Deploy (Code-first)
Purpose

This setup allows:

WordPress core to remain stateless

Custom features to live in wp-content/mu-plugins

Safe local development after git pull

No modification to WordPress core files

1. Prerequisites

Docker

Git

Port 8000 free

2. Pull source code
git pull origin main


Expected structure:

public/
└── wp-content/
    └── mu-plugins/

3. Build Docker image
docker build -t wp-local .

4. Run container
docker run -p 8000:80 wp-local

5. First-time WordPress setup

Open browser:

http://localhost:8000


Proceed with:

Language selection

Database configuration

Admin account

⚠️ This step does not modify Git-tracked files

6. MU-Plugins behavior

All files in wp-content/mu-plugins are:

Auto-loaded by WordPress

Loaded before normal plugins

Not manageable via WP Admin UI

This is intentional.

7. What you are allowed to modify

✅ Allowed (safe):

wp-content/mu-plugins/*

wp-content/uploads/

Database

Dockerfile

❌ Never modify:

/wp-admin

/wp-includes

Core PHP files in root

8. Reset WordPress safely (no code loss)

If WordPress breaks:

docker rm -f wp-local
docker build --no-cache -t wp-local .
docker run -p 8000:80 wp-local


This:

Re-downloads WordPress core

Preserves mu-plugins

Does NOT affect Git code

9. Why this works

WordPress core = disposable

Custom logic = versioned

No bootstrap hacks

No early require

No admin override

Final Notes

This setup is intentionally framework-like, not UI-driven.

It is suitable for:

Custom features

Headless WordPress

Multi-language manual content

Dark mode / UI toggles

CI/CD pipelines

Next logical step (recommendation)

Create single MU-plugin entry:

wp-content/mu-plugins/custom-features.php


Inside it:

Feature registry

Safe hooks (init, wp_enqueue_scripts)

No theme assumptions