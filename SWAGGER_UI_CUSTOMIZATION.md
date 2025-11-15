# 🎨 Swagger UI - Customization Guide

## چه تغییراتی داشته؟

### ✅ **دو نسخه UI موجود:**

#### 1️⃣ **Vanilla CSS** (پیش‌فرض)
- فایل: `index.html`
- **قابلیت‌ها:**
  - Pure CSS (بدون dependency)
  - Responsive design
  - Modern colors
  - سریع‌تر load شدن

#### 2️⃣ **Tailwind CSS** (جدید)
- فایل: `tailwind-index.html`
- **قابلیت‌ها:**
  - Tailwind CSS CDN
  - Alpine.js برای interactivity
  - کاملاً customizable
  - Production-ready

---

## 🎯 انتخاب کدام نسخه؟

### استفاده از Vanilla CSS (پیش‌فرض)
```bash
php artisan swagger:init
# استفاده می‌کند: storage/swagger-ui/index.html
```

### استفاده از Tailwind
```bash
# یکی از دو روش:

# 1. Copy Tailwind version
cp storage/swagger-ui/tailwind-index.html storage/swagger-ui/index.html

# 2. یا Rename
mv storage/swagger-ui/index.html storage/swagger-ui/vanilla-index.html
mv storage/swagger-ui/tailwind-index.html storage/swagger-ui/index.html
```

---

## 🎨 Customization - Vanilla Version

### تغییر رنگ‌ها

ویرایش `storage/swagger-ui/index.html`:

```html
<style>
    :root {
        /* رنگ‌های اصلی */
        --primary: #3b82f6;           /* آبی */
        --primary-dark: #1e40af;
        --primary-light: #eff6ff;

        --secondary: #06b6d4;         /* فیروزه‌ای */
        --success: #10b981;           /* سبز */
        --warning: #f59e0b;           /* زرد */
        --danger: #ef4444;            /* قرمز */

        --dark: #1f2937;              /* متن تیره */
        --light: #f9fafb;             /* زمینه روشن */
        --border: #e5e7eb;            /* خطوط */
        --text: #374151;              /* متن */
        --text-light: #6b7280;        /* متن کم‌رنگ */
    }
</style>
```

### مثال: تغییر به رنگ‌های سبز

```html
:root {
    --primary: #059669;           /* سبز تیره */
    --primary-dark: #047857;
    --primary-light: #ecfdf5;

    --secondary: #14b8a6;         /* تیرکوازی */
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
}
```

### تغییر Font

```html
html {
    font-family: 'Your Font', system-ui, sans-serif;
}

code, pre {
    font-family: 'Fira Code', 'Courier New', monospace;
}
```

### تغییر Background

```html
body {
    background: linear-gradient(135deg, #your-color-1 0%, #your-color-2 100%);
}
```

---

## 🎨 Customization - Tailwind Version

### تغییر Color Palette

ویرایش `tailwind-index.html`:

```html
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#your-color-50',
                        100: '#your-color-100',
                        200: '#your-color-200',
                        // ... تا 900
                    },
                }
            }
        }
    }
</script>
```

### مثال: Palette قرمز/صورتی

```html
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#fdf2f8',
                        100: '#fce7f3',
                        200: '#fbcfe8',
                        300: '#f8a5d8',
                        400: '#f472b6',
                        500: '#ec4899',
                        600: '#db2777',
                        700: '#be185d',
                        800: '#9d174d',
                        900: '#831843',
                    },
                }
            }
        }
    }
</script>
```

### تغییر Layout

```html
<!-- Desktop: 1/4 + 3/4 -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Sidebar 1/4 -->
    <!-- Content 3/4 -->
</div>

<!-- اگر می‌خوای 1/3 + 2/3 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Sidebar 1/3 -->
    <!-- Content 2/3 -->
</div>
```

### تغییر Header

```html
<!-- Dark header -->
<header class="bg-slate-900 text-white">
    <!-- ... -->
</header>

<!-- Gradient header -->
<header class="bg-gradient-to-r from-primary-600 to-cyan-600">
    <!-- ... -->
</header>
```

---

## 📝 UI Elements Reference

### Method Badges

```html
<!-- آبی برای GET -->
<span class="bg-blue-100 text-blue-700">GET</span>

<!-- سبز برای POST -->
<span class="bg-green-100 text-green-700">POST</span>

<!-- زرد برای PUT/PATCH -->
<span class="bg-amber-100 text-amber-700">PUT</span>

<!-- قرمز برای DELETE -->
<span class="bg-red-100 text-red-700">DELETE</span>
```

### Status Colors

```html
<!-- 2xx - Green -->
<div class="bg-green-50 border-green-200">Success</div>

<!-- 4xx - Yellow -->
<div class="bg-amber-50 border-amber-200">Client Error</div>

<!-- 5xx - Red -->
<div class="bg-red-50 border-red-200">Server Error</div>
```

---

## 🔧 Advanced Customization

