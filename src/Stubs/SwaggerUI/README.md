# 🎨 Swagger UI Themes

ُ## ۳ نسخه کامل Swagger UI

### 📁 فایل‌ها

| فایل | نوع | استفاده |
|-----|------|---------|
| `index.html` | Vanilla CSS | پیش‌فرض - هیچ dependency نیست |
| `tailwind-index.html` | Tailwind + Alpine | کاملاً customizable |
| `dark-mode-index.html` | Dark Mode | Modern UI + Dark Toggle |

---

## 🚀 سریع‌ترین راه

### گام ۱: انتخاب Theme

```bash
cd /path/to/project
./switch-swagger-theme.sh tailwind
```

**گزینه‌ها:**
- `vanilla` - پیش‌فرض (بدون dependency)
- `tailwind` - Tailwind CDN (fully customizable)
- `dark` - Dark Mode (toggle included)

### گام ۲: Generate

```bash
php artisan swagger:generate
```

### گام ۳: View

```bash
php artisan swagger:ui
```

---

## 🎨 تغییر رنگ‌ها

### Vanilla Theme
Edit `storage/swagger-ui/index.html`:

```html
<style>
    :root {
        --primary: #3b82f6;        /* تغییر رنگ */
        --secondary: #06b6d4;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }
</style>
```

### Tailwind Theme
Edit `storage/swagger-ui/index.html`:

```html
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        50: '#f0f9ff',
                        500: '#22c55e',   /* Change here */
                        600: '#16a34a',
                    }
                }
            }
        }
    }
</script>
```

---

## 🎯 Which Theme to Choose?

### Vanilla ✅
- If you want **zero dependencies**
- If you want **fast loading**
- If you understand **CSS**

### Tailwind ✅
- If you want **full customization**
- If you use **Tailwind in your project**
- If you want **easy color switching**

### Dark Mode ✅
- If you want **modern UI**
- If users prefer **dark mode**
- If you want **user preference saved**

---

## 📱 Responsive

All themes are **fully responsive**:
- Mobile (320px)
- Tablet (768px)
- Desktop (1024px)

---

## 📚 More Info

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
