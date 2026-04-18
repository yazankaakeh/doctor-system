<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Blog\Enums\PostTypeEnum;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogPostTags;
use Modules\Core\App\Enums\ActiveEnum;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            [
                'title' => ['en' => 'Technology', 'ar' => 'التكنولوجيا', 'tr' => 'Teknoloji'],
                'description' => ['en' => 'Latest technology news and updates', 'ar' => 'آخر أخبار وتحديثات التكنولوجيا', 'tr' => 'En son teknoloji haberleri ve güncellemeleri'],
                'is_active' => ActiveEnum::ACTIVE,
            ],
            [
                'title' => ['en' => 'Healthcare', 'ar' => 'الرعاية الصحية', 'tr' => 'Sağlık'],
                'description' => ['en' => 'Health tips and medical insights', 'ar' => 'نصائح صحية ورؤى طبية', 'tr' => 'Sağlık ipuçları ve tıbbi görüşler'],
                'is_active' => ActiveEnum::ACTIVE,
            ],
            [
                'title' => ['en' => 'Business', 'ar' => 'الأعمال', 'tr' => 'İş'],
                'description' => ['en' => 'Business strategies and entrepreneurship', 'ar' => 'استراتيجيات الأعمال وريادة الأعمال', 'tr' => 'İş stratejileri ve girişimcilik'],
                'is_active' => ActiveEnum::ACTIVE,
            ],
            [
                'title' => ['en' => 'Lifestyle', 'ar' => 'نمط الحياة', 'tr' => 'Yaşam Tarzı'],
                'description' => ['en' => 'Tips for better living', 'ar' => 'نصائح لحياة أفضل', 'tr' => 'Daha iyi yaşam için ipuçları'],
                'is_active' => ActiveEnum::ACTIVE,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $category) {
            $createdCategories[] = BlogCategory::create($category);
        }

        // Create tags
        $tags = [
            ['name' => ['en' => 'Innovation', 'ar' => 'الابتكار', 'tr' => 'Yenilik']],
            ['name' => ['en' => 'AI', 'ar' => 'الذكاء الاصطناعي', 'tr' => 'Yapay Zeka']],
            ['name' => ['en' => 'Wellness', 'ar' => 'العافية', 'tr' => 'Sağlıklı Yaşam']],
            ['name' => ['en' => 'Productivity', 'ar' => 'الإنتاجية', 'tr' => 'Verimlilik']],
            ['name' => ['en' => 'Digital', 'ar' => 'رقمي', 'tr' => 'Dijital']],
            ['name' => ['en' => 'Leadership', 'ar' => 'القيادة', 'tr' => 'Liderlik']],
            ['name' => ['en' => 'Design', 'ar' => 'التصميم', 'tr' => 'Tasarım']],
            ['name' => ['en' => 'Tips', 'ar' => 'نصائح', 'tr' => 'İpuçları']],
        ];

        $createdTags = [];
        foreach ($tags as $tag) {
            $createdTags[] = BlogPostTags::create($tag);
        }

        // Create blog posts
        $posts = [
            [
                'category_id' => $createdCategories[0]->id, // Technology
                'title' => [
                    'en' => 'The Future of Artificial Intelligence in Healthcare',
                    'ar' => 'مستقبل الذكاء الاصطناعي في الرعاية الصحية',
                    'tr' => 'Sağlık Hizmetlerinde Yapay Zekanın Geleceği',
                ],
                'description' => [
                    'en' => '<h2>Introduction</h2><p>Artificial Intelligence is revolutionizing healthcare in unprecedented ways. From diagnostic tools to personalized treatment plans, AI is reshaping how we approach medical care.</p><h3>Key Benefits</h3><ul><li>Faster and more accurate diagnoses</li><li>Personalized treatment recommendations</li><li>Predictive analytics for disease prevention</li><li>Automated administrative tasks</li></ul><p>The integration of AI in healthcare promises a future where medical professionals can focus more on patient care while technology handles routine tasks.</p>',
                    'ar' => '<h2>مقدمة</h2><p>يُحدث الذكاء الاصطناعي ثورة في الرعاية الصحية بطرق غير مسبوقة. من أدوات التشخيص إلى خطط العلاج الشخصية، يعيد الذكاء الاصطناعي تشكيل كيفية التعامل مع الرعاية الطبية.</p>',
                    'tr' => '<h2>Giriş</h2><p>Yapay Zeka, sağlık hizmetlerinde benzeri görülmemiş şekillerde devrim yaratıyor. Teşhis araçlarından kişiselleştirilmiş tedavi planlarına kadar, YZ tıbbi bakıma yaklaşımımızı yeniden şekillendiriyor.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [1, 2, 3], // Innovation, AI, Wellness
            ],
            [
                'category_id' => $createdCategories[1]->id, // Healthcare
                'title' => [
                    'en' => '10 Essential Tips for Mental Wellness',
                    'ar' => '10 نصائح أساسية للصحة النفسية',
                    'tr' => 'Zihinsel Sağlık İçin 10 Temel İpucu',
                ],
                'description' => [
                    'en' => '<h2>Mental Health Matters</h2><p>Taking care of your mental health is just as important as physical health. Here are 10 essential tips to maintain your mental wellness:</p><ol><li>Practice mindfulness and meditation daily</li><li>Maintain a consistent sleep schedule</li><li>Exercise regularly</li><li>Stay connected with loved ones</li><li>Set boundaries and learn to say no</li><li>Seek professional help when needed</li><li>Limit social media consumption</li><li>Practice gratitude</li><li>Engage in hobbies you enjoy</li><li>Take breaks and rest</li></ol><p>Remember, mental wellness is a journey, not a destination.</p>',
                    'ar' => '<h2>الصحة النفسية مهمة</h2><p>إن الاهتمام بصحتك النفسية لا يقل أهمية عن الصحة البدنية. إليك 10 نصائح أساسية للحفاظ على صحتك النفسية.</p>',
                    'tr' => '<h2>Zihinsel Sağlık Önemlidir</h2><p>Zihinsel sağlığınıza özen göstermek, fiziksel sağlık kadar önemlidir. Zihinsel sağlığınızı korumak için 10 temel ipucu.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [3, 8], // Wellness, Tips
            ],
            [
                'category_id' => $createdCategories[2]->id, // Business
                'title' => [
                    'en' => 'Building a Successful Startup: Lessons from Top Entrepreneurs',
                    'ar' => 'بناء شركة ناشئة ناجحة: دروس من كبار رواد الأعمال',
                    'tr' => 'Başarılı Bir Startup Kurmak: Önde Gelen Girişimcilerden Dersler',
                ],
                'description' => [
                    'en' => '<h2>The Startup Journey</h2><p>Starting a business is one of the most challenging yet rewarding experiences. Learn from the best entrepreneurs who have paved the way.</p><h3>Key Lessons</h3><ul><li><strong>Start with a problem worth solving</strong> - Focus on real pain points</li><li><strong>Build a strong team</strong> - Surround yourself with talented people</li><li><strong>Stay lean and iterate quickly</strong> - Fail fast, learn faster</li><li><strong>Focus on customer feedback</strong> - Your users are your best advisors</li><li><strong>Never stop learning</strong> - The market evolves, so should you</li></ul><p>Success in entrepreneurship comes from persistence, adaptability, and a willingness to learn from failures.</p>',
                    'ar' => '<h2>رحلة الشركات الناشئة</h2><p>بدء عمل تجاري هو أحد أكثر التجارب تحديًا ومكافأة. تعلم من أفضل رواد الأعمال الذين مهدوا الطريق.</p>',
                    'tr' => '<h2>Startup Yolculuğu</h2><p>İş kurmak, en zorlu ama aynı zamanda en ödüllendirici deneyimlerden biridir. Yolu açan en iyi girişimcilerden öğrenin.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [1, 4, 6], // Innovation, Productivity, Leadership
            ],
            [
                'category_id' => $createdCategories[0]->id, // Technology
                'title' => [
                    'en' => 'Understanding Cloud Computing: A Beginner\'s Guide',
                    'ar' => 'فهم الحوسبة السحابية: دليل المبتدئين',
                    'tr' => 'Bulut Bilişimi Anlamak: Başlangıç Kılavuzu',
                ],
                'description' => [
                    'en' => '<h2>What is Cloud Computing?</h2><p>Cloud computing has transformed how businesses operate and store data. This comprehensive guide will help you understand the basics.</p><h3>Types of Cloud Services</h3><ul><li><strong>IaaS (Infrastructure as a Service)</strong> - Rent virtual hardware</li><li><strong>PaaS (Platform as a Service)</strong> - Development platforms</li><li><strong>SaaS (Software as a Service)</strong> - Ready-to-use applications</li></ul><h3>Benefits</h3><p>Cost efficiency, scalability, accessibility, and disaster recovery are just some of the advantages of cloud computing.</p>',
                    'ar' => '<h2>ما هي الحوسبة السحابية؟</h2><p>لقد غيرت الحوسبة السحابية طريقة عمل الشركات وتخزين البيانات. سيساعدك هذا الدليل الشامل على فهم الأساسيات.</p>',
                    'tr' => '<h2>Bulut Bilişim Nedir?</h2><p>Bulut bilişim, işletmelerin çalışma ve veri depolama şeklini değiştirdi. Bu kapsamlı kılavuz, temel bilgileri anlamanıza yardımcı olacaktır.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [1, 5], // Innovation, Digital
            ],
            [
                'category_id' => $createdCategories[3]->id, // Lifestyle
                'title' => [
                    'en' => 'Mastering Work-Life Balance in the Digital Age',
                    'ar' => 'إتقان التوازن بين العمل والحياة في العصر الرقمي',
                    'tr' => 'Dijital Çağda İş-Yaşam Dengesinde Ustalaşmak',
                ],
                'description' => [
                    'en' => '<h2>Finding Balance</h2><p>In today\'s always-connected world, maintaining a healthy work-life balance has become more challenging than ever.</p><h3>Strategies for Balance</h3><ol><li>Set clear boundaries between work and personal time</li><li>Schedule regular breaks throughout the day</li><li>Prioritize tasks using time management techniques</li><li>Disconnect from devices before bedtime</li><li>Make time for hobbies and relationships</li></ol><p>Remember, balance looks different for everyone. Find what works for you and stick to it.</p>',
                    'ar' => '<h2>إيجاد التوازن</h2><p>في عالم اليوم المتصل دائمًا، أصبح الحفاظ على توازن صحي بين العمل والحياة أكثر تحديًا من أي وقت مضى.</p>',
                    'tr' => '<h2>Denge Bulmak</h2><p>Günümüzün sürekli bağlı dünyasında, sağlıklı bir iş-yaşam dengesini korumak her zamankinden daha zor hale geldi.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [3, 4, 8], // Wellness, Productivity, Tips
            ],
            [
                'category_id' => $createdCategories[2]->id, // Business
                'title' => [
                    'en' => 'The Power of Digital Marketing in 2025',
                    'ar' => 'قوة التسويق الرقمي في 2025',
                    'tr' => '2025\'te Dijital Pazarlamanın Gücü',
                ],
                'description' => [
                    'en' => '<h2>Digital Marketing Evolution</h2><p>As we progress through 2025, digital marketing continues to evolve at a rapid pace. Businesses must adapt to stay competitive.</p><h3>Key Trends</h3><ul><li>AI-powered personalization</li><li>Voice search optimization</li><li>Video content dominance</li><li>Influencer partnerships</li><li>Interactive content experiences</li></ul><p>Success in digital marketing requires staying ahead of trends and understanding your audience deeply.</p>',
                    'ar' => '<h2>تطور التسويق الرقمي</h2><p>مع تقدمنا ​​في عام 2025، يستمر التسويق الرقمي في التطور بوتيرة سريعة. يجب على الشركات التكيف للبقاء في المنافسة.</p>',
                    'tr' => '<h2>Dijital Pazarlama Evrimi</h2><p>2025\'e ilerlerken, dijital pazarlama hızla gelişmeye devam ediyor. İşletmeler rekabetçi kalmak için uyum sağlamalı.</p>',
                ],
                'type' => PostTypeEnum::PUBLISHED,
                'is_active' => ActiveEnum::ACTIVE,
                'clapping' => 0,
                'tags' => [1, 5, 7], // Innovation, Digital, Design
            ],
        ];

        foreach ($posts as $postData) {
            $tags = $postData['tags'];
            unset($postData['tags']);

            $post = BlogPost::create($postData);

            // Attach tags
            $post->tags()->attach($tags);

            // Add default image (you can customize this)
            // $post->addMediaFromUrl('https://via.placeholder.com/800x450')->toMediaCollection('img');
        }

        $this->command->info('Blog posts seeded successfully with categories and tags!');
    }
}
