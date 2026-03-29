<!DOCTYPE html>
<html lang="ar" dir="rtl">
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> --}}
    <head>
            <script>window.currentUserId = window.currentUserId || null;</script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- مكتبة SheetJS Excel - يجب أن تكون قبل أي كود جافاسكريبت آخر -->
    <script src="/js/xlsx.full.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Flatpickr Date Picker - محلي لجميع الصفحات -->
    <link rel="stylesheet" href="/js/flatpickr.min.css">
    <script src="/js/flatpickr.min.js"></script>
    <script src="/js/ar.js"></script>

        <!-- خط عربي كلاسيكي -->
        <!-- خط Amiri من Google Fonts تم حذفه لتقليل الاعتماد على الإنترنت -->

        <style>
                        /* شكل المؤشر في حقول التاريخ */
                        input[type="text"][placeholder*="yyyy"] {
                            cursor: pointer !important;
                        }
            /* تأثير دخول هادئ للمحتوى */
            @keyframes page-fade-up {
                from {
                    opacity: 0;
                    transform: translateY(8px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* تنسيق placeholder للحقول النصية لجعل صيغة التاريخ أكثر وضوحاً */
            input[type="text"]::placeholder {
                color: #6B7280;
                font-style: italic;
                opacity: 1;
                font-weight: 500;
            }

            /* placeholder في حقول التاريخ يبقى مرئياً حتى مع القيم الفارغة */
            input[type="text"][placeholder*="yyyy"]::placeholder,
            .js-order-date::placeholder,
            .js-start-date::placeholder,
            .js-end-date::placeholder {
                color: #3B82F6;
                opacity: 0.9;
                font-style: italic;
                font-weight: 600;
            }

            input[type="text"]:placeholder-shown {
                background-color: #F9FAFB;
            }

            /* تأثير بصري لحقول التاريخ الفارغة */
            .js-order-date:empty,
            .js-start-date:empty,
            .js-end-date:empty {
                background-color: #F3F4F6;
            }

            #app-main-content {
                animation: page-fade-up 420ms ease-out;
            }

            #hadithTickerViewport {
                position: relative;
                min-width: 0;
                flex: 1;
                overflow: hidden;
            }

            #hadithTickerText {
                display: inline-block;
                min-width: 100%;
                white-space: nowrap;
                transition: opacity 320ms ease;
                font-family: 'Amiri', 'Traditional Arabic', 'Times New Roman', serif;
                font-weight: 700;
                font-size: 15px;
                line-height: 1.6;
            }

            @media (min-width: 640px) {
                #hadithTickerText {
                    font-size: 17px;
                }
            }

            .hadith-ticker-fade-out {
                opacity: 0;
            }

            .hadith-ticker-fade-in {
                opacity: 1;
            }

            .hadith-ticker-marquee {
                animation-name: hadith-ticker-marquee;
                animation-duration: var(--ticker-scroll-duration, 18s);
                animation-timing-function: linear;
                animation-iteration-count: infinite;
                animation-direction: alternate;
            }

            @keyframes hadith-ticker-marquee {
                from {
                    transform: translateX(0);
                }
                to {
                    transform: translateX(calc(-1 * var(--ticker-scroll-distance, 0px)));
                }
            }

            #hadithRefreshBtn {
                cursor: pointer;
                padding: 4px 8px;
                border: none;
                background: transparent;
                color: #059669;
                font-size: 16px;
                transition: transform 200ms ease, opacity 200ms ease;
                flex-shrink: 0;
            }

            #hadithRefreshBtn:hover {
                transform: rotate(20deg) scale(1.1);
                opacity: 0.8;
            }

            #hadithRefreshBtn:active {
                transform: rotate(20deg) scale(0.95);
            }

            #hadithRefreshBtn.refreshing {
                animation: refresh-spin 500ms cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }

            @keyframes refresh-spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                #app-main-content,
                #hadithTickerText {
                    animation: none !important;
                    transition: none !important;
                }

                .hadith-ticker-marquee {
                    animation: none !important;
                }
            }

            /* شريط الأدوات العائم الموحد */
            .floating-toolbar-container {
                display: flex;
                justify-content: center;
                margin-top: 10px; /* ربع سانتيم تقريباً */
                margin-bottom: 10px;
                z-index: 1000;
            }
            .floating-toolbar {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                box-shadow: 0 2px 8px #0002;
                padding: 12px 24px;
                min-width: 350px;
                max-width: 98vw;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .floating-toolbar h2, .floating-toolbar h3 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 700;
                color: #374151;
            }
            .floating-toolbar .text-[11px] {
                font-size: 11px;
            }
            .floating-toolbar button, .floating-toolbar a {
                margin-right: 0;
                margin-left: 0;
            }
            @media (max-width: 600px) {
                .floating-toolbar {
                    padding: 8px 6px;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased pb-12">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow relative">
                    <div class="w-full py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="app-main-content">
                {{ $slot }}
            </main>
        </div>

        <!-- شريط الأقوال والأحاديث (صغير وثابت أسفل الصفحة) -->
        <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-emerald-300 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
            <div class="w-full flex items-center gap-2 px-3 py-2 sm:px-6">
                <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700">نور</span>
                <div id="hadithTickerViewport">
                    <p id="hadithTickerText" class="hadith-ticker-fade-in text-gray-700"></p>
                </div>
                <button id="hadithRefreshBtn" title="تحديث النص" aria-label="تحديث النص">🔄</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tickerText = document.getElementById('hadithTickerText');
                const tickerViewport = document.getElementById('hadithTickerViewport');
                if (!tickerText || !tickerViewport) {
                    return;
                }

                const STORAGE_KEY = 'kashf_hadith_ticker_state_v6';
                const ROTATION_INTERVAL_MS = 20000;
                const FADE_DURATION_MS = 320;
                const TARGET_CYCLE_SIZE = 500;

                const baseItems = [
                    'عن أبي بصير، عن الإمام الصادق (ع): «إن ولاية أمير المؤمنين (ع) مفروضة كفريضة الصلاة» — المصدر: الكافي، ج1، ح1',
                    'قال رسول الله ﷺ: «إني تارك فيكم الثقلين: كتاب الله وعترتي أهل بيتي» — المصدر: الكافي، ج1، باب الكتاب والسنة',
                    'عن الإمام عليّ (ع): «أنا علمت الناس من الكتاب وسنة النبي» — المصدر: نهج البلاغة، الخطبة 4',
                    'قال الإمام الباقر (ع): «ما من عبد شيعتنا إلا وله عند الله دعوة مستجابة» — المصدر: البحار، ج24، ص346',
                    'عن الإمام الصادق (ع): «من عرف نفسه فقد عرف ربه» — المصدر: البحار، ج4، ص265',
                    'قال الإمام الصادق (ع): «كم من عابد لله بالليل سارق بالنهار» — المصدر: الكافي، ج3، كتاب النكاح',
                    'عن الإمام علي (ع): «الدنيا سوق ربح فيها من شاء وخسر فيها من شاء» — المصدر: نهج البلاغة، قصار الحكم',
                    'قال الإمام الصادق (ع): «إن الله يحب العبد المؤمن التقي الخفي» — المصدر: الكافي، ج2، ح1',
                    'عن الإمام الكاظم (ع): «مثل الذي يعلم الناس الخير ولا يعمل به كمثل الشمعة تحرق نفسها وتضيء للآخرين» — المصدر: البحار',
                    'قال الإمام علي (ع): «عليك بالقسط وپالعدل في صغير أمورك وكبيرها» — المصدر: نهج البلاغة',
                    'عن الإمام الصادق (ع): «لا يكون العبد مؤمناً حتى يكون خوفه كخوفه من الذل، وحياؤه كحيائه من العري» — المصدر: الكافي، ج2',
                    'قال الإمام الرضا (ع): «العلم خزائن مفاتيحها السؤال» — المصدر: البحار، ج1، ص180',
                    'عن الإمام علي (ع): «المعروف بين المرء وبين أخيه يذهب الشحناء والبغضاء» — المصدر: نهج البلاغة',
                    'قال الإمام الصادق (ع): «من أحب أن يعلم أنه يحب الله فليحب أخاه». — المصدر: الكافي، ج2، كتاب الإيمان والكفر',
                    'عن الإمام الباقر (ع): «ما من عمل أحب إلى الله من النفع الذي يلحق الناس» — المصدر: البحار',
                    'فتوى للسيد السيستاني (دام ظله): يجب تقليد المجتهد الأعلم في العصر — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): الخمس يجب في أرباح المكاسب والاستفادات بعد مؤونة السنة — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): صلاة الجماعة تجب على من استطاع الحضور — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): الحج من أركان الإسلام وواجب على المستطيع — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): لا يجوز أكل لحم الحيوان المذبوح برفع اليد بدون نية التذكية — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): ربا الفضل محرم إلا مع تساوي المقدار والتقابض — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): البيع الفاسد لا ينقل الملك وعلى البائع رد البدل — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): لا يجوز أخذ الفائدة على القروض — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): الكذب في المعاملات يستوجب الإثم الكبير — المصدر: الموقع الرسمي',
                    'قال الإمام علي (ع): «اتقِ الله واتركِ الحرام تستغنِ عن الحلال» — المصدر: نهج البلاغة',
                    'عن الإمام الصادق (ع): «التجار ثلاثة: رجل يبيع دينه بدنياه فقد خسر، ورجل يبيع دنياه بدينه فقد ربح، ورجل يبيع كل منهما بصاحبه فشتّان بينهم» — المصدر: الكافي',
                    'قال الإمام الكاظم (ع): «الصمت جنة للعاقل ونطاق للأحمق» — المصدر: البحار',
                    'عن الإمام علي (ع): «الحزم في الأمور والعزم على ما تختاره ينجي من الندامة» — المصدر: نهج البلاغة',
                    'قال الإمام الصادق (ع): «الحلم زينة والعفو فضيلة والصبر سلاح والشكر نجاة» — المصدر: الكافي',
                    'عن الإمام الباقر (ع): «الورع أن تخاف الله سراً وعلانية» — المصدر: البحار',
                    'فتوى للسيد السيستاني (دام ظله): يجب الإنفاق على الزوجة والأولاد من مال الزوج — المصدر: منهاج الصالحين',
                    'فتوى للسيد السيستاني (دام ظله): حق المرأة في المهر حق واجب غير قابل للتنازل — المصدر: منهاج الصالحين',
                    'قال رسول الله ﷺ: «خيركم خيركم لأهله وأنا خيركم لأهلي» — المصدر: الكافي، كتاب النكاح',
                    'عن الإمام علي (ع): «الأقارب سهام الله فمن قطعهم ضربهم الله بضربات القيامة» — المصدر: نهج البلاغة',
                    'فتوى للسيد السيستاني (دام ظله): الصلة والعطف على الأقارب من الأعمال المستحبة جداً — المصدر: منهاج الصالحين',
                    'قال الإمام الصادق (ع): «من لم يكن له الصبر، لم تكن له القوة» — المصدر: الكافي',
                    'عن الإمام الكاظم (ع): «الغضب مفتاح كل شر والحلم مفتاح كل خير» — المصدر: البحار',
                    'فتوى للسيد السيستاني (دام ظله): اجتناب الغضب والعصبية من الأمور المهمة في الأخلاق — المصدر: الموقع الرسمي',
                    'قال الإمام علي (ع): «من استبدل بالعلم الجهل فقد بدَّل نعماً كفراً» — المصدر: نهج البلاغة',
                    'عن الإمام الصادق (ع): «العلماء يوم القيامة يدعون الأنبياء» — المصدر: البحار',
                    'فتوى للسيد السيستاني (دام ظله): طلب العلم والتعلم من أهم المستحبات — المصدر: منهاج الصالحين',
                    'قال الإمام الباقر (ع): «الذاكر الله في الغفلة كالمجاهد المقاتل» — المصدر: الكافي',
                    'عن الإمام علي (ع): «ذكر الله يطرد الداء ويملأ القلب نوراً» — المصدر: نهج البلاغة',
                    'فتوى للسيد السيستاني (دام ظله): الذكر والدعاء والتضرع إلى الله تعالى من أعظم القربات — المصدر: الموقع الرسمي',
                    'قال الإمام الصادق (ع): «من عامل الناس بالعدل ازدادوا حوله» — المصدر: الكافي',
                    'عن الإمام الرضا (ع): «العدل ساس القرى والرحمة إمام الملك» — المصدر: البحار',
                    'فتوى للسيد السيستاني (دام ظله): العدل أساس الحكم والعمل والحياة الصالحة — المصدر: منهاج الصالحين',
                    'قال الإمام علي (ع): «ما من شيء أفضل من الإرادة والرأي والعلم» — المصدر: نهج البلاغة',
                    '﴿إِنَّمَا يُرِيدُ اللَّهُ لِيُذْهِبَ عَنكُمُ الرِّجْسَ أَهْلَ الْبَيْتِ وَيُطَهِّرَكُمْ تَطْهِيرًا﴾ — سورة الأحزاب: 33',
                    '﴿قُل لَّا أَسْأَلُكُمْ عَلَيْهِ أَجْرًا إِلَّا الْمَوَدَّةَ فِي الْقُرْبَى﴾ — سورة الشورى: 23',
                    '﴿كُنتُمْ خَيْرَ أُمَّةٍ أُخْرِجَتْ لِلنَّاسِ﴾ — سورة آل عمران: 110',
                    '﴿وَالْعَصْرِ * إِنَّ الْإِنسَانَ لَفِي خُسْرٍ﴾ — سورة العصر: 1-2',
                    '﴿الر كِتَابٌ أَنزَلْنَاهُ إِلَيْكَ لِتُخْرِجَ النَّاسَ مِنَ الظُّلُمَاتِ إِلَى النُّورِ﴾ — سورة إبراهيم: 1',
                    '﴿يَا أَيُّهَا الَّذِينَ آمَنُوا اتَّقُوا اللَّهَ حَقَّ تُقَاتِهِ﴾ — سورة آل عمران: 102',
                    '﴿قُلْ هُوَ اللَّهُ أَحَدٌ * اللَّهُ الصَّمَدُ﴾ — سورة الإخلاص: 1-2',
                    '﴿لَا إِكْرَاهَ فِي الدِّينِ قَد تَّبَيَّنَ الرُّشْدُ مِنَ الْغَيِّ﴾ — سورة البقرة: 256',
                    '﴿إِنَّ اللَّهَ مَعَ الصَّابِرِينَ﴾ — سورة البقرة: 153',
                    '﴿وَمَن يَتَّقِ اللَّهَ يَجْعَل لَّهُ مَخْرَجًا * وَيَرْزُقْهُ مِنْ حَيْثُ لَا يَحْتَسِبُ﴾ — سورة الطلاق: 2-3',
                    '﴿الْقَارِعَةُ * مَا الْقَارِعَةُ * وَمَا أَدْرَاكَ مَا الْقَارِعَةُ﴾ — سورة القارعة: 1-3',
                    '﴿بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ * الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ﴾ — سورة الفاتحة: 1-2',
                    '﴿وَإِن تَشْكُرُوا يَرْضَهُ لَكُمْ﴾ — سورة الزمر: 7',
                    '﴿فَإِذَا قَضَيْتَ الصَّلَاةَ فَانتَشِرُوا فِي الْأَرْضِ﴾ — سورة الجمعة: 10',
                    '﴿يَا أَيُّهَا النَّاسُ إِنَّا خَلَقْنَاكُم مِّن ذَكَرٍ وَأُنثَىٰ﴾ — سورة الحجرات: 13',
                    '﴿إِنَّ أَكْرَمَكُمْ عِندَ اللَّهِ أَتْقَاكُمْ﴾ — سورة الحجرات: 13',
                    '﴿وَبِالْوَالِدَيْنِ إِحْسَانًا﴾ — سورة الإسراء: 23',
                    '﴿فَبِأَيِّ آلَاءِ رَبِّكُمَا تُكَذِّبَانِ﴾ — سورة الرحمن: 13',
                    '﴿اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ﴾ — سورة العلق: 1'
                ];

                const updateTickerScrollMode = function () {
                    tickerText.classList.remove('hadith-ticker-marquee');
                    tickerText.style.removeProperty('--ticker-scroll-distance');
                    tickerText.style.removeProperty('--ticker-scroll-duration');

                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        return;
                    }

                    const overflowWidth = tickerText.scrollWidth - tickerViewport.clientWidth;
                    if (overflowWidth <= 10) {
                        return;
                    }

                    const durationSec = Math.max(12, Math.min(45, overflowWidth / 35));
                    tickerText.style.setProperty('--ticker-scroll-distance', overflowWidth + 'px');
                    tickerText.style.setProperty('--ticker-scroll-duration', durationSec + 's');
                    tickerText.classList.add('hadith-ticker-marquee');
                };

                const buildRandomOrder = function (length) {
                    const order = Array.from({ length: length }, function (_, idx) {
                        return idx;
                    });

                    for (let i = order.length - 1; i > 0; i -= 1) {
                        const j = Math.floor(Math.random() * (i + 1));
                        const temp = order[i];
                        order[i] = order[j];
                        order[j] = temp;
                    }

                    return order;
                };

                const normalizeCycleSize = function (baseLength) {
                    if (baseLength <= 1) {
                        return 1;
                    }

                    return Math.max(baseLength, TARGET_CYCLE_SIZE);
                };

                const ensureNotStartingWith = function (arr, forbiddenValue) {
                    if (!Number.isInteger(forbiddenValue) || arr.length <= 1 || arr[0] !== forbiddenValue) {
                        return arr;
                    }

                    const swapIndex = arr.findIndex(function (item) {
                        return item !== forbiddenValue;
                    });

                    if (swapIndex > 0) {
                        const temp = arr[0];
                        arr[0] = arr[swapIndex];
                        arr[swapIndex] = temp;
                    }

                    return arr;
                };

                const buildExpandedRandomCycle = function (length, targetSize, avoidFirstIndex) {
                    if (length <= 1) {
                        return [0];
                    }

                    const cycle = [];
                    let previousItem = Number.isInteger(avoidFirstIndex) ? avoidFirstIndex : null;

                    while (cycle.length < targetSize) {
                        const chunk = buildRandomOrder(length);
                        ensureNotStartingWith(chunk, previousItem);

                        for (let i = 0; i < chunk.length && cycle.length < targetSize; i += 1) {
                            const nextItem = chunk[i];
                            if (previousItem !== null && nextItem === previousItem) {
                                continue;
                            }

                            cycle.push(nextItem);
                            previousItem = nextItem;
                        }
                    }

                    return cycle;
                };

                const isValidOrder = function (order, length) {
                    if (!Array.isArray(order) || order.length < 1) {
                        return false;
                    }

                    return order.every(function (item) {
                        return Number.isInteger(item) && item >= 0 && item < length;
                    });
                };

                const buildQuranItemsFromPayload = function (payload) {
                    if (!payload || payload.status !== 'OK' || !payload.data || !payload.data.ayahs) {
                        return [];
                    }
                    const surahName = payload.data.name || '';
                    return payload.data.ayahs
                        .filter(function (a) { return a.text && a.text.trim().length > 10; })
                        .slice(0, 12)
                        .map(function (a) {
                            return '﴿' + a.text.trim() + '﴾ \u2014 ' + surahName + ': \u0622\u064a\u0629 ' + a.numberInSurah;
                        });
                };

                const fetchRemoteContent = async function () {
                    // تم تعطيل جلب بيانات من alquran.cloud لعدم توفر الإنترنت في بعض الحاسبات
                    return [];
                };

                let tickerIntervalId = null;

                const startTicker = function (items) {
                    if (!Array.isArray(items) || items.length === 0) {
                        tickerText.textContent = 'تعذر تحميل النصوص الموثقة حالياً.';
                        return;
                    }

                    const loadState = function () {
                        try {
                            const raw = localStorage.getItem(STORAGE_KEY);
                            if (!raw) {
                                return null;
                            }

                            const parsed = JSON.parse(raw);
                            if (!parsed || !isValidOrder(parsed.order, items.length)) {
                                return null;
                            }

                            const maxPosition = parsed.order.length - 1;
                            const safePosition = Number.isInteger(parsed.position)
                                ? Math.min(Math.max(parsed.position, 0), maxPosition)
                                : 0;

                            return {
                                order: parsed.order,
                                position: safePosition
                            };
                        } catch (error) {
                            return null;
                        }
                    };

                    const saveState = function (state) {
                        try {
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                        } catch (error) {
                            // تجاهل أخطاء التخزين في المتصفح
                        }
                    };

                    const makeInitialState = function () {
                        const cycleSize = normalizeCycleSize(items.length);

                        return {
                            order: buildExpandedRandomCycle(items.length, cycleSize),
                            position: 0
                        };
                    };

                    let tickerState = loadState() || makeInitialState();

                    const getCurrentItemIndex = function () {
                        return tickerState.order[tickerState.position];
                    };

                    const showCurrentItem = function () {
                        tickerText.textContent = items[getCurrentItemIndex()];
                        tickerText.classList.remove('hadith-ticker-marquee');
                        tickerText.style.removeProperty('--ticker-scroll-distance');
                        tickerText.style.removeProperty('--ticker-scroll-duration');

                        requestAnimationFrame(function () {
                            updateTickerScrollMode();
                        });
                    };

                    const moveToNextRandomItem = function () {
                        if (tickerState.position >= tickerState.order.length - 1) {
                            const lastShownIndex = getCurrentItemIndex();
                            const cycleSize = normalizeCycleSize(items.length);

                            tickerState = {
                                order: buildExpandedRandomCycle(items.length, cycleSize, lastShownIndex),
                                position: 0
                            };
                            return;
                        }

                        tickerState.position += 1;
                    };

                    const rotateItem = function () {
                        tickerText.classList.remove('hadith-ticker-fade-in');
                        tickerText.classList.add('hadith-ticker-fade-out');

                        setTimeout(function () {
                            moveToNextRandomItem();
                            saveState(tickerState);
                            showCurrentItem();
                            tickerText.classList.remove('hadith-ticker-fade-out');
                            tickerText.classList.add('hadith-ticker-fade-in');
                        }, FADE_DURATION_MS);
                    };

                    showCurrentItem();
                    saveState(tickerState);
                    if (tickerIntervalId) { clearInterval(tickerIntervalId); }
                    tickerIntervalId = setInterval(rotateItem, ROTATION_INTERVAL_MS);

                    let resizeDebounceTimer;
                    window.addEventListener('resize', function () {
                        clearTimeout(resizeDebounceTimer);
                        resizeDebounceTimer = setTimeout(function () {
                            updateTickerScrollMode();
                        }, 140);
                    });

                }; // نهاية startTicker

                // زر تحديث البيانات من المصادر الخارجية
                const refreshBtn = document.getElementById('hadithRefreshBtn');
                if (refreshBtn) {
                    refreshBtn.addEventListener('click', function () {
                        if (refreshBtn.dataset.loading === '1') { return; }
                        refreshBtn.dataset.loading = '1';
                        refreshBtn.classList.add('refreshing');

                        tickerText.classList.remove('hadith-ticker-fade-in');
                        tickerText.classList.add('hadith-ticker-fade-out');

                        fetchRemoteContent().then(function (remoteItems) {
                            const newItems = remoteItems.length > 0
                                ? baseItems.concat(remoteItems)
                                : baseItems;
                            localStorage.removeItem(STORAGE_KEY);
                            setTimeout(function () {
                                startTicker(newItems);
                                tickerText.classList.remove('hadith-ticker-fade-out');
                                tickerText.classList.add('hadith-ticker-fade-in');
                                refreshBtn.classList.remove('refreshing');
                                refreshBtn.dataset.loading = '0';
                            }, FADE_DURATION_MS);
                        });
                    });
                }

                tickerText.textContent = 'جاري تحميل النصوص...';

                fetchRemoteContent().then(function (remoteItems) {
                    const allItems = remoteItems.length > 0
                        ? baseItems.concat(remoteItems)
                        : baseItems;
                    startTicker(allItems);
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // تحسين الـ placeholder - إظهار صيغة التاريخ بشكل واضح
                const enhanceDateInputs = function () {
                    const dateSelectors = [
                        '.js-order-date',
                        '.js-start-date',
                        '.js-end-date',
                        'input[type="text"][placeholder*="yyyy"]'
                    ];

                    dateSelectors.forEach(function (selector) {
                        document.querySelectorAll(selector).forEach(function (input) {
                            // التأكد من وجود placeholder
                            if (!input.placeholder || !input.placeholder.includes('yyyy')) {
                                input.placeholder = 'yyyy/mm/dd';
                            }
                            // إضافة title عند التمرير فوق الحقل
                            if (!input.title || !input.title.includes('yyyy')) {
                                input.title = 'صيغة التاريخ: yyyy/mm/dd';
                            }
                            // التأكد من أن الحقل فارغ يعرضها placeholder بشكل صحيح
                            if (!input.value) {
                                input.style.color = '#2563eb';
                            }

                            // عند التركيز والكتابة
                            input.addEventListener('input', function () {
                                if (input.value) {
                                    input.style.color = '#1F2937';
                                } else {
                                    input.style.color = '#2563eb';
                                }
                            });

                            // عند فقدان التركيز
                            input.addEventListener('blur', function () {
                                if (!input.value) {
                                    input.style.color = '#2563eb';
                                }
                            });
                        });
                    });
                };

                // Flatpickr date picker - يعمل على جميع حقول التاريخ في كل الصفحات
                const dateSelectors = [
                    'input[type="text"][name*="date"]',  // name يحتوي "date"
                    'input[type="text"][data-date-input]', // data attribute
                    'input[type="text"][placeholder*="yyyy"]', // أي حقل فيه placeholder تاريخ
                    '#masterOrderDate',    // create.blade.php header
                    '#masterStartDate',    // create.blade.php header
                    '#masterEndDate',      // create.blade.php header
                    '.js-order-date',      // create.blade.php table rows
                    '.js-start-date',      // create.blade.php table rows
                    '.js-end-date',        // create.blade.php table rows
                    '#start_date',         // edit.blade.php
                    '#end_date',           // edit.blade.php
                    '#filterFromDate',     // dashboard.blade.php
                    '#filterToDate'        // dashboard.blade.php
                ];

                function activateFlatpickrAll() {
                    dateSelectors.forEach(function (selector) {
                        document.querySelectorAll(selector).forEach(function (input) {
                            if (input._flatpickr) return;
                            window.flatpickr(input, {
                                dateFormat: 'Y/m/d',
                                altFormat: 'Y/m/d',
                                altInput: false,
                                locale: 'ar',
                                allowInput: true,
                                disableMobile: true
                            });
                        });
                    });
                }

                function waitForFlatpickrAndActivate(retry) {
                    if (window.flatpickr && typeof window.flatpickr === 'function') {
                        activateFlatpickrAll();
                    } else if (retry > 0) {
                        setTimeout(function() { waitForFlatpickrAndActivate(retry-1); }, 200);
                    } else {
                        console.warn('flatpickr غير محمل!');
                    }
                }

                // Initialize on page load
                enhanceDateInputs();
                waitForFlatpickrAndActivate(20); // يحاول لمدة 4 ثواني كحد أقصى

                // Re-initialize when new rows are added to the payroll table
                if (typeof MutationObserver !== 'undefined') {
                    const tableBody = document.querySelector('#payrollTable tbody');
                    if (tableBody) {
                        const observer = new MutationObserver(function () {
                            enhanceDateInputs();
                            waitForFlatpickrAndActivate(10);
                        });
                        observer.observe(tableBody, { childList: true, subtree: true });
                    }
                }
            });
        </script>
    </body>
</html>
