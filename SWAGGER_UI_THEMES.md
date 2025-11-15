# 🎨 Swagger UI - Complete Customization & Theming Guide

## 📊 Summary

### ✅ چه ساخته شده؟

**۳ نسخه کامل UI:**

| نسخه | فایل | ویژگی‌ها | استفاده |
|------|------|---------|---------|
| **Vanilla** | `index.html` | CSS خالص، بدون dependency | پیش‌فرض |
| **Tailwind** | `tailwind-index.html` | Tailwind CDN + Alpine.js | Full Customization |
| **Dark Mode** | `dark-mode-index.html` | Tailwind + Dark Mode Toggle | Modern UI |

---

## 🎯 کدام نسخه انتخاب کنم؟

### ✅ Vanilla اگر:
- می‌خوای سریع‌ترین load
- dependency نمی‌خوای
- CSS خوب دانی و می‌خوای customize کنی

### ✅ Tailwind اگر:
- می‌خوای کاملاً customizable
- Tailwind اصلاً توی پروژه‌ات داری
- theme switch می‌خوای

### ✅ Dark Mode اگر:
- می‌خوای modern UI
- users prefer dark mode می‌خوای support کنی
- بهترین user experience می‌خوای

---

## 🚀 How to Switch Themes

### راه ۱: Bash Script (سریع‌ترین)

```bash
# مثال Vanilla (پیش‌فرض)
./switch-swagger-theme.sh vanilla

# مثال Tailwind
./switch-swagger-theme.sh tailwind

# مثال Dark Mode
./switch-swagger-theme.sh dark
```

### راه ۲: Manual Copy

```bash
# From vanilla to tailwind
cp storage/swagger-ui/index.html storage/swagger-ui/vanilla-backup.html
cp src/Stubs/SwaggerUI/tailwind-index.html storage/swagger-ui/index.html

# From any to dark mode
cp src/Stubs/SwaggerUI/dark-mode-index.html storage/swagger-ui/index.html
```

### راه ۳: PHP Artisan Command

```bash
php artisan swagger:init --force
# سپس select نسخه‌ای که می‌خوای
```

---

## 🎨 **Vanilla CSS - Customization**

### تغییر رنگ‌ها

فایل: `storage/swagger-ui/index.html`

```html
<style>
    :root {
        /* رنگ‌های اصلی */
        --primary: #3b82f6;           /* آبی (default) */
        --primary-dark: #1e40af;
        --primary-light: #eff6ff;

        --secondary: #06b6d4;         /* فیروزه */
        --success: #10b981;           /* سبز */
        --warning: #f59e0b;           /* زرد */
        --danger: #ef4444;            /* قرمز */
    }
</style>
```

### مثال: تغییر به سفز

```html
:root {
    --primary: #059669;           /* Green-600 */
    --primary-dark: #047857;      /* Green-700 */
    --primary-light: #ecfdf5;     /* Green-50 */
    --secondary: #14b8a6;         /* Teal-500 */
}
```

### مثال: تغییر به بنفش

```html
:root {
    --primary: #8b5cf6;           /* Violet-600 */
    --primary-dark: #7c3aed;      /* Violet-700 */
    --primary-light: #f5f3ff;     /* Violet-50 */
    --secondary: #d946ef;         /* Fuchsia-500 */
}
```

### تغییر Font

```html
html {
    font-family: 'Your Font', system-ui, sans-serif;
}

code, pre {
    font-family: 'Fira Code', monospace;
}
```

### تغییر Background

```html
body {
    /* Solid color */
    background: #f0f9ff;

    /* Gradient */
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
}
```

---

## 🎨 **Tailwind CSS - Full Customization**

### Color Palette Customization

فایل: `storage/swagger-ui/index.html` (بعد از copy کردن tailwind version)

```html
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#f0fdf4',   /* Light */
                        100: '#dcfce7',
                        200: '#bbf7d0',
                        300: '#86efac',
                        400: '#4ade80',
                        500: '#22c55e',  /* Main */
                        600: '#16a34a',  /* Dark */
                        700: '#15803d',
                        800: '#166534',
                        900: '#145231',  /* Very Dark */
                    },
                }
            }
        }
    }
</script>
```

### Color Sets (آماده شده)

```html
<!-- Purple Set -->
<script>
    primary: {
        50: '#faf5ff',
        100: '#f3e8ff',
        // ... تا 900: '#4c0519'
    }
</script>

<!-- Red Set -->
<script>
    primary: {
        50: '#fef2f2',
        // ... تا 900: '#7f1d1d'
    }
</script>

<!-- Orange Set -->
<script>
    primary: {
        50: '#fff7ed',
        // ... تا 900: '#7c2d12'
    }
</script>
```

### Layout Customization

```html
<!-- تغییر sidebar width -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- 1/3 -->
</div>

<!-- تغییر max-width -->
<div class="max-w-5xl mx-auto">
    <!-- narrower -->
</div>
```

### Header Customization

```html
<!-- Dark Header -->
<header class="bg-slate-900 text-white">

<!-- Gradient Header -->
<header class="bg-gradient-to-r from-primary-600 to-cyan-600">

<!-- Minimal Header -->
<header class="border-b border-slate-200">
```

---

## 🌙 **Dark Mode - Advanced**

### Dark Mode Toggle (auto-save)

فایل: `dark-mode-index.html` میخهarة داره:

```html
<button @click="darkMode = !darkMode">
    <span x-show="!darkMode">🌙</span>
    <span x-show="darkMode">☀️</span>
</button>
```

