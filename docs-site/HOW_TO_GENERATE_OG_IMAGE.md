# چطور تصویر OG را بسازیم؟

## ⚠️ نکته مهم
**Puppeteer دیگر نیاز نیست!** از package.json حذف شده است.

## روش ساده (بدون نیاز به نصب چیزی):

### مرحله 1: باز کردن قالب
1. فایل `og-image-template.html` را در مرورگر Chrome یا Firefox باز کنید
2. صفحه را به صورت کامل لود کنید

### مرحله 2: دانلود تصویر
روی دکمه **"📥 Download OG Image (PNG)"** کلیک کنید

### مرحله 3: ذخیره فایل
فایل دانلود شده را با نام `og-image.png` در پوشه `public/` ذخیره کنید

### مرحله 4: Deploy
```bash
npm run generate
# یا
npm run build
```

## ✅ تمام!

تصویر OG شما آماده است و در شبکه‌های اجتماعی نمایش داده می‌شود.

---

## روش جایگزین: اسکرین‌شات دستی

اگر دکمه دانلود کار نکرد:

1. فایل `og-image-template.html` را در Chrome باز کنید
2. F12 را بزنید (Developer Tools)
3. به تب "Elements" بروید
4. روی المنت با کلاس `.og-image` راست کلیک کنید
5. "Capture node screenshot" را انتخاب کنید
6. فایل را با نام `og-image.png` در `public/` ذخیره کنید

---

## روش حرفه‌ای: طراحی سفارشی

می‌توانید از ابزارهای آنلاین استفاده کنید:

- **Canva**: [canva.com](https://canva.com) - ابعاد: 1200×630
- **Figma**: [figma.com](https://figma.com) - Frame با ابعاد 1200×630
- **Photoshop/GIMP**: طراحی دلخواه با ابعاد 1200×630

---

## تست کردن

بعد از deploy، تصویر را در این سایت‌ها تست کنید:

- [Facebook Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

---

## مشخصات تصویر

- **ابعاد**: 1200 × 630 پیکسل
- **فرمت**: PNG یا JPG
- **حجم**: زیر 1MB (ترجیحاً زیر 300KB)
- **نسبت**: 1.91:1
