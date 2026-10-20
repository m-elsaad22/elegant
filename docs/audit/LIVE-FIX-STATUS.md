# ارفع الملف الكامل — إصلاح أخطاء Google Search Console

الإصدار **5.0.0** يعالج أسباب تقرير الفهرسة (12 أكتوبر 2026): 404، noindex، خرائط وهمية، `/page/2/`، ومحتوى خارج الإمارات. ارفع الملف **كاملاً**. أول سطر لازم يكون `<?php`.

## اعمل الآتي الآن

1. ارفع **الملف كاملاً** من المستودع:
   `docs/audit/mu-plugins/elegant-live-fix.php`
   إلى:
   `wp-content/mu-plugins/elegant-live-fix.php`
2. استبدل `public_html/robots.txt` بالنسخة:
   `docs/audit/cpanel/robots.txt`
   (الملف الحالي ملف ثابت، وجوجل بيطلب منه 21 خريطة كلها 404).
3. ادمج قواعد `docs/audit/cpanel/htaccess-elegant-live-fix.txt` داخل `public_html/.htaccess` من غير ما تمسح بلوك LiteSpeed/Wordfence.
4. في File Manager احذف مجلدات السبام إن وُجدت (أو سيّبها للإضافة تنقلها):
   - `public_html/contact-us/` (صفحة قمار KOKO5000 وليست صفحة ووردبريس)
   - `public_html/cities/` (صفحة قمار VIP288)
5. LiteSpeed Cache → **Purge All** وأي خيار لمسح كاش الـ 404.
6. افتح `https://elegantswimmingpools.com/` ثم:
   - `https://elegantswimmingpools.com/robots.txt` (سطر Sitemap واحد: `/?sitemap=1`)
   - `https://elegantswimmingpools.com/?sitemap=1`
   - `https://elegantswimmingpools.com/about-us/` (بعد إصلاح الـ rewrite)
7. في Search Console: احذف الخرائط `sitemap.xml` و`sitemap-1.xml`…`sitemap-20.xml`، وأضف:
   `https://elegantswimmingpools.com/?sitemap=1`

## ماذا يصلح 5.0.0

| تقرير GSC | السبب على الموقع | الإصلاح |
|---|---|---|
| 112 صفحة 404 | `robots.txt` يعلن خرائط `sitemap-N.xml` غير موجودة، وروابط Rank Math `*-sitemap.xml` و`/page/2/` وpermalinks لا تصل لووردبريس | robots صحيح + rewrite للخرائط + 301 لـ `/page/N/` |
| 699 اكتُشفت ولم تُفهرس | جوجل لقى آلاف روابط الخرائط الوهمية والمقالات الرقيقة | خريطة واحدة تعمل + إسقاط مدن خارج الإمارات من الخريطة |
| 278 تم الزحف ولم تُفهرس | مقالات مولَّدة لينبع/جازان/المنصورة/السعودية… | `noindex` + حذفها من Sitemap. الإبقاء على أبوظبي/دبي/الإمارات |
| 9 noindex | طبيعي لخرائط XML وبعض الصفحات | لا نلغي noindex الخرائط. صفحة `/sitemap/` الفارغة تبقى noindex |
| 7 canonical بديل | إشارة معلوماتية | تُترك |
| أزرار واتساب/اتصال | تبقى دائرية في الزاوية `#elegant-corner-fabs` | بدون تغيير |

الإضافة تحاول كتابة `robots.txt` و`.htaccess` وعزل مجلدات السبام تلقائياً عند أول زيارة بعد الرفع. لو صلاحيات الملفات تمنع الكتابة، نفّذ الخطوات 2–4 يدوياً من cPanel.
