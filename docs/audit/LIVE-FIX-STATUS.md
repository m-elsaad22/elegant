# ارفع الملف الكامل — الموقع مكسور بسبب ملف ناقص

**السبب:** الكود الظاهر فوق الصفحة (`public static function force_kayan_home_query`) اتعرض لأن الملف في `mu-plugins` اتحفظ **من غير** `<?php` في أول سطر. PHP ساعتها يطبع الملف كنص قبل `<!DOCTYPE>`. هيرو الرئيسية لسه على `/blog/` لأن الإضافة الحقيقية ما اشتغلتش.

## اعمل الآتي الآن

1. احذف أي ملف ناقص في `wp-content/mu-plugins/` (خصوصاً أي نسخة فيها `force_kayan_home_query` لوحدها).
2. ارفع **الملف كاملاً** من المستودع:
   `docs/audit/mu-plugins/elegant-live-fix.php`
   إلى:
   `wp-content/mu-plugins/elegant-live-fix.php`
3. أول حرفين لازم يكونوا `<?php` — لو فتحت الملف في File Manager ولاقيت السطر الأول `public static function` يبقى الملف غلط.
4. LiteSpeed Cache → Purge All.
5. افتح `https://elegantswimmingpools.com/` (يفضّل نافذة خاصة).

الإصدار 4.1.0 يخفي شريط `#elegant-3d-dock` العريض (كبسولات واتساب/اتصال بعرض الشاشة) ويرجع أزرار دائرية 56px ثابتة في الزاوية السفلى اليسرى.
