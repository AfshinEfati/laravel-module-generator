# راهنمای تولید تصویر OG

## روش 1: استفاده از مرورگر (ساده‌ترین)

1. فایل `og-image-template.html` را در مرورگر باز کنید
2. F12 را بزنید و به Developer Tools بروید
3. در Console این کد را اجرا کنید:

```javascript
// انتخاب المنت تصویر OG
const element = document.querySelector('.og-image');

// تبدیل به canvas
html2canvas(element, {
  width: 1200,
  height: 630,
  scale: 2
}).then(canvas => {
  // دانلود به عنوان PNG
  const link = document.createElement('a');
  link.download = 'og-image.png';
  link.href = canvas.toDataURL('image/png');
  link.click();
});
```

**نکته:** اگر `html2canvas` در دسترس نیست، ابتدا این کد را اجرا کنید:
```javascript
const script = document.createElement('script');
script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
document.head.appendChild(script);
```

## روش 2: اسکرین‌شات دستی

1. فایل `og-image-template.html` را در Chrome باز کنید
2. F12 را بزنید
3. به تب "Elements" بروید
4. روی المنت با کلاس `.og-image` راست کلیک کنید
5. "Capture node screenshot" را انتخاب کنید
6. فایل را با نام `og-image.png` در پوشه `public/` ذخیره کنید

## روش 3: استفاده از Puppeteer (خودکار)

اگر Puppeteer نصب کردید:

```bash
npm install puppeteer
npm run generate:og
```

## روش 4: ابزارهای آنلاین

### استفاده از Canva:
1. به [Canva.com](https://canva.com) بروید
2. "Custom size" را انتخاب کنید: 1200 × 630 پیکسل
3. طراحی مشابه `og-image-template.html` را بسازید
4. دانلود به عنوان PNG

### استفاده از Figma:
1. به [Figma.com](https://figma.com) بروید
2. Frame جدید با ابعاد 1200 × 630 ایجاد کنید
3. طراحی را بسازید
4. Export به عنوان PNG با scale 2x

## بعد از تولید تصویر:

1. فایل `og-image.png` را در پوشه `public/` قرار دهید
2. مطمئن شوید که `nuxt.config.ts` به `.png` اشاره می‌کند (قبلاً انجام شده)
3. سایت را build و deploy کنید
4. تست کنید در:
   - [Facebook Debugger](https://developers.facebook.com/tools/debug/)
   - [Twitter Card Validator](https://cards-dev.twitter.com/validator)
   - [LinkedIn Inspector](https://www.linkedin.com/post-inspector/)

## نکات مهم:

- ✅ ابعاد دقیق: 1200 × 630 پیکسل
- ✅ فرمت: PNG یا JPG
- ✅ حجم: زیر 1MB (ترجیحاً زیر 300KB)
- ✅ نسبت ابعاد: 1.91:1
- ✅ لوگوی Laravel باید واضح و قابل مشاهده باشد