### اضافه کردن Dark Mode

```html
<script>
function apiDocs() {
    return {
        darkMode: false,

        // CSS classes تغییر بدهند
        get bgClass() {
            return this.darkMode ? 'bg-slate-900' : 'bg-white';
        },

        get textClass() {
            return this.darkMode ? 'text-white' : 'text-slate-900';
        }
    }
}
</script>
```

### اضافه کردن Logo

```html
<div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-cyan-500 rounded-lg flex items-center justify-center text-white font-bold text-xl">
    YOUR LOGO
</div>
```

### اضافه کردن Theme Toggle

```html
<button @click="theme = theme === 'light' ? 'dark' : 'light'"
    class="p-2 hover:bg-slate-100 rounded-lg transition">
    <span x-show="theme === 'light'">🌙</span>
    <span x-show="theme === 'dark'">☀️</span>
</button>
```

---

## 📱 Responsive Design

### Breakpoints (Tailwind)

```html
<!-- Mobile -->
<div class="block lg:hidden">Mobile Menu</div>

<!-- Desktop -->
<div class="hidden lg:block">Desktop Sidebar</div>

<!-- Tablet -->
<div class="hidden md:block lg:hidden">Tablet</div>
```

### Grid Layouts

```html
<!-- 1 col on mobile, 2 on tablet, 4 on desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
</div>
```

---

## 🎨 Color Schemes

### Professional Blue
```css
primary: #2563eb (Blue-600)
secondary: #06b6d4 (Cyan-500)
```

### Modern Purple
```css
primary: #7c3aed (Violet-600)
secondary: #ec4899 (Pink-500)
```

### Green & Teal
```css
primary: #059669 (Green-600)
secondary: #14b8a6 (Teal-500)
```

### Corporate Gray
```css
primary: #475569 (Slate-700)
secondary: #64748b (Slate-500)
```

### Energetic Orange
```css
primary: #ea580c (Orange-600)
secondary: #f59e0b (Amber-500)
```

---

## 🔍 CSS Customization Examples

### مثال ۱: سفارشی‌سازی کامل

```html
<style>
    /* تغییر رنگ اصلی */
    :root {
        --primary: #8b5cf6;           /* بنفش */
        --primary-dark: #7c3aed;
        --primary-light: #f5f3ff;
        --secondary: #06b6d4;         /* فیروزه */
    }

    /* تغییر Header */
    header {
        background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 100%);
        color: white;
    }

    /* تغییر Sidebar */
    .sidebar-link.active {
        background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    }

    /* تغییر Endpoint Cards */
    .endpoint {
        border: 2px solid var(--border);
        transition: all 0.3s ease;
    }

    .endpoint:hover {
        border-color: var(--primary);
        box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2);
    }
</style>
```

### مثال ۲: RTL Support (فارسی)

```html
<html lang="fa" dir="rtl">
<style>
    body {
        text-align: right;
    }

    .sidebar {
        order: 2;
        margin-right: 0;
        margin-left: 2rem;
    }

    .main-content {
        order: 1;
    }

    .endpoint-path {
        direction: ltr;
        text-align: left;
    }
</style>
```

---

## 📦 Using with Tailwind Project

اگر پروژه‌ات از Tailwind استفاده می‌کنه:

### خط ۱: استفاده از Tailwind Version
```bash
cp src/Stubs/SwaggerUI/tailwind-index.html storage/swagger-ui/index.html
```

### خط ۲: تطابق Colors
```js
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: {
          // همان colors پروژه‌ات
        }
      }
    }
  }
}
```

---

## 🚀 Production Setup

### CDN Links (Tailwind Version)

```html
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### Self-hosted Option

```html
<!-- اگر CDN برای تو مشکل‌ساز بود -->
<link rel="stylesheet" href="/css/tailwind.css">
<script src="/js/alpine.js"></script>
```

---

## 💡 Tips & Tricks

### Tip 1: Override Styles
```html
<style>
    /* Your custom CSS here - بعد از Tailwind load شدن -->
    .custom-endpoint {
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }
</style>
```

### Tip 2: Animation
```css
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.endpoint {
    animation: fadeIn 0.3s ease-out;
}
```

### Tip 3: Font Import
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', system-ui, sans-serif;
    }
</style>
```

---

## 📚 Files

| فایل | نوع | استفاده |
|-----|------|---------|
| `index.html` | Vanilla CSS | پیش‌فرض |
| `tailwind-index.html` | Tailwind + Alpine | Production |
| `swagger.json` | Config | اسامپل |
| `.htaccess` | Routing | SPA routing |

---

## ✅ Next Steps

1. **انتخاب Version:** Vanilla یا Tailwind
2. **Customize Colors:** رنگ‌های خود را بروز کنید
3. **Add Logo:** لوگو شرکت خود اضافه کنید
4. **Deploy:** در production استقرار دهید

---

**سوالی داری؟** README فایل‌ها رو ببین یا documentation رو دنبال کن! 🚀
