<?php

namespace Modules\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CMS\Enums\PageStatusEnum;
use Modules\CMS\Enums\PageTemplateEnum;
use Modules\CMS\Enums\PanelItemTypeEnum;
use Modules\CMS\Enums\PanelTypeEnum;
use Modules\CMS\Models\Page;
use Modules\CMS\Models\Panel;
use Modules\CMS\Models\PanelItem;

class MedicalConsultationSeeder extends Seeder
{
    public function run(): void
    {
        // Create/Update the home page for medical consultation
        $homePage = Page::query()->updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => [
                    'en' => 'Home',
                    'ar' => 'الرئيسية',
                    'tr' => 'Ana Sayfa',
                ],
                'slug' => 'home',
                'excerpt' => [
                    'en' => 'Your trusted partner for medical consultations and healthcare services',
                    'ar' => 'شريكك الموثوق للاستشارات الطبية وخدمات الرعاية الصحية',
                    'tr' => 'Tıbbi danışmanlık ve sağlık hizmetleri için güvenilir ortağınız',
                ],
                'content' => [
                    'en' => 'Professional medical consultation services',
                    'ar' => 'خدمات استشارات طبية احترافية',
                    'tr' => 'Profesyonel tıbbi danışmanlık hizmetleri',
                ],
                'status' => PageStatusEnum::PUBLISHED,
                'template' => PageTemplateEnum::LANDING,
                'use_panel_builder' => true,
                'order' => 0,
                'published_at' => now(),
                'meta_data' => [
                    'hero' => [
                        'title' => [
                            'en' => 'Your Health, Our Priority',
                            'ar' => 'صحتك، أولويتنا',
                            'tr' => 'Sağlığınız, Önceliğimiz',
                        ],
                        'subtitle' => [
                            'en' => 'Expert Medical Consultation',
                            'ar' => 'استشارات طبية متخصصة',
                            'tr' => 'Uzman Tıbbi Danışmanlık',
                        ],
                        'description' => [
                            'en' => 'Connect with experienced doctors and specialists for personalized medical consultations. Get quality healthcare from the comfort of your home.',
                            'ar' => 'تواصل مع أطباء ومتخصصين ذوي خبرة للحصول على استشارات طبية مخصصة. احصل على رعاية صحية عالية الجودة من راحة منزلك.',
                            'tr' => 'Kişiselleştirilmiş tıbbi danışmanlık için deneyimli doktorlar ve uzmanlarla bağlantı kurun. Evinizin rahatlığında kaliteli sağlık hizmeti alın.',
                        ],
                        'cta_text' => [
                            'en' => 'Book Appointment',
                            'ar' => 'احجز موعد',
                            'tr' => 'Randevu Al',
                        ],
                        'cta_url' => '/patient/register',
                        'image' => 'assets/img/front-pages/landing-page/hero-dashboard.png',
                    ],
                ],
            ]
        );

        // Set SEO data
        $homePage->seo()->updateOrCreate(
            ['seoable_id' => $homePage->id, 'seoable_type' => get_class($homePage)],
            [
                'title' => [
                    'en' => 'Medical Consultation - Expert Healthcare Services',
                    'ar' => 'استشارات طبية - خدمات رعاية صحية متخصصة',
                    'tr' => 'Tıbbi Danışmanlık - Uzman Sağlık Hizmetleri',
                ],
                'meta_description' => [
                    'en' => 'Connect with experienced doctors for professional medical consultations. Book appointments online and get quality healthcare services.',
                    'ar' => 'تواصل مع أطباء ذوي خبرة للحصول على استشارات طبية احترافية. احجز المواعيد عبر الإنترنت واحصل على خدمات رعاية صحية عالية الجودة.',
                    'tr' => 'Profesyonel tıbbi danışmanlık için deneyimli doktorlarla iletişime geçin. Online randevu alın ve kaliteli sağlık hizmetleri alın.',
                ],
                'robots_index' => true,
                'robots_follow' => true,
            ]
        );

        // Delete existing panels for this page to avoid duplicates
        Panel::where('page_id', $homePage->id)->delete();

        // Create Features Panel (Medical Services)
        $featuresPanel = Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::FEATURES,
            'title' => [
                'en' => 'Our Medical Services',
                'ar' => 'خدماتنا الطبية',
                'tr' => 'Tıbbi Hizmetlerimiz',
            ],
            'order' => 1,
            'is_active' => true,
            'settings' => [
                'badge' => [
                    'en' => 'Services',
                    'ar' => 'الخدمات',
                    'tr' => 'Hizmetler',
                ],
                'description' => [
                    'en' => 'Comprehensive healthcare services tailored to your needs',
                    'ar' => 'خدمات رعاية صحية شاملة مصممة لتلبية احتياجاتك',
                    'tr' => 'İhtiyaçlarınıza göre uyarlanmış kapsamlı sağlık hizmetleri',
                ],
            ],
        ]);

        $services = [
            [
                'icon' => 'ti tabler-stethoscope',
                'title' => ['en' => 'General Medicine', 'ar' => 'الطب العام', 'tr' => 'Genel Tıp'],
                'description' => ['en' => 'Comprehensive primary care and preventive health services for all ages.', 'ar' => 'رعاية أولية شاملة وخدمات صحية وقائية لجميع الأعمار.', 'tr' => 'Her yaş için kapsamlı birinci basamak sağlık ve koruyucu sağlık hizmetleri.'],
            ],
            [
                'icon' => 'ti tabler-heart-rate-monitor',
                'title' => ['en' => 'Cardiology', 'ar' => 'أمراض القلب', 'tr' => 'Kardiyoloji'],
                'description' => ['en' => 'Expert heart care with advanced diagnostic and treatment options.', 'ar' => 'رعاية قلبية متخصصة مع خيارات تشخيص وعلاج متقدمة.', 'tr' => 'Gelişmiş teşhis ve tedavi seçenekleriyle uzman kalp bakımı.'],
            ],
            [
                'icon' => 'ti tabler-brain',
                'title' => ['en' => 'Neurology', 'ar' => 'طب الأعصاب', 'tr' => 'Nöroloji'],
                'description' => ['en' => 'Specialized care for brain, spine, and nervous system disorders.', 'ar' => 'رعاية متخصصة لاضطرابات الدماغ والعمود الفقري والجهاز العصبي.', 'tr' => 'Beyin, omurga ve sinir sistemi bozuklukları için özel bakım.'],
            ],
            [
                'icon' => 'ti tabler-bone',
                'title' => ['en' => 'Orthopedics', 'ar' => 'جراحة العظام', 'tr' => 'Ortopedi'],
                'description' => ['en' => 'Treatment for bones, joints, muscles, and sports injuries.', 'ar' => 'علاج العظام والمفاصل والعضلات والإصابات الرياضية.', 'tr' => 'Kemik, eklem, kas ve spor yaralanmaları tedavisi.'],
            ],
            [
                'icon' => 'ti tabler-eye',
                'title' => ['en' => 'Ophthalmology', 'ar' => 'طب العيون', 'tr' => 'Göz Hastalıkları'],
                'description' => ['en' => 'Complete eye care including vision tests and surgical treatments.', 'ar' => 'رعاية كاملة للعيون تشمل فحوصات الرؤية والعلاجات الجراحية.', 'tr' => 'Görme testleri ve cerrahi tedaviler dahil tam göz bakımı.'],
            ],
            [
                'icon' => 'ti tabler-mood-kid',
                'title' => ['en' => 'Pediatrics', 'ar' => 'طب الأطفال', 'tr' => 'Pediatri'],
                'description' => ['en' => 'Specialized healthcare for infants, children, and adolescents.', 'ar' => 'رعاية صحية متخصصة للرضع والأطفال والمراهقين.', 'tr' => 'Bebekler, çocuklar ve ergenler için özel sağlık hizmeti.'],
            ],
        ];

        foreach ($services as $index => $service) {
            PanelItem::create([
                'panel_id' => $featuresPanel->id,
                'type' => PanelItemTypeEnum::FEATURE_CARD,
                'title' => $service['title'],
                'content' => $service['description'],
                'order' => $index + 1,
                'is_active' => true,
                'data' => ['icon' => $service['icon']],
            ]);
        }

        // Create Team Panel (Doctors)
        $teamPanel = Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::TEAM,
            'title' => [
                'en' => 'Meet Our Doctors',
                'ar' => 'تعرف على أطبائنا',
                'tr' => 'Doktorlarımızla Tanışın',
            ],
            'order' => 2,
            'is_active' => true,
            'settings' => [
                'badge' => [
                    'en' => 'Expert Team',
                    'ar' => 'فريق متخصص',
                    'tr' => 'Uzman Ekip',
                ],
                'description' => [
                    'en' => 'Our experienced medical professionals are dedicated to your health',
                    'ar' => 'متخصصونا الطبيون ذوو الخبرة مكرسون لصحتك',
                    'tr' => 'Deneyimli tıp profesyonellerimiz sağlığınıza adanmıştır',
                ],
            ],
        ]);

        $doctors = [
            [
                'name' => 'Dr. Ahmed Hassan',
                'role' => ['en' => 'Cardiologist', 'ar' => 'طبيب قلب', 'tr' => 'Kardiyolog'],
                'bio' => ['en' => '15+ years of experience in cardiovascular medicine', 'ar' => 'أكثر من 15 عامًا من الخبرة في طب القلب والأوعية الدموية', 'tr' => 'Kardiyovasküler tıpta 15+ yıl deneyim'],
            ],
            [
                'name' => 'Dr. Sarah Miller',
                'role' => ['en' => 'Neurologist', 'ar' => 'طبيبة أعصاب', 'tr' => 'Nörolog'],
                'bio' => ['en' => 'Specialized in brain and nervous system disorders', 'ar' => 'متخصصة في اضطرابات الدماغ والجهاز العصبي', 'tr' => 'Beyin ve sinir sistemi bozukluklarında uzman'],
            ],
            [
                'name' => 'Dr. Omar Yilmaz',
                'role' => ['en' => 'Pediatrician', 'ar' => 'طبيب أطفال', 'tr' => 'Pediatrist'],
                'bio' => ['en' => 'Dedicated to providing compassionate care for children', 'ar' => 'مكرس لتقديم رعاية رحيمة للأطفال', 'tr' => 'Çocuklara şefkatli bakım sağlamaya adanmış'],
            ],
            [
                'name' => 'Dr. Fatima Al-Rashid',
                'role' => ['en' => 'Dermatologist', 'ar' => 'طبيبة جلدية', 'tr' => 'Dermatolog'],
                'bio' => ['en' => 'Expert in skin conditions and cosmetic treatments', 'ar' => 'خبيرة في الأمراض الجلدية والعلاجات التجميلية', 'tr' => 'Cilt hastalıkları ve kozmetik tedavilerde uzman'],
            ],
        ];

        foreach ($doctors as $index => $doctor) {
            $teamItem = PanelItem::create([
                'panel_id' => $teamPanel->id,
                'type' => PanelItemTypeEnum::TEAM_MEMBER,
                'title' => ['en' => $doctor['name'], 'ar' => $doctor['name'], 'tr' => $doctor['name']],
                'content' => $doctor['bio'],
                'order' => $index + 1,
                'is_active' => true,
                'data' => [
                    'name' => $doctor['name'],
                    'role' => $doctor['role'],
                    'social_links' => [],
                ],
            ]);

            // Add avatar image for team member
            $avatarNumber = ($index % 15) + 1;
            $avatarPath = public_path("assets/img/avatars/{$avatarNumber}.png");
            if (file_exists($avatarPath)) {
                $teamItem->addMedia($avatarPath)
                    ->preservingOriginal()
                    ->toMediaCollection('item_image');
            }
        }

        // Create Reviews Panel (Patient Testimonials)
        $reviewsPanel = Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::REVIEWS,
            'title' => [
                'en' => 'Patient Testimonials',
                'ar' => 'شهادات المرضى',
                'tr' => 'Hasta Yorumları',
            ],
            'order' => 3,
            'is_active' => true,
            'settings' => [
                'badge' => [
                    'en' => 'Testimonials',
                    'ar' => 'الشهادات',
                    'tr' => 'Referanslar',
                ],
                'description' => [
                    'en' => 'What our patients say about their experience',
                    'ar' => 'ما يقوله مرضانا عن تجربتهم',
                    'tr' => 'Hastalarımız deneyimleri hakkında ne diyor',
                ],
            ],
        ]);

        $reviews = [
            [
                'name' => 'Mohammed Ali',
                'role' => ['en' => 'Heart Patient', 'ar' => 'مريض قلب', 'tr' => 'Kalp Hastası'],
                'rating' => 5,
                'content' => ['en' => 'The cardiology team provided exceptional care. Dr. Hassan explained everything clearly and the follow-up care was outstanding.', 'ar' => 'قدم فريق أمراض القلب رعاية استثنائية. شرح الدكتور حسن كل شيء بوضوح وكانت رعاية المتابعة ممتازة.', 'tr' => 'Kardiyoloji ekibi olağanüstü bakım sağladı. Dr. Hassan her şeyi net bir şekilde açıkladı ve takip bakımı mükemmeldi.'],
            ],
            [
                'name' => 'Ayşe Demir',
                'role' => ['en' => 'Regular Patient', 'ar' => 'مريضة منتظمة', 'tr' => 'Düzenli Hasta'],
                'rating' => 5,
                'content' => ['en' => 'I have been coming here for years. The staff is always friendly and the doctors take time to listen to your concerns.', 'ar' => 'أتردد على هذا المكان منذ سنوات. الموظفون ودودون دائمًا والأطباء يأخذون الوقت للاستماع إلى مخاوفك.', 'tr' => 'Yıllardır buraya geliyorum. Personel her zaman samimi ve doktorlar endişelerinizi dinlemek için zaman ayırıyor.'],
            ],
            [
                'name' => 'John Smith',
                'role' => ['en' => 'Orthopedic Patient', 'ar' => 'مريض عظام', 'tr' => 'Ortopedi Hastası'],
                'rating' => 5,
                'content' => ['en' => 'After my knee surgery, the rehabilitation program was excellent. I am now back to my normal activities thanks to the wonderful team.', 'ar' => 'بعد جراحة الركبة، كان برنامج إعادة التأهيل ممتازًا. أنا الآن عدت إلى أنشطتي الطبيعية بفضل الفريق الرائع.', 'tr' => 'Diz ameliyatımdan sonra rehabilitasyon programı mükemmeldi. Harika ekip sayesinde artık normal aktivitelerime döndüm.'],
            ],
        ];

        foreach ($reviews as $index => $review) {
            $reviewItem = PanelItem::create([
                'panel_id' => $reviewsPanel->id,
                'type' => PanelItemTypeEnum::REVIEW,
                'title' => ['en' => $review['name'], 'ar' => $review['name'], 'tr' => $review['name']],
                'content' => $review['content'],
                'order' => $index + 1,
                'is_active' => true,
                'data' => [
                    'name' => $review['name'],
                    'role' => $review['role'],
                    'rating' => $review['rating'],
                ],
            ]);

            // Add avatar image for reviewer
            $avatarNumber = (($index + 5) % 15) + 1;
            $avatarPath = public_path("assets/img/avatars/{$avatarNumber}.png");
            if (file_exists($avatarPath)) {
                $reviewItem->addMedia($avatarPath)
                    ->preservingOriginal()
                    ->toMediaCollection('item_image');
            }
        }

        // Create Stats Panel (Clinic Statistics)
        $statsPanel = Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::STATS,
            'title' => [
                'en' => 'Our Achievements',
                'ar' => 'إنجازاتنا',
                'tr' => 'Başarılarımız',
            ],
            'order' => 4,
            'is_active' => true,
            'settings' => [],
        ]);

        $stats = [
            [
                'icon' => 'ti tabler-users',
                'number' => '50000',
                'suffix' => '+',
                'label' => ['en' => 'Patients Served', 'ar' => 'مريض تمت خدمتهم', 'tr' => 'Hizmet Verilen Hasta'],
            ],
            [
                'icon' => 'ti tabler-stethoscope',
                'number' => '120',
                'suffix' => '+',
                'label' => ['en' => 'Expert Doctors', 'ar' => 'طبيب متخصص', 'tr' => 'Uzman Doktor'],
            ],
            [
                'icon' => 'ti tabler-building-hospital',
                'number' => '15',
                'suffix' => '',
                'label' => ['en' => 'Medical Departments', 'ar' => 'قسم طبي', 'tr' => 'Tıbbi Bölüm'],
            ],
            [
                'icon' => 'ti tabler-award',
                'number' => '25',
                'suffix' => '+',
                'label' => ['en' => 'Years of Experience', 'ar' => 'سنة من الخبرة', 'tr' => 'Yıllık Deneyim'],
            ],
        ];

        foreach ($stats as $index => $stat) {
            PanelItem::create([
                'panel_id' => $statsPanel->id,
                'type' => PanelItemTypeEnum::STAT,
                'title' => $stat['label'],
                'order' => $index + 1,
                'is_active' => true,
                'data' => [
                    'icon' => $stat['icon'],
                    'number' => $stat['number'],
                    'suffix' => $stat['suffix'],
                    'label' => $stat['label'],
                ],
            ]);
        }

        // Create FAQ Panel
        $faqPanel = Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::FAQ,
            'title' => [
                'en' => 'Frequently Asked Questions',
                'ar' => 'الأسئلة الشائعة',
                'tr' => 'Sıkça Sorulan Sorular',
            ],
            'order' => 5,
            'is_active' => true,
            'settings' => [
                'badge' => [
                    'en' => 'FAQ',
                    'ar' => 'أسئلة شائعة',
                    'tr' => 'SSS',
                ],
                'description' => [
                    'en' => 'Find answers to common questions about our services',
                    'ar' => 'اعثر على إجابات للأسئلة الشائعة حول خدماتنا',
                    'tr' => 'Hizmetlerimiz hakkında sık sorulan sorulara cevaplar bulun',
                ],
            ],
        ]);

        $faqs = [
            [
                'question' => ['en' => 'How do I book an appointment?', 'ar' => 'كيف أحجز موعدًا؟', 'tr' => 'Nasıl randevu alabilirim?'],
                'answer' => ['en' => 'You can book an appointment through our online portal, by calling our reception, or visiting our clinic directly. Online booking is available 24/7 for your convenience.', 'ar' => 'يمكنك حجز موعد من خلال بوابتنا الإلكترونية أو عن طريق الاتصال بالاستقبال أو زيارة عيادتنا مباشرة. الحجز عبر الإنترنت متاح على مدار الساعة لراحتك.', 'tr' => 'Online portalımız üzerinden, resepsiyonumuzu arayarak veya kliniğimizi doğrudan ziyaret ederek randevu alabilirsiniz. Online randevu haftanın 7 günü 24 saat kullanılabilir.'],
            ],
            [
                'question' => ['en' => 'What insurance plans do you accept?', 'ar' => 'ما خطط التأمين التي تقبلونها؟', 'tr' => 'Hangi sigorta planlarını kabul ediyorsunuz?'],
                'answer' => ['en' => 'We accept most major insurance plans. Please contact our billing department or check our insurance page for a complete list of accepted providers.', 'ar' => 'نقبل معظم خطط التأمين الرئيسية. يرجى الاتصال بقسم الفواتير لدينا أو مراجعة صفحة التأمين للحصول على قائمة كاملة بمقدمي الخدمات المقبولين.', 'tr' => 'Çoğu büyük sigorta planını kabul ediyoruz. Kabul edilen sağlayıcıların tam listesi için lütfen faturalama bölümümüzle iletişime geçin veya sigorta sayfamızı kontrol edin.'],
            ],
            [
                'question' => ['en' => 'Do you offer telemedicine consultations?', 'ar' => 'هل تقدمون استشارات الطب عن بعد؟', 'tr' => 'Teletıp danışmanlığı sunuyor musunuz?'],
                'answer' => ['en' => 'Yes, we offer telemedicine consultations for many of our services. You can schedule a video consultation through our patient portal from the comfort of your home.', 'ar' => 'نعم، نقدم استشارات الطب عن بعد للعديد من خدماتنا. يمكنك جدولة استشارة فيديو من خلال بوابة المريض من راحة منزلك.', 'tr' => 'Evet, birçok hizmetimiz için teletıp danışmanlığı sunuyoruz. Hasta portalımız üzerinden evinizin rahatlığında video danışmanlığı planlayabilirsiniz.'],
            ],
            [
                'question' => ['en' => 'What are your operating hours?', 'ar' => 'ما هي ساعات عملكم؟', 'tr' => 'Çalışma saatleriniz nedir?'],
                'answer' => ['en' => 'Our clinic is open Monday to Friday from 8:00 AM to 8:00 PM, and Saturday from 9:00 AM to 5:00 PM. Emergency services are available 24/7.', 'ar' => 'عيادتنا مفتوحة من الاثنين إلى الجمعة من 8:00 صباحًا إلى 8:00 مساءً، والسبت من 9:00 صباحًا إلى 5:00 مساءً. خدمات الطوارئ متاحة على مدار الساعة.', 'tr' => 'Kliniğimiz Pazartesi-Cuma 08:00-20:00, Cumartesi 09:00-17:00 saatleri arasında açıktır. Acil servisler 7/24 kullanılabilir.'],
            ],
            [
                'question' => ['en' => 'How can I access my medical records?', 'ar' => 'كيف يمكنني الوصول إلى سجلاتي الطبية؟', 'tr' => 'Tıbbi kayıtlarıma nasıl erişebilirim?'],
                'answer' => ['en' => 'You can access your medical records through our secure patient portal. Simply log in with your credentials to view test results, prescriptions, and appointment history.', 'ar' => 'يمكنك الوصول إلى سجلاتك الطبية من خلال بوابة المريض الآمنة. ما عليك سوى تسجيل الدخول ببيانات اعتمادك لعرض نتائج الفحوصات والوصفات الطبية وتاريخ المواعيد.', 'tr' => 'Güvenli hasta portalımız üzerinden tıbbi kayıtlarınıza erişebilirsiniz. Test sonuçlarını, reçeteleri ve randevu geçmişini görüntülemek için kimlik bilgilerinizle giriş yapın.'],
            ],
        ];

        foreach ($faqs as $index => $faq) {
            PanelItem::create([
                'panel_id' => $faqPanel->id,
                'type' => PanelItemTypeEnum::FAQ_ITEM,
                'title' => $faq['question'],
                'content' => $faq['answer'],
                'order' => $index + 1,
                'is_active' => true,
                'data' => [
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ],
            ]);
        }

        // Create CTA Panel
        Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::CTA,
            'title' => [
                'en' => 'Ready to Take Care of Your Health?',
                'ar' => 'مستعد للعناية بصحتك؟',
                'tr' => 'Sağlığınıza Bakmaya Hazır mısınız?',
            ],
            'order' => 6,
            'is_active' => true,
            'settings' => [
                'subtitle' => [
                    'en' => 'Book your appointment today and experience quality healthcare',
                    'ar' => 'احجز موعدك اليوم واستمتع برعاية صحية عالية الجودة',
                    'tr' => 'Bugün randevunuzu alın ve kaliteli sağlık hizmeti deneyimleyin',
                ],
                'button_text' => [
                    'en' => 'Book Appointment',
                    'ar' => 'احجز موعد',
                    'tr' => 'Randevu Al',
                ],
                'button_url' => '/patient/register',
            ],
        ]);

        // Create Contact Panel
        Panel::create([
            'page_id' => $homePage->id,
            'type' => PanelTypeEnum::CONTACT,
            'title' => [
                'en' => 'Contact Us',
                'ar' => 'اتصل بنا',
                'tr' => 'Bize Ulaşın',
            ],
            'order' => 7,
            'is_active' => true,
            'settings' => [
                'badge' => [
                    'en' => 'Get in Touch',
                    'ar' => 'تواصل معنا',
                    'tr' => 'İletişime Geçin',
                ],
                'description' => [
                    'en' => 'Have questions? We are here to help you',
                    'ar' => 'لديك أسئلة؟ نحن هنا لمساعدتك',
                    'tr' => 'Sorularınız mı var? Size yardımcı olmak için buradayız',
                ],
                'email' => 'info@medicalclinic.com',
                'phone' => '+90 555 123 4567',
                'address' => [
                    'en' => '123 Medical Center Drive, Health City',
                    'ar' => '123 شارع المركز الطبي، مدينة الصحة',
                    'tr' => '123 Tıp Merkezi Caddesi, Sağlık Şehri',
                ],
            ],
        ]);
    }
}