Preference save می‌شه در `localStorage`

### Customize Dark Colors

```html
<!-- تغییر dark background -->
<body :class="{ 'dark': darkMode }" class="bg-white dark:bg-slate-950">

<!-- تغییر dark text -->
<h1 class="text-slate-900 dark:text-white">

<!-- تغییر dark card -->
<div class="bg-white dark:bg-slate-900">
```

---

## 📝 CSS Changes Reference

### عناصر Customizable

#### Method Badges
```css
.method-get { background: #3b82f6; }      /* Blue */
.method-post { background: #10b981; }     /* Green */
.method-put { background: #f59e0b; }      /* Amber */
.method-patch { background: #8b5cf6; }    /* Purple */
.method-delete { background: #ef4444; }   /* Red */
```

#### Status Colors
```css
/* Success (2xx) */
.response-2xx { background: #d1fae5; border: #6ee7b7; }

/* Client Error (4xx) */
.response-4xx { background: #fef3c7; border: #fcd34d; }

/* Server Error (5xx) */
.response-5xx { background: #fee2e2; border: #fca5a5; }
```

#### Interactive Elements
```css
button:hover { transform: translateY(-2px); }
button:active { transform: translateY(0); }
.endpoint:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
```

---

## 🎯 Complete Customization Examples

### مثال ۱: Professional Blue Theme

```html
<!-- tailwind-index.html یا dark-mode-index.html -->
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#eff6ff',
                        100: '#dbeafe',
                        200: '#bfdbfe',
                        300: '#93c5fd',
                        400: '#60a5fa',
                        500: '#3b82f6',  /* Main Blue */
                        600: '#2563eb',
                        700: '#1d4ed8',
                        800: '#1e40af',
                        900: '#1e3a8a',
                    }
                }
            }
        }
    }
</script>

<!-- Header Gradient -->
<header class="bg-gradient-to-r from-primary-600 to-cyan-500">
```

### مثال ۲: Dark Modern Theme

```html
<!-- dark-mode-index.html -->
<!-- Default dark mode ON -->
<script>
    function apiDocs() {
        return {
            darkMode: true,  // Start dark
            // ...
        }
    }
</script>
```

### مثال ۳: High Contrast Theme

```html
<!-- Vanilla index.html -->
<style>
    :root {
        --primary: #000000;
        --secondary: #ffffff;
        --danger: #ff0000;
        --success: #00aa00;
    }

    .endpoint:hover {
        filter: invert(1);
    }
</style>
```

---

## 🔧 Files Summary

| فایل | شامل | استفاده |
|-----|------|---------|
| `index.html` | Vanilla CSS | Active theme |
| `tailwind-index.html` | Tailwind + Alpine | Alternative |
| `dark-mode-index.html` | Tailwind + Dark | Alternative |
| `vanilla-index.html` | (بعد از backup) | Fallback |
| `switch-swagger-theme.sh` | Theme switcher | CLI tool |

---

## 🚀 Quick Start

### ۱. انتخاب Theme

```bash
./switch-swagger-theme.sh tailwind
```

### ۲. Generate Docs

```bash
php artisan swagger:generate
```

### ۳. View

```bash
php artisan swagger:ui
```

### ۴. Customize

ویرایش `storage/swagger-ui/index.html` براساس theme

---

## 📱 Responsive Design

### Breakpoints
```html
<!-- Mobile -->
grid-cols-1

<!-- Tablet (md) -->
md:grid-cols-2

<!-- Desktop (lg) -->
lg:grid-cols-4
```

### تغییر Layout

```html
<!-- 3-column default -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

<!-- 2-column default -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2">

<!-- Full-width -->
<div class="w-full">
```

---

## 💡 Pro Tips

### Tip 1: Use CSS Variables (Vanilla)
```css
/* سریع‌تر تغییر */
:root {
    --font-sans: 'Inter', system-ui;
    --font-mono: 'Fira Code', monospace;
    --rounded: 8px;
    --shadow: 0 1px 3px rgba(0,0,0,0.1);
}

body { font-family: var(--font-sans); }
code { font-family: var(--font-mono); }
```

### Tip 2: Create Theme Presets (Tailwind)
```bash
# Create different configs
tailwind-blue.html
tailwind-green.html
tailwind-purple.html

# Switch easily
./switch-swagger-theme.sh tailwind-blue
```

### Tip 3: Export Theme as JSON
```bash
# Save preferences
{
  "theme": "tailwind",
  "darkMode": true,
  "colors": {
    "primary": "#8b5cf6"
  }
}
```

---

## ❓ Troubleshooting

| مشکل | حل |
|------|-----|
| Theme تغییر نمی‌کند | Cache clear کن: Clear browser cache |
| Dark mode کار نمی‌کنه | Alpine.js بارگذاری نشده؟ CDN check کن |
| Tailwind styles نشون نمی‌دن | CDN internet check کن |
| Script error | browser console check کن (F12) |

---

## 📚 Further Resources

- [Tailwind Color Palette](https://tailwindcss.com/docs/customization/colors)
- [Alpine.js Docs](https://alpinejs.dev/)
- [CSS Variables](https://developer.mozilla.org/en-US/docs/Web/CSS/var)

---

**آماده‌ای؟ شروع کن:**

```bash
./switch-swagger-theme.sh tailwind
php artisan swagger:generate
php artisan swagger:ui
```

بعد ویرایش کن تا `storage/swagger-ui/index.html` براساس سلیقه‌ات 🎨
