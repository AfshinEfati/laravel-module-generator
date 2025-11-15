# 🎨 Swagger UI Themes

## ۳ نسخه کامل Swagger UI

### 📁 فایل‌ها

| فایل | نوع | استفاده |
|-----|------|---------|
| `index.html` | Vanilla CSS | پیش‌فرض - هیچ dependency نیست |
| `tailwind-index.html` | Tailwind + Alpine | کاملاً customizable |
| `dark-mode-index.html` | Dark Mode | Modern UI + Dark Toggle |

---

## 🚀 سریع‌ترین راه

### روش ۱: Config File (.env) - **پیشنهاد شده**

Edit `.env`:

```env
SWAGGER_THEME=tailwind
SWAGGER_COLOR_PRIMARY=#8b5cf6
SWAGGER_UI_TITLE=My API
```

سپس:

```bash
php artisan swagger:init --force
php artisan swagger:generate
php artisan swagger:ui
```

**فایل‌های مرتبط:**
- `.env.swagger.example` - تمام تنظیمات موجود
- `config/module-generator.php` - پیکربندی

---

### روش ۲: Interactive Command

```bash
php artisan swagger:config
# Menu برای انتخاب theme و colors
```

---

### روش ۳: Direct Command

```bash
php artisan swagger:config --theme=dark --primary-color=#ff5722
```

---

### روش ۴: Bash Script (قدیم)

```bash
./switch-swagger-theme.sh tailwind  # Manual theme switching
```

---

## 🎨 تغییر رنگ‌ها - از طریق .env

### مثال ۱: تغییر Theme

```env
# .env
SWAGGER_THEME=dark
```

سپس: `php artisan swagger:init --force`

---

### مثال ۲: Purple Color Scheme

```env
SWAGGER_THEME=tailwind
SWAGGER_COLOR_PRIMARY=#8b5cf6
SWAGGER_COLOR_PRIMARY_DARK=#7c3aed
SWAGGER_COLOR_PRIMARY_LIGHT=#f5f3ff
SWAGGER_COLOR_SECONDARY=#d946ef
```

سپس: `php artisan swagger:init --force`

---

### مثال ۳: Dark Mode Configuration

```env
SWAGGER_THEME=dark
SWAGGER_DARK_MODE_DEFAULT=dark      # Start dark by default
SWAGGER_DARK_MODE_PERSIST=true      # Remember user choice
```

---

## 📋 تمام تنظیمات

| تنظیم | مثال | توضیح |
|--------|------|--------|
| `SWAGGER_THEME` | `tailwind` | vanilla, tailwind, dark |
| `SWAGGER_COLOR_PRIMARY` | `#8b5cf6` | رنگ اصلی |
| `SWAGGER_COLOR_SECONDARY` | `#d946ef` | رنگ ثانویه |
| `SWAGGER_UI_TITLE` | `My API` | عنوان صفحه |
| `SWAGGER_DARK_MODE_DEFAULT` | `auto` | auto, light, dark |
| `SWAGGER_SERVER_PORT` | `8000` | پورت سرور |

👉 **اطلاعات کامل:** `SWAGGER_CONFIG.md`

---

## 🚀 Quick Start

```bash
# 1. Edit .env
nano .env
# Set SWAGGER_THEME=dark

# 2. Apply
php artisan swagger:init --force

# 3. Generate
php artisan swagger:generate

# 4. View
php artisan swagger:ui
```

---

## ✅ All Themes Include

- ✅ Responsive design
- ✅ Method badges (GET/POST/PUT/PATCH/DELETE)
- ✅ Status code colors
- ✅ Parameter extraction
- ✅ Response display
- ✅ Search functionality
- ✅ Copy-to-clipboard
- ✅ Beautiful UI

---

## 📚 More Info

👉 See `SWAGGER_CONFIG.md` for:
- All configuration options
- .env setup
- Command reference
- Color presets

👉 See `SWAGGER_UI_CUSTOMIZATION.md` for:
- ۵ color palette examples
- CSS customization
- Tailwind config
- RTL support

👉 See `SWAGGER_UI_THEMES.md` for:
- Detailed comparison
- Feature matrix
- Pro tips
- Troubleshooting

---

## 💡 Tips

1. **Try different themes** - Choose what works best for you
2. **Backup before switching** - Script creates automatic backup
3. **Customize gradually** - Start with color, then layout
4. **Test on mobile** - Make sure responsive design works
5. **Check dark mode** - Test both light and dark modes

---

## 🆘 Troubleshooting

| Problem | Solution |
|---------|----------|
| Theme doesn't change | Clear browser cache |
| CDN not loading | Check internet connection |
| Dark mode not working | Check if Alpine.js loaded |
| Colors not applying | Check CSS syntax |

---

## ✅ All Themes Include

- ✅ Responsive design
- ✅ Method badges (GET/POST/PUT/PATCH/DELETE)
- ✅ Status code colors
- ✅ Parameter extraction
- ✅ Response display
- ✅ Search functionality
- ✅ Copy-to-clipboard
- ✅ Beautiful UI

---

**Ready? Start with:**

```bash
./switch-swagger-theme.sh tailwind
php artisan swagger:generate
php artisan swagger:ui
```
