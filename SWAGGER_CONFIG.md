# 🔧 Swagger Configuration Guide

## مقدمه

تمام تنظیمات Swagger UI اکنون از طریق **config file** و **.env** قابل تغییر هستند. دیگر نیازی به ویرایش فایل‌های HTML نیست.

---

## 📋 Configuration Options

تمام این options توی `config/module-generator.php` قابل تنظیم هستند:

```php
'swagger' => [
    // Theme: vanilla, tailwind, dark
    'theme' => env('SWAGGER_THEME', 'vanilla'),

    // Colors
    'colors' => [
        'primary' => env('SWAGGER_COLOR_PRIMARY', '#3b82f6'),
        'secondary' => env('SWAGGER_COLOR_SECONDARY', '#06b6d4'),
        // ... و بقیه
    ],

    // Fonts
    'fonts' => [
        'family' => env('SWAGGER_FONT_FAMILY', 'system-ui, -apple-system, sans-serif'),
        'mono' => env('SWAGGER_FONT_MONO', '"Fira Code", monospace'),
    ],

    // Dark Mode
    'dark_mode' => [
        'enabled' => true,
        'default' => 'auto',  // auto, light, dark
        'persist' => true,     // Save user preference
    ],

    // Display Options
    'display' => [
        'title' => env('SWAGGER_UI_TITLE', 'API Documentation'),
        'description' => env('SWAGGER_UI_DESCRIPTION', 'REST API Documentation'),
        'show_models' => true,
        'show_examples' => true,
        'persist_auth' => true,  // Remember auth token
    ],

    // Server Settings
    'server' => [
        'port' => env('SWAGGER_SERVER_PORT', 8000),
        'host' => env('SWAGGER_SERVER_HOST', 'localhost'),
    ],

    // Spec Output
    'spec' => [
        'path' => env('SWAGGER_SPEC_PATH', 'storage/swagger-ui'),
        'filename' => env('SWAGGER_SPEC_FILENAME', 'swagger.json'),
    ],
]
```

---

## 🚀 استفاده سریع

### روش ۱: .env فایل (آسان‌ترین)

ویرایش `.env`:

```env
# Theme Selection
SWAGGER_THEME=tailwind

# Colors (Hex format)
SWAGGER_COLOR_PRIMARY=#8b5cf6
SWAGGER_COLOR_SECONDARY=#d946ef
SWAGGER_COLOR_SUCCESS=#10b981
SWAGGER_COLOR_WARNING=#f59e0b
SWAGGER_COLOR_DANGER=#ef4444

# Fonts
SWAGGER_FONT_FAMILY=system-ui, sans-serif
SWAGGER_FONT_MONO="Fira Code", monospace

# Display
SWAGGER_UI_TITLE=My API Docs
SWAGGER_UI_DESCRIPTION=Complete REST API Documentation

# Server
SWAGGER_SERVER_PORT=8000
SWAGGER_SERVER_HOST=localhost

# Dark Mode
SWAGGER_DARK_MODE_DEFAULT=auto
SWAGGER_DARK_MODE_PERSIST=true
```

سپس:

```bash
php artisan swagger:init --force
php artisan swagger:generate
php artisan swagger:ui
```

---

### روش ۲: Artisan Command (Interactive)

```bash
php artisan swagger:config
```

**Menu گزینه‌ها:**
- Select theme
- Choose color preset
- Customize specific colors
- Set display options

---

### روش ۳: Direct Command Line

```bash
# Change theme
php artisan swagger:config --theme=dark

# Change primary color
php artisan swagger:config --primary-color=#ff5722

# Change title
php artisan swagger:config --title="My API"

# Export to .env format
php artisan swagger:config --export-env
```

---

### روش ۴: Direct Config Edit

Edit `config/module-generator.php`:

```php
'swagger' => [
    'theme' => 'tailwind',
    'colors' => [
        'primary' => '#8b5cf6',
        'secondary' => '#d946ef',
        // ...
    ],
]
```

---

## 🎨 Color Presets

### Blue (پیش‌فرض)
```env
SWAGGER_COLOR_PRIMARY=#3b82f6
SWAGGER_COLOR_PRIMARY_DARK=#1e40af
SWAGGER_COLOR_PRIMARY_LIGHT=#eff6ff
SWAGGER_COLOR_SECONDARY=#06b6d4
```

### Purple
```env
SWAGGER_COLOR_PRIMARY=#8b5cf6
SWAGGER_COLOR_PRIMARY_DARK=#7c3aed
SWAGGER_COLOR_PRIMARY_LIGHT=#f5f3ff
SWAGGER_COLOR_SECONDARY=#d946ef
```

### Green
```env
SWAGGER_COLOR_PRIMARY=#059669
SWAGGER_COLOR_PRIMARY_DARK=#047857
SWAGGER_COLOR_PRIMARY_LIGHT=#ecfdf5
SWAGGER_COLOR_SECONDARY=#14b8a6
```

### Gray
```env
SWAGGER_COLOR_PRIMARY=#6b7280
SWAGGER_COLOR_PRIMARY_DARK=#4b5563
SWAGGER_COLOR_PRIMARY_LIGHT=#f3f4f6
SWAGGER_COLOR_SECONDARY=#9ca3af
```

### Orange
```env
SWAGGER_COLOR_PRIMARY=#f97316
SWAGGER_COLOR_PRIMARY_DARK=#ea580c
SWAGGER_COLOR_PRIMARY_LIGHT=#fff7ed
SWAGGER_COLOR_SECONDARY=#fb923c
```

---

## 📊 مثال: تغییر Theme

### گام ۱: .env را تغییر دهید

```env
SWAGGER_THEME=dark  # از vanilla به dark
```

