# WordPress Customized Plugins

This document describes the recommended architecture for extending WordPress with custom PHP features, without modifying WordPress core files.

- Clean separation between CMS core and custom logic
- Safe local development & CI/CD
- Feature-based architecture (dark mode, multilingual, etc.)

## 1. Philosophy

### 1.1. What we DO NOT do

❌ Modify files inside `public/wp-includes`

❌ Modify files inside `public/wp-admin`

❌ Add application logic into `wp-config.php`

❌ Assume themes or UI always exist

### 1.2. What we DO

✅ Use Must-Use Plugins (MU-Plugins) as `bootstrap layer`

✅ Keep all custom logic in a separate `features/` directory

✅ Load features only after WordPress is fully initialized

✅ Enable/disable features via configuration

### 1.3. Repository Structure
website-blog-personal/
├─ public/                     # WordPress core (not customized)
│  ├─ wp-admin/
│  ├─ wp-includes/
│  └─ wp-content/
│     └─ mu-plugins/
│        └─ bootstrap.php      # Custom bootstrap entry point
│
├─ features/                   # Custom features
│  ├─ features.config.php
│  └─ dark-mode/
│     ├─ dark-mode.php
│     ├─ dark-mode.css
│     └─ dark-mode.js
│
├─ .gitignore
└─ README.md

5. MU-Plugins Bootstrap
5.1. Why MU-Plugins?

MU-Plugins are loaded automatically

Cannot be disabled via admin UI

Loaded after WordPress core, before themes

Safe place for application bootstrap

Official behavior:

Must-use plugins are loaded automatically before normal plugins and themes.

5.2. Create MU-Plugins directory
mkdir public/wp-content/mu-plugins

5.3. Create bootstrap file
touch public/wp-content/mu-plugins/bootstrap.php

5.4. bootstrap.php (Minimal Safe Version)
<?php
/**
 * Application Bootstrap
 * Loads custom features safely after WordPress is ready
 */

if (!defined('ABSPATH')) {
    return;
}

/**
 * Do not load custom features during installation
 */
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

/**
 * Load custom features after theme setup
 */
add_action('after_setup_theme', function () {

    $features_root = dirname(__DIR__, 3) . '/features';

    if (!is_dir($features_root)) {
        return;
    }

    $config_file = $features_root . '/features.config.php';

    if (!file_exists($config_file)) {
        return;
    }

    $features = require $config_file;

    if (!empty($features['dark_mode'])) {
        require_once $features_root . '/dark-mode/dark-mode.php';
    }
});

6. Feature Configuration
6.1. Feature flags

📄 features/features.config.php

<?php

return [
    'dark_mode' => false,
];


Features are disabled by default

Enables safe CI/CD and local development

No feature runs unless explicitly enabled

7. Feature Development Rules

Each feature must follow these rules:

7.1. Feature isolation

One feature per folder

No global side effects

No direct output outside hooks

7.2. WordPress safety guards

Every feature must begin with:

if (!defined('ABSPATH')) {
    return;
}

7.3. Hook-based execution only

❌ Do not execute logic at file load time
✅ Always use WordPress hooks (add_action, add_filter)