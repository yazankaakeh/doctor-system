<?php

namespace Modules\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CMS\Enums\PageStatusEnum;
use Modules\CMS\Enums\PageTemplateEnum;
use Modules\CMS\Models\Page;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        $landingPage = Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => [
                    'en' => 'Home',
                    'ar' => 'الرئيسية',
                    'tr' => 'Ana Sayfa',
                ],
                'slug' => 'home',
                'excerpt' => [
                    'en' => 'Welcome to our website',
                    'ar' => 'مرحبا بكم في موقعنا',
                    'tr' => 'Web sitemize hoş geldiniz',
                ],
                'content' => [
                    'en' => 'Landing page content',
                    'ar' => 'محتوى الصفحة الرئيسية',
                    'tr' => 'Açılış sayfası içeriği',
                ],
                'status' => PageStatusEnum::PUBLISHED,
                'template' => PageTemplateEnum::LANDING,
                'order' => 0,
                'published_at' => now(),
                'meta_data' => [
                    'hero' => [
                        'title' => [
                            'en' => 'All in one SaaS application for your business',
                            'ar' => 'تطبيق SaaS الكل في واحد لعملك',
                            'tr' => 'İşletmeniz için hepsi bir arada SaaS uygulaması',
                        ],
                        'subtitle' => [
                            'en' => 'No coding required to make customizations',
                            'ar' => 'لا حاجة للبرمجة لإجراء التخصيصات',
                            'tr' => 'Özelleştirmeler için kodlama gerekmez',
                        ],
                        'description' => [
                            'en' => 'The time is now for it to be okay to be great. People in this world shun people for being great.',
                            'ar' => 'الوقت الآن مناسب لأن يكون رائعًا. الناس في هذا العالم يتجنبون الناس لكونهم عظماء.',
                            'tr' => 'Harika olmak için şimdi tam zamanı. Bu dünyadaki insanlar harika olmak için insanları kaçırıyor.',
                        ],
                        'cta_text' => [
                            'en' => 'Get Started',
                            'ar' => 'ابدأ الآن',
                            'tr' => 'Başlayın',
                        ],
                        'cta_url' => '/register',
                        'image' => 'assets/img/front-pages/landing-page/hero-dashboard.png',
                    ],
                    'features' => [
                        'badge' => [
                            'en' => 'Useful Features',
                            'ar' => 'ميزات مفيدة',
                            'tr' => 'Yararlı Özellikler',
                        ],
                        'title' => [
                            'en' => 'Everything you need to start your next project',
                            'ar' => 'كل ما تحتاجه لبدء مشروعك القادم',
                            'tr' => 'Bir sonraki projenizi başlatmak için ihtiyacınız olan her şey',
                        ],
                        'description' => [
                            'en' => 'Not just a set of tools, the package includes ready-to-deploy conceptual application.',
                            'ar' => 'ليست مجرد مجموعة من الأدوات، تتضمن الحزمة تطبيقًا مفاهيميًا جاهزًا للنشر.',
                            'tr' => 'Sadece bir araç seti değil, paket dağıtıma hazır kavramsal uygulama içerir.',
                        ],
                        'items' => [
                            [
                                'icon' => 'ti tabler-crown',
                                'title' => [
                                    'en' => 'Quality Code',
                                    'ar' => 'كود عالي الجودة',
                                    'tr' => 'Kaliteli Kod',
                                ],
                                'description' => [
                                    'en' => 'Code structure that all developers will easily understand and fall in love with.',
                                    'ar' => 'بنية كود سيفهمها جميع المطورين بسهولة ويعجبون بها.',
                                    'tr' => 'Tüm geliştiricilerin kolayca anlayacağı ve bayılacağı kod yapısı.',
                                ],
                            ],
                            [
                                'icon' => 'ti tabler-users',
                                'title' => [
                                    'en' => 'Continuous Updates',
                                    'ar' => 'تحديثات مستمرة',
                                    'tr' => 'Sürekli Güncellemeler',
                                ],
                                'description' => [
                                    'en' => 'Free updates for the next 12 months, including new demos and features.',
                                    'ar' => 'تحديثات مجانية للأشهر الـ 12 القادمة، بما في ذلك عروض توضيحية وميزات جديدة.',
                                    'tr' => 'Yeni demolar ve özellikler dahil olmak üzere önümüzdeki 12 ay için ücretsiz güncellemeler.',
                                ],
                            ],
                            [
                                'icon' => 'ti tabler-atom',
                                'title' => [
                                    'en' => 'Stater-Kit',
                                    'ar' => 'مجموعة البداية',
                                    'tr' => 'Başlangıç Kiti',
                                ],
                                'description' => [
                                    'en' => 'Start your project quickly without having to remove unnecessary features.',
                                    'ar' => 'ابدأ مشروعك بسرعة دون الحاجة لإزالة الميزات غير الضرورية.',
                                    'tr' => 'Gereksiz özellikleri kaldırmak zorunda kalmadan projenize hızlı bir şekilde başlayın.',
                                ],
                            ],
                            [
                                'icon' => 'ti tabler-api',
                                'title' => [
                                    'en' => 'API Ready',
                                    'ar' => 'جاهز لواجهة برمجة التطبيقات',
                                    'tr' => 'API Hazır',
                                ],
                                'description' => [
                                    'en' => 'Just change the endpoint and see your own data loaded within seconds.',
                                    'ar' => 'فقط قم بتغيير نقطة النهاية وشاهد بياناتك الخاصة محملة في ثوانٍ.',
                                    'tr' => 'Sadece uç noktayı değiştirin ve kendi verilerinizin saniyeler içinde yüklendiğini görün.',
                                ],
                            ],
                            [
                                'icon' => 'ti tabler-headset',
                                'title' => [
                                    'en' => 'Excellent Support',
                                    'ar' => 'دعم ممتاز',
                                    'tr' => 'Mükemmel Destek',
                                ],
                                'description' => [
                                    'en' => 'An easy-to-follow doc with lots of references and code examples.',
                                    'ar' => 'وثائق سهلة المتابعة مع الكثير من المراجع وأمثلة التعليمات البرمجية.',
                                    'tr' => 'Birçok referans ve kod örneği içeren takip edilmesi kolay bir belge.',
                                ],
                            ],
                            [
                                'icon' => 'ti tabler-file-description',
                                'title' => [
                                    'en' => 'Well Documented',
                                    'ar' => 'موثق بشكل جيد',
                                    'tr' => 'İyi Belgelenmiş',
                                ],
                                'description' => [
                                    'en' => 'An easy-to-follow doc with lots of references and code examples.',
                                    'ar' => 'وثائق سهلة المتابعة مع الكثير من المراجع وأمثلة التعليمات البرمجية.',
                                    'tr' => 'Birçok referans ve kod örneği içeren takip edilmesi kolay bir belge.',
                                ],
                            ],
                        ],
                    ],
                    'team' => [
                        'badge' => [
                            'en' => 'Our Great Team',
                            'ar' => 'فريقنا الرائع',
                            'tr' => 'Harika Ekibimiz',
                        ],
                        'title' => [
                            'en' => 'Supported by real people',
                            'ar' => 'بدعم من أشخاص حقيقيين',
                            'tr' => 'Gerçek insanlar tarafından desteklenir',
                        ],
                        'description' => [
                            'en' => 'Who is behind these great-looking interfaces?',
                            'ar' => 'من وراء هذه الواجهات الرائعة المظهر؟',
                            'tr' => 'Bu harika görünen arayüzlerin arkasında kim var?',
                        ],
                        'members' => [
                            [
                                'name' => 'Sophie Gilbert',
                                'position' => [
                                    'en' => 'Project Manager',
                                    'ar' => 'مدير المشروع',
                                    'tr' => 'Proje Yöneticisi',
                                ],
                                'avatar' => 'assets/img/avatars/1.png',
                            ],
                            [
                                'name' => 'Paul Miles',
                                'position' => [
                                    'en' => 'UI Designer',
                                    'ar' => 'مصمم واجهة المستخدم',
                                    'tr' => 'UI Tasarımcı',
                                ],
                                'avatar' => 'assets/img/avatars/2.png',
                            ],
                            [
                                'name' => 'Nannie Ford',
                                'position' => [
                                    'en' => 'Development Lead',
                                    'ar' => 'قائد التطوير',
                                    'tr' => 'Geliştirme Lideri',
                                ],
                                'avatar' => 'assets/img/avatars/3.png',
                            ],
                            [
                                'name' => 'Chris Watkins',
                                'position' => [
                                    'en' => 'Marketing Manager',
                                    'ar' => 'مدير التسويق',
                                    'tr' => 'Pazarlama Müdürü',
                                ],
                                'avatar' => 'assets/img/avatars/4.png',
                            ],
                        ],
                    ],
                    'contact' => [
                        'badge' => [
                            'en' => 'Contact US',
                            'ar' => 'اتصل بنا',
                            'tr' => 'Bize Ulaşın',
                        ],
                        'title' => [
                            'en' => "Let's work together",
                            'ar' => 'لنعمل معًا',
                            'tr' => 'Birlikte çalışalım',
                        ],
                        'description' => [
                            'en' => 'Any question or remark? just write us a message',
                            'ar' => 'أي سؤال أو ملاحظة؟ فقط اكتب لنا رسالة',
                            'tr' => 'Herhangi bir soru veya not? sadece bize bir mesaj yazın',
                        ],
                        'email' => 'example@gmail.com',
                        'phone' => '+1234-568-963',
                    ],
                    'cta' => [
                        'title' => [
                            'en' => 'Ready to Get Started?',
                            'ar' => 'هل أنت مستعد للبدء؟',
                            'tr' => 'Başlamaya Hazır mısınız?',
                        ],
                        'subtitle' => [
                            'en' => 'Start your project with a 14-day free trial',
                            'ar' => 'ابدأ مشروعك بتجربة مجانية لمدة 14 يومًا',
                            'tr' => '14 günlük ücretsiz deneme ile projenize başlayın',
                        ],
                        'button_text' => [
                            'en' => 'Get Started',
                            'ar' => 'ابدأ الآن',
                            'tr' => 'Başlayın',
                        ],
                        'button_url' => '/register',
                    ],
                    'faq' => [
                        'badge' => [
                            'en' => 'FAQ',
                            'ar' => 'الأسئلة الشائعة',
                            'tr' => 'SSS',
                        ],
                        'title' => [
                            'en' => 'Frequently asked questions',
                            'ar' => 'الأسئلة المتكررة',
                            'tr' => 'Sıkça sorulan sorular',
                        ],
                        'description' => [
                            'en' => 'Browse through these FAQs to find answers to commonly asked questions.',
                            'ar' => 'تصفح هذه الأسئلة الشائعة للعثور على إجابات للأسئلة الشائعة.',
                            'tr' => 'Sık sorulan sorulara cevaplar bulmak için bu SSS\'lere göz atın.',
                        ],
                        'items' => [
                            [
                                'question' => [
                                    'en' => 'Do you charge for each upgrade?',
                                    'ar' => 'هل تفرض رسومًا على كل ترقية؟',
                                    'tr' => 'Her yükseltme için ücret alıyor musunuz?',
                                ],
                                'answer' => [
                                    'en' => 'Lemon drops chocolate cake gummies carrot cake chupa chups muffin topping. Sesame snaps icing marzipan gummi bears macaroon dragée danish caramels powder.',
                                    'ar' => 'قطرات الليمون كعكة الشوكولاتة الحلوى كعكة الجزر تشوبا تشوبس الكعك الطبقة. السمسم سناب الثلج المرزبانية الدببة الصمغية ماكارون دراغي الدنماركي الكراميل مسحوق.',
                                    'tr' => 'Limon damlaları çikolatalı kek şekerleme havuç kek chupa chups kek üstü. Susam atıştırmalıkları buzlanma badem ezmesi sakızlı ayılar makaron drajelik danimarka karamel tozu.',
                                ],
                            ],
                            [
                                'question' => [
                                    'en' => 'Do I need to purchase a license for each website?',
                                    'ar' => 'هل أحتاج إلى شراء ترخيص لكل موقع ويب؟',
                                    'tr' => 'Her web sitesi için bir lisans satın almam gerekiyor mu?',
                                ],
                                'answer' => [
                                    'en' => 'Dessert ice cream donut oat cake jelly-o pie sugar plum cheesecake. Bear claw dragée oat cake dragée ice cream halvah tootsie roll.',
                                    'ar' => 'حلوى الآيس كريم دونات كعكة الشوفان هلام فطيرة البرقوق السكر كعكة الجبن. مخلب الدب دراغيه كعكة الشوفان دراغي الآيس كريم حلاوة طحينية توتسي رول.',
                                    'tr' => 'Tatlı dondurma donut yulaf kek jöle-o turta şeker erikli cheesecake. Ayı pençesi draje yulaf kek draje dondurma helva tootsie roll.',
                                ],
                            ],
                            [
                                'question' => [
                                    'en' => 'What is regular license?',
                                    'ar' => 'ما هي الرخصة العادية؟',
                                    'tr' => 'Normal lisans nedir?',
                                ],
                                'answer' => [
                                    'en' => 'Regular license can be used for end products that do not charge users for access or service. Single regular license can be used for single end product and end product can be used by you or your client.',
                                    'ar' => 'يمكن استخدام الترخيص العادي للمنتجات النهائية التي لا تفرض رسومًا على المستخدمين للوصول أو الخدمة. يمكن استخدام ترخيص عادي واحد لمنتج نهائي واحد ويمكن استخدام المنتج النهائي من قبلك أو عميلك.',
                                    'tr' => 'Normal lisans, kullanıcılardan erişim veya hizmet için ücret almayan son ürünler için kullanılabilir. Tek normal lisans tek son ürün için kullanılabilir ve son ürün sizin veya müşteriniz tarafından kullanılabilir.',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        // Set SEO data
        $landingPage->seo()->updateOrCreate(
            ['seoable_id' => $landingPage->id, 'seoable_type' => get_class($landingPage)],
            [
                'title' => [
                    'en' => 'Home - Welcome to Our Platform',
                    'ar' => 'الرئيسية - مرحبًا بك في منصتنا',
                    'tr' => 'Ana Sayfa - Platformumuza Hoş Geldiniz',
                ],
                'meta_description' => [
                    'en' => 'All in one SaaS application for your business. No coding required.',
                    'ar' => 'تطبيق SaaS الكل في واحد لعملك. لا حاجة للبرمجة.',
                    'tr' => 'İşletmeniz için hepsi bir arada SaaS uygulaması. Kodlama gerekmez.',
                ],
                'robots_index' => true,
                'robots_follow' => true,
            ]
        );
    }
}