### گام ۲: Reinitialize کنید

```bash
php artisan swagger:init --force
```

### گام ۳: Generate docs

```bash
php artisan swagger:generate
```

### گام ۴: View

```bash
php artisan swagger:ui
```

---

## 🎭 Theme Options

### Vanilla CSS
- **استفاده:** پیش‌فرض، بسیار سریع
- **Configuration:** CSS variables
- **Dependencies:** ❌ None
- **Customization:** ✅ Colors, Fonts

### Tailwind CSS
- **استفاده:** Modern UI، fully customizable
- **Configuration:** Tailwind config
- **Dependencies:** ✅ CDN
- **Customization:** ✅ Colors، Layout، Typography

### Dark Mode
- **استفاده:** Modern UI with dark toggle
- **Configuration:** Dark mode settings
- **Dependencies:** ✅ CDN
- **Customization:** ✅ Auto/Light/Dark defaults

---

## 📱 Display Options

```env
# UI Display
SWAGGER_UI_TITLE=My Company API
SWAGGER_UI_DESCRIPTION=Enterprise API Documentation
SWAGGER_SHOW_MODELS=true           # Show/hide schema models
SWAGGER_SHOW_EXAMPLES=true         # Show/hide examples
SWAGGER_PERSIST_AUTH=true          # Remember auth token
```

---

## 🌐 Server Configuration

```env
# Server Settings
SWAGGER_SERVER_PORT=8000           # Port to run on
SWAGGER_SERVER_HOST=localhost      # Host to bind to
```

استفاده:

```bash
php artisan swagger:ui              # Default: localhost:8000
php artisan swagger:ui --port=3000  # Custom port
```

---

## 🌙 Dark Mode Configuration

```env
# Dark Mode
SWAGGER_DARK_MODE_ENABLED=true      # Enable/disable feature
SWAGGER_DARK_MODE_DEFAULT=auto      # auto, light, dark
SWAGGER_DARK_MODE_PERSIST=true      # Save user preference
```

**Options:**
- `auto` - Follow system preference
- `light` - Always light by default
- `dark` - Always dark by default
- User can toggle (if enabled)

---

## 📝 تمام تنظیمات

| تنظیم | پیش‌فرض | نوع | توضیح |
|-------|---------|------|--------|
| `SWAGGER_THEME` | vanilla | String | Theme (vanilla/tailwind/dark) |
| `SWAGGER_COLOR_PRIMARY` | #3b82f6 | Hex | Primary color |
| `SWAGGER_COLOR_SECONDARY` | #06b6d4 | Hex | Secondary color |
| `SWAGGER_COLOR_SUCCESS` | #10b981 | Hex | Success color |
| `SWAGGER_COLOR_WARNING` | #f59e0b | Hex | Warning color |
| `SWAGGER_COLOR_DANGER` | #ef4444 | Hex | Danger/Error color |
| `SWAGGER_FONT_FAMILY` | system-ui | String | Default font |
| `SWAGGER_FONT_MONO` | Fira Code | String | Code font |
| `SWAGGER_UI_TITLE` | API Documentation | String | Page title |
| `SWAGGER_UI_DESCRIPTION` | REST API Documentation | String | Description |
| `SWAGGER_SERVER_PORT` | 8000 | Number | Dev server port |
| `SWAGGER_SERVER_HOST` | localhost | String | Dev server host |
| `SWAGGER_DARK_MODE_DEFAULT` | auto | String | Dark mode default |

---

## 🛠️ Command Reference

### تمام Commands

```bash
# Show current configuration
php artisan swagger:config --show

# Interactive configuration
php artisan swagger:config

# Set theme
php artisan swagger:config --theme=dark

# Change colors
php artisan swagger:config --primary-color=#ff5722

# Export configuration as .env
php artisan swagger:config --export-env

# Reset to defaults
php artisan swagger:config --reset

# Initialize with config
php artisan swagger:init --force

# Generate documentation
php artisan swagger:generate

# Start development server
php artisan swagger:ui
```

---

## ✅ راهنمای استفاده

### کاربر جدید

```bash
# 1. Setup config interactively
php artisan swagger:config

# 2. Initialize
php artisan swagger:init

# 3. Generate
php artisan swagger:generate

# 4. View
php artisan swagger:ui
```

### تغییر Theme

```bash
# Edit .env
SWAGGER_THEME=dark

# Apply
php artisan swagger:init --force
```

### تغییر Colors

```bash
# .env
SWAGGER_COLOR_PRIMARY=#8b5cf6

# Apply
php artisan swagger:init --force
php artisan swagger:generate
```

---

## 📂 فایل‌های مرتبط

```
.env                           → Configuration file
config/module-generator.php    → Config class
storage/swagger-ui/            → Generated UI
  ├── index.html               → Active theme
  ├── vanilla-index.html       → Vanilla backup
  ├── tailwind-index.html      → Tailwind theme
  └── dark-mode-index.html     → Dark theme
```

---

## 💡 نکات مهم

1. **تغییرات .env فوری نیستند** - باید `swagger:init --force` را اجرا کنید
2. **Colors باید hex format باشند** - مثل `#3b82f6`
3. **Fonts باید CSS format باشند** - مثل `"Fira Code", monospace`
4. **Dark mode یوزر preference را save میکند** - localStorage استفاده میکند
5. **Port/Host می‌تواند override شود** - توسط command line options

---

## 🚀 نتیجه

اکنون **تمام تنظیمات** از طریق:
- ✅ `.env` فایل
- ✅ `config/module-generator.php`
- ✅ `php artisan swagger:config` command

قابل تغییر هستند **بدون نیاز به ویرایش فایل‌های HTML**! 🎉
