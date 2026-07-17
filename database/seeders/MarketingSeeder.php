<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\BrandChat;
use App\Models\BrandNameSuggestion;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\PricingPackage;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $img1 = 'https://placehold.co/800x600/1a1a1a/c9a84c?text=Portfolio';
        $img2 = 'https://placehold.co/800x600/1a1a1a/c9a84c?text=Brand';

        SiteSetting::query()->delete();
        SiteSetting::create([
            'brand_name_en' => 'deepmarks',
            'brand_name_ar' => 'ديبماركس',
            'logo_url' => null,
            'login_cta_label_en' => 'Login',
            'login_cta_label_ar' => 'تسجيل الدخول',
            'login_cta_url' => '/name-branding',
            'start_cta_label_en' => 'Start now',
            'start_cta_label_ar' => 'ابدأ الآن',
            'start_cta_url' => '/name-branding',
            'footer_tagline_en' => 'Discover premium branding and visual design solutions tailored to your business vision.',
            'footer_tagline_ar' => 'اكتشف حلول العلامة التجارية والتصميم البصري المتميزة والمصممة خصيصاً لرؤية عملك.',
            'footer_copyright_en' => '© 2026 Deepmarks. All rights reserved.',
            'footer_copyright_ar' => '© 2026 ديبماركس. جميع الحقوق محفوظة.',
            'newsletter_placeholder_en' => 'Your email address',
            'newsletter_placeholder_ar' => 'بريدك الإلكتروني',
            'social_links' => [
                ['platform' => 'twitter', 'url' => 'https://x.com/Deepmark194898', 'label' => 'Twitter (X)'],
                ['platform' => 'instagram', 'url' => '#', 'label' => 'Instagram'],
                ['platform' => 'tiktok', 'url' => 'https://www.tiktok.com/@deepmarksai', 'label' => 'TikTok'],
                ['platform' => 'linkedin', 'url' => 'https://www.linkedin.com/company/deep-marks/', 'label' => 'LinkedIn'],
                ['platform' => 'facebook', 'url' => '#', 'label' => 'Facebook'],
            ],
            'contact_email' => 'hello@deepmarks.com',
            'contact_email_label_en' => 'Email',
            'contact_email_label_ar' => 'البريد الإلكتروني',
            'contact_email_desc_en' => 'For briefs, partnerships & support',
            'contact_email_desc_ar' => 'للموجزات والشراكات والدعم',
            'contact_checklist_en' => ['Your brand goals', 'Target audience', 'Timeline + budget range'],
            'contact_checklist_ar' => ['أهداف علامتك التجارية', 'الجمهور المستهدف', 'الجدول الزمني ونطاق الميزانية'],
            'contact_pill_en' => 'Contact Us',
            'contact_pill_ar' => 'تواصل معنا',
            'contact_form_label_en' => 'Contact form',
            'contact_form_label_ar' => 'نموذج التواصل',
            'contact_form_title_en' => 'Tell us about your project',
            'contact_form_title_ar' => 'أخبرنا عن مشروعك',
            'contact_form_lead_en' => "Share your goals and timeline. We'll review your request and get back with next steps.",
            'contact_form_lead_ar' => 'شارك أهدافك والجدول الزمني. سنراجع طلبك ونعود إليك بالخطوات التالية.',
            'contact_side_label_en' => 'Quick details',
            'contact_side_label_ar' => 'تفاصيل سريعة',
            'contact_side_title_en' => 'Reach Deepmarks',
            'contact_side_title_ar' => 'تواصل مع ديبماركس',
            'contact_side_lead_en' => "Choose the fastest way to contact us. We're happy to help with project questions and recommendations.",
            'contact_side_lead_ar' => 'اختر أسرع طريقة للتواصل معنا. يسعدنا المساعدة في أسئلة المشروع والتوصيات.',
            'contact_response_note_en' => 'We usually respond within 1–2 business days.',
            'contact_response_note_ar' => 'نرد عادة خلال يوم إلى يومين عمل.',
            'blogs_pill_en' => 'Blogs',
            'blogs_pill_ar' => 'المدونة',
            'blogs_title_en' => 'Latest insights from Deepmarks',
            'blogs_title_ar' => 'أحدث الرؤى من ديبماركس',
            'blogs_subtitle_en' => 'Thoughtful articles on naming, brand emotion, and identity systems.',
            'blogs_subtitle_ar' => 'مقالات مدروسة حول التسمية وعاطفة العلامة التجارية وأنظمة الهوية.',
        ]);

        HomeSection::query()->delete();
        $sections = [
            [
                'section_key' => 'hero',
                'sort_order' => 1,
                'content_en' => [
                    'pill' => 'DEEPMARKS INTELLIGENT SYSTEM',
                    'title_html' => "Brand names that connect on a<br /><em>deep emotional level</em>.<br />Powered by <em>expert AI linguistics </em>",
                    'subtitle_html' => 'Skip the generic lists. <strong>deepmarks </strong> is an elite naming platform driven by an expert linguistic AI designed to solve your branding needs instantly. We unlock distinctive, domain-ready brand names that go beyond surface definitions to build <strong> deep </strong> emotional connections with your audience and command ultimate market authority.',
                    'cta_label' => 'Generate Your Brand Name',
                    'cta_url' => '/name-branding',
                ],
                'content_ar' => [
                    'pill' => 'نظام ديبماركس الذكي',
                    'title_html' => 'أسماء علامات تجارية تتصل على<br /><em>مستوى عاطفي عميق</em>.<br />مدعومة بـ <em>لغويات الذكاء الاصطناعي المتخصصة</em>',
                    'subtitle_html' => 'تجاوز القوائم العامة. <strong>ديبماركس</strong> منصة تسمية نخبوية مدفوعة بذكاء اصطناعي لغوي متخصص لحل احتياجات علامتك فوراً.',
                    'cta_label' => 'أنشئ اسم علامتك التجارية',
                    'cta_url' => '/name-branding',
                ],
            ],
            [
                'section_key' => 'about',
                'sort_order' => 2,
                'content_en' => [
                    'label' => 'About Deepmarks',
                    'title' => 'Visual Case Studies & Brand Execution Guides',
                    'lead' => 'We turn your generated brand name into a complete visual brand identity and comprehensive execution guide. We build out logo marks, color systems, and strict typography rules to bring your concept to life and carve deep emotional connections with your clients.',
                    'bullets' => [
                        'Science based naming · based on name archetypes',
                        '47% growth in new customers',
                        'Precise names that fit your brand',
                    ],
                    'cta_label' => 'Generate Your Brand Name',
                    'cta_url' => '/name-branding',
                    'stats' => [
                        ['value' => '200+', 'label' => 'Happy Customers'],
                        ['value' => '47%', 'label' => 'Avg. Growth'],
                        ['value' => '3', 'label' => 'Expert Packages'],
                        ['value' => '∞', 'label' => 'Creative Ideas'],
                    ],
                ],
                'content_ar' => [
                    'label' => 'عن ديبماركس',
                    'title' => 'دراسات حالة بصرية وأدلة تنفيذ العلامة التجارية',
                    'lead' => 'نحول اسم علامتك التجارية إلى هوية بصرية كاملة ودليل تنفيذ شامل.',
                    'bullets' => [
                        'تسمية علمية مبنية على نماذج الأسماء',
                        'نمو بنسبة 47% في العملاء الجدد',
                        'أسماء دقيقة تناسب علامتك',
                    ],
                    'cta_label' => 'أنشئ اسم علامتك التجارية',
                    'cta_url' => '/name-branding',
                    'stats' => [
                        ['value' => '200+', 'label' => 'عملاء سعداء'],
                        ['value' => '47%', 'label' => 'متوسط النمو'],
                        ['value' => '3', 'label' => 'باقات متخصصة'],
                        ['value' => '∞', 'label' => 'أفكار إبداعية'],
                    ],
                ],
            ],
            [
                'section_key' => 'portfolio_intro',
                'sort_order' => 3,
                'content_en' => [
                    'label' => 'Portfolio',
                    'title_html' => 'Our Selected Projects<br /><em class="deepmarks-gold">That Elevate Brands</em>',
                    'subtitle' => 'A curated collection of our best work — crafted with precision and brought to life in collaboration with visionary entrepreneurs, startups, and small businesses.',
                    'cta_label' => 'See More',
                    'cta_url' => '/contact',
                ],
                'content_ar' => [
                    'label' => 'معرض الأعمال',
                    'title_html' => 'مشاريعنا المختارة<br /><em class="deepmarks-gold">التي ترتقي بالعلامات</em>',
                    'subtitle' => 'مجموعة منتقاة من أفضل أعمالنا — صُممت بدقة وأُنجزت بالتعاون مع رواد أعمال ورؤى ناشئة.',
                    'cta_label' => 'المزيد',
                    'cta_url' => '/contact',
                ],
            ],
            [
                'section_key' => 'features',
                'sort_order' => 4,
                'content_en' => [
                    'label' => 'Built for Entrepreneurs',
                    'title' => 'Powered by branding science & world-class design',
                    'items' => [
                        [
                            'icon' => 'bi-layers',
                            'title' => 'Intentional Branding — Not Just a Logo',
                            'body' => "Your brand's visual identity shows people who you are — how they see, trust, and remember your business. At Deepmarks, we don't just make random visuals. We deliver branding and identity solutions designed with strategy, clarity, and impact — mixing brand strategy consulting, brand design services, and brand identity development.",
                        ],
                        [
                            'icon' => 'bi-person-hearts',
                            'title' => 'Emotionally Aligned Brand Archetypes',
                            'body' => "A strong brand has a personality people remember. We use research-backed archetypes to define your brand's emotional core, so your logo, colors, typography, and messaging all speak with the same voice. Archetypes are the emotional DNA of every brand — universal patterns that shape how people connect, trust, and remember.",
                        ],
                        [
                            'icon' => 'bi-graph-up-arrow',
                            'title' => 'Brand Positioning & Strategy',
                            'body' => 'We work with you to define your unique space in the market. Through competitive analysis, audience research, and strategic positioning frameworks, we ensure your brand stands out with clarity, authority, and purpose.',
                        ],
                        [
                            'icon' => 'bi-palette2',
                            'title' => 'Complete Visual Identity System',
                            'body' => "From logo creation to typography, color palettes, and real-world brand applications — we build cohesive systems that scale. Every touchpoint is crafted to look premium, consistent, and aligned with your brand's core personality.",
                        ],
                    ],
                ],
                'content_ar' => [
                    'label' => 'مصمم لرواد الأعمال',
                    'title' => 'مدعوم بعلم العلامة التجارية وتصميم عالمي المستوى',
                    'items' => [
                        [
                            'icon' => 'bi-layers',
                            'title' => 'علامة تجارية مقصودة — وليس مجرد شعار',
                            'body' => 'هويتك البصرية تُظهر للناس من أنت. نقدم حلول هوية مصممة بالاستراتيجية والوضوح والتأثير.',
                        ],
                        [
                            'icon' => 'bi-person-hearts',
                            'title' => 'نماذج عاطفية متوافقة',
                            'body' => 'نستخدم نماذج بحثية لتحديد الجوهر العاطفي لعلامتك حتى تتحدث كل عناصرها بصوت واحد.',
                        ],
                        [
                            'icon' => 'bi-graph-up-arrow',
                            'title' => 'تموضع واستراتيجية العلامة',
                            'body' => 'نحدد معك مساحتك الفريدة في السوق عبر التحليل والأطر الاستراتيجية.',
                        ],
                        [
                            'icon' => 'bi-palette2',
                            'title' => 'نظام هوية بصرية متكامل',
                            'body' => 'من الشعار إلى التطبيقات الواقعية — نبني أنظمة متماسكة قابلة للتوسع.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'process',
                'sort_order' => 5,
                'content_en' => [
                    'label' => 'How it Works',
                    'title' => 'Four steps to your premium brand',
                    'intro' => 'From first brief to final delivery — a clear, collaborative process designed to bring your brand to life with precision and purpose.',
                    'cta_label' => 'Get started',
                    'cta_url' => '/name-branding',
                    'steps' => [
                        [
                            'num' => '01',
                            'title' => 'Explore Our Creative Work',
                            'body' => "Browse Deepmarks' portfolio and discover premium brand identities crafted for modern businesses. From logos to full visual systems, every project showcases our design quality and creative direction.",
                        ],
                        [
                            'num' => '02',
                            'title' => 'Submit Your Brand Request',
                            'body' => "Fill out the form with your business details, your name, email, and project goals. Choose the package that fits your needs and tell us what kind of visual identity you're looking for.",
                        ],
                        [
                            'num' => '03',
                            'title' => 'Connect With Our Designers',
                            'body' => "Our team reviews your submission and reaches out to discuss your vision, timeline, and expectations. You'll be matched with the designer whose style best fits your brand direction.",
                        ],
                        [
                            'num' => '04',
                            'title' => 'Launch with Confidence',
                            'body' => 'Walk away with a complete visual identity system — premium, consistent, and ready to scale. From logo to real-world applications, your brand will look strong across every touchpoint.',
                        ],
                    ],
                ],
                'content_ar' => [
                    'label' => 'كيف يعمل',
                    'title' => 'أربع خطوات لعلامتك المتميزة',
                    'intro' => 'من الموجز الأول إلى التسليم النهائي — عملية واضحة وتعاونية.',
                    'cta_label' => 'ابدأ الآن',
                    'cta_url' => '/name-branding',
                    'steps' => [
                        ['num' => '01', 'title' => 'استكشف أعمالنا', 'body' => 'تصفح معرض أعمال ديبماركس واكتشف هويات علامات متميزة.'],
                        ['num' => '02', 'title' => 'قدّم طلبك', 'body' => 'املأ النموذج بتفاصيل عملك وأهداف مشروعك.'],
                        ['num' => '03', 'title' => 'تواصل مع المصممين', 'body' => 'يراجع فريقنا طلبك ويتواصل لمناقشة رؤيتك.'],
                        ['num' => '04', 'title' => 'انطلق بثقة', 'body' => 'احصل على نظام هوية بصرية كامل جاهز للتوسع.'],
                    ],
                ],
            ],
            [
                'section_key' => 'pricing_intro',
                'sort_order' => 6,
                'content_en' => [
                    'label' => 'Pricing',
                    'title' => 'Plans for all businesses',
                    'subtitle' => 'Suitable for Personal, Agencies & Startups.',
                ],
                'content_ar' => [
                    'label' => 'الأسعار',
                    'title' => 'خطط لجميع الأعمال',
                    'subtitle' => 'مناسبة للأفراد والوكالات والشركات الناشئة.',
                ],
            ],
            [
                'section_key' => 'faq_intro',
                'sort_order' => 7,
                'content_en' => [
                    'label' => 'FAQ',
                    'title' => 'Frequently Asked Questions',
                    'subtitle' => 'Have questions? Our FAQ section has you covered with quick answers to the most common inquiries.',
                ],
                'content_ar' => [
                    'label' => 'الأسئلة الشائعة',
                    'title' => 'الأسئلة المتكررة',
                    'subtitle' => 'هل لديك أسئلة؟ قسم الأسئلة الشائعة يغطي الاستفسارات الأكثر شيوعاً.',
                ],
            ],
            [
                'section_key' => 'cta',
                'sort_order' => 8,
                'content_en' => [
                    'label' => 'Join Us Now',
                    'title_html' => 'Each Project We Undertake<br /><em class="deepmarks-gold">is a Unique Opportunity.</em>',
                    'body' => 'Ready to take the next step? Join us now and start transforming your vision into reality with expert support.',
                    'cta_label' => 'Start now',
                    'cta_url' => '/contact',
                ],
                'content_ar' => [
                    'label' => 'انضم إلينا الآن',
                    'title_html' => 'كل مشروع نقوم به<br /><em class="deepmarks-gold">فرصة فريدة.</em>',
                    'body' => 'هل أنت مستعد للخطوة التالية؟ انضم إلينا الآن وحوّل رؤيتك إلى واقع.',
                    'cta_label' => 'ابدأ الآن',
                    'cta_url' => '/contact',
                ],
            ],
        ];

        foreach ($sections as $section) {
            HomeSection::create(array_merge($section, ['is_active' => true]));
        }

        // Portfolio projects reuse BrandNameSuggestion (marketing featured)
        $demoChat = BrandChat::query()->firstOrCreate(
            ['topic' => 'marketing-portfolio-demo'],
            [
                'user_id' => null,
                'language' => 'en',
                'answers' => ['source' => 'marketing_seeder'],
                'response' => null,
            ]
        );

        BrandNameSuggestion::query()->where('brand_chat_id', $demoChat->id)->delete();
        $projects = [
            [
                'suggestion_index' => 1,
                'name' => 'Lift2026',
                'archetype' => 'Hero',
                'domains' => ['lift2026.com', 'lift2026.ai'],
                'liked' => true,
                'is_marketing_featured' => true,
                'marketing_image_url' => $img1,
                'marketing_author_name' => 'Alex Doe',
                'marketing_author_position' => 'Lead Designer',
                'marketing_author_avatar_url' => $img1,
                'marketing_description_en' => 'Premium brand identity system for a modern fitness and lifestyle brand.',
                'marketing_description_ar' => 'نظام هوية علامة متميز لعلامة لياقة وأسلوب حياة حديثة.',
                'marketing_lead_en' => 'Lift2026 is a modern fitness and lifestyle brand. We developed the brand strategy, visual identity, color system, and flexible logo system that scales across digital and physical touchpoints.',
                'marketing_lead_ar' => 'ليفت 2026 علامة لياقة وأسلوب حياة حديثة. طورنا الاستراتيجية والهوية البصرية ونظام الألوان والشعار.',
                'marketing_gallery_images' => [$img1, $img2],
                'marketing_content_en' => [
                    'We built a bold visual system that supports both premium performance messaging and approachable sports lifestyle storytelling.',
                    'The color palette and typography were designed to feel energetic, confident, and modern across digital, packaging, and apparel.',
                    'Deliverables included logo variations, application mockups, brand guidelines, and identity rules for a fast-growing fitness ecosystem.',
                ],
                'marketing_content_ar' => [
                    'بنينا نظاماً بصرياً جريئاً يدعم رسائل الأداء المتميز وسرد أسلوب الحياة الرياضي.',
                    'صُممت لوحة الألوان والخطوط لتبدو حيوية وواثقة وحديثة.',
                    'شملت التسليمات تنويعات الشعار والنماذج الإرشادية وقواعد الهوية.',
                ],
                'marketing_deliverables_en' => [
                    'Brand strategy and positioning',
                    'Primary and secondary logo system',
                    'Color palette and typography rules',
                    'Application mockups and guidelines',
                ],
                'marketing_deliverables_ar' => [
                    'استراتيجية وتموضع العلامة',
                    'نظام الشعار الأساسي والثانوي',
                    'لوحة الألوان وقواعد الخطوط',
                    'نماذج تطبيقية وإرشادات',
                ],
            ],
            [
                'suggestion_index' => 2,
                'name' => 'ZAT',
                'archetype' => 'Creator',
                'domains' => ['zat.brand', 'zat.co'],
                'liked' => true,
                'is_marketing_featured' => true,
                'marketing_image_url' => $img2,
                'marketing_author_name' => 'Maria Lee',
                'marketing_author_position' => 'Brand Strategist',
                'marketing_author_avatar_url' => $img2,
                'marketing_description_en' => 'Culturally rich branding for a lifestyle brand rooted in identity.',
                'marketing_description_ar' => 'علامة تجارية غنية ثقافياً لأسلوب حياة متجذر في الهوية.',
                'marketing_lead_en' => 'ZAT is a lifestyle brand that blends cultural storytelling with modern luxury. We crafted a brand identity that feels both rooted and aspirational.',
                'marketing_lead_ar' => 'زات علامة أسلوب حياة تمزج السرد الثقافي مع الفخامة الحديثة.',
                'marketing_gallery_images' => [$img2],
                'marketing_content_en' => [
                    'The identity system supports a narrative that honors heritage while remaining fresh and contemporary across product and digital experiences.',
                    'Logo locking, color treatments, and brand photography direction were created to reinforce a premium yet approachable feeling.',
                    'Hands-on guidelines help the team maintain consistency while allowing the brand to scale into new collections and spaces.',
                ],
                'marketing_content_ar' => [
                    'يدعم نظام الهوية سرداً يكرم التراث مع البقاء معاصراً.',
                    'أُنشئت قواعد الشعار والألوان واتجاه التصوير لتعزيز الإحساس المتميز.',
                    'تساعد الإرشادات العملية الفريق على الحفاظ على الاتساق أثناء التوسع.',
                ],
                'marketing_deliverables_en' => [
                    'Identity system and logo guidelines',
                    'Cultural storytelling framework',
                    'Packaging and lifestyle mockups',
                    'Typeface and color application rules',
                ],
                'marketing_deliverables_ar' => [
                    'نظام الهوية وإرشادات الشعار',
                    'إطار السرد الثقافي',
                    'نماذج التغليف وأسلوب الحياة',
                    'قواعد تطبيق الخطوط والألوان',
                ],
            ],
            [
                'suggestion_index' => 3,
                'name' => 'Nogoom FC',
                'archetype' => 'Ruler',
                'domains' => ['nogoomfc.com'],
                'liked' => true,
                'is_marketing_featured' => true,
                'marketing_image_url' => $img1,
                'marketing_author_name' => 'Omar Salah',
                'marketing_author_position' => 'Creative Director',
                'marketing_author_avatar_url' => $img1,
                'marketing_description_en' => 'Bold sports club identity with dynamic logo system and brand guidelines.',
                'marketing_description_ar' => 'هوية نادي رياضي جريئة مع نظام شعار ديناميكي وإرشادات العلامة.',
                'marketing_lead_en' => 'Nogoom FC required a visual identity that could energize fans, sponsors, and community activations with a single cohesive system.',
                'marketing_lead_ar' => 'احتاج نجوم إف سي إلى هوية بصرية تُحفّز الجماهير والرعاة بتناسق واحد.',
                'marketing_gallery_images' => [$img1],
                'marketing_content_en' => [
                    'We created a dynamic logo family and support graphics that work across jerseys, stadium signage, digital, and social content.',
                    'The identity uses bold angles, rich colors, and confident typography to feel modern, athletic, and memorable.',
                    'Complete brand guidelines ensure every expression of the club stays consistent and premium as the franchise grows.',
                ],
                'marketing_content_ar' => [
                    'أنشأنا عائلة شعارات ديناميكية ورسومات داعمة عبر القمصان واللافتات والمحتوى الرقمي.',
                    'تستخدم الهوية زوايا جريئة وألواناً غنية وخطوطاً واثقة.',
                    'تضمن الإرشادات الكاملة اتساق كل تعبير للنادي أثناء النمو.',
                ],
                'marketing_deliverables_en' => [
                    'Dynamic logo system',
                    'Club identity and pattern system',
                    'Environmental graphics and merchandise mockups',
                    'Visual brand guidelines and usage rules',
                ],
                'marketing_deliverables_ar' => [
                    'نظام شعار ديناميكي',
                    'هوية النادي ونظام الأنماط',
                    'رسومات بيئية ونماذج بضائع',
                    'إرشادات بصرية وقواعد الاستخدام',
                ],
            ],
        ];
        foreach ($projects as $project) {
            BrandNameSuggestion::create(array_merge($project, [
                'brand_chat_id' => $demoChat->id,
            ]));
        }

        BlogPost::query()->delete();
        $blogs = [
            [
                'slug' => 'deep-emotion-branding',
                'published_at' => '2026-06-12',
                'title_en' => 'How deep emotion branding creates loyalty',
                'title_ar' => 'كيف تخلق العلامة العاطفية العميقة الولاء',
                'badge_en' => 'Brand Psychology',
                'badge_ar' => 'علم نفس العلامة',
                'image_url' => $img1,
                'author_name' => 'Noor Hassan',
                'author_title_en' => 'Creative Director',
                'author_title_ar' => 'المدير الإبداعي',
                'author_avatar_url' => $img1,
                'lead_en' => 'How deep emotion branding creates loyalty — a Deepmarks perspective on what makes brand execution feel instant, emotional, and premium.',
                'lead_ar' => 'كيف تخلق العلامة العاطفية العميقة الولاء — منظور ديبماركس.',
                'content_en' => [
                    'Deep emotion branding is not about being loud — it is about being resonant. When your brand consistently signals the same feelings, audiences stop “noticing” and start “trusting.”',
                    'Names, visuals, and tone should work together like one system. The moment they align, your brand becomes easy to remember and easier to choose.',
                    'At Deepmarks, we treat emotional clarity as a design requirement — translating it into naming, identity rules, and real touchpoints.',
                ],
                'content_ar' => [
                    'العلامة العاطفية العميقة ليست عن الصخب — بل عن الرنين. عندما تشير علامتك باستمرار لنفس المشاعر، يبدأ الجمهور بالثقة.',
                    'الأسماء والمرئيات والنبرة يجب أن تعمل كنظام واحد.',
                    'في ديبماركس نتعامل مع الوضوح العاطفي كمتطلب تصميمي.',
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'naming-science',
                'published_at' => '2026-05-29',
                'title_en' => 'Naming science: making brands feel instantly right',
                'title_ar' => 'علم التسمية: جعل العلامات تشعر بالصواب فوراً',
                'badge_en' => 'AI Linguistics',
                'badge_ar' => 'لغويات الذكاء الاصطناعي',
                'image_url' => $img2,
                'author_name' => 'Mariam Saeed',
                'author_title_en' => 'Brand Strategist',
                'author_title_ar' => 'استراتيجية العلامة',
                'author_avatar_url' => $img2,
                'lead_en' => 'Naming science: making brands feel instantly right — a Deepmarks perspective.',
                'lead_ar' => 'علم التسمية — منظور ديبماركس.',
                'content_en' => [
                    'A great name is more than a label. It is a promise — delivered through sound, rhythm, and meaning.',
                    'Our approach combines linguistic intelligence with brand archetype research to create names that feel aligned before the first interaction.',
                    'You get options that are structured, distinctive, and ready to scale across marketing, product, and community.',
                ],
                'content_ar' => [
                    'الاسم العظيم أكثر من تسمية. إنه وعد يُقدَّم عبر الصوت والإيقاع والمعنى.',
                    'يجمع نهجنا بين الذكاء اللغوي وبحث نماذج العلامات.',
                    'تحصل على خيارات منظمة ومميزة وجاهزة للتوسع.',
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'identity-systems',
                'published_at' => '2026-04-18',
                'title_en' => 'Building identity systems that scale across touchpoints',
                'title_ar' => 'بناء أنظمة هوية تتوسع عبر نقاط التواصل',
                'badge_en' => 'Design Systems',
                'badge_ar' => 'أنظمة التصميم',
                'image_url' => $img1,
                'author_name' => 'Tarek Amin',
                'author_title_en' => 'Design Lead',
                'author_title_ar' => 'قائد التصميم',
                'author_avatar_url' => $img1,
                'lead_en' => 'Building identity systems that scale across touchpoints — a Deepmarks perspective.',
                'lead_ar' => 'بناء أنظمة هوية قابلة للتوسع — منظور ديبماركس.',
                'content_en' => [
                    'Modern brands live everywhere: web, mobile, packaging, slides, social, and internal tools. Identity systems make your brand consistent without slowing you down.',
                    'We build rules — not just assets. That means typography logic, spacing patterns, color usage, and flexible logo behavior across real scenarios.',
                    'The result is execution that looks premium today and still holds up tomorrow.',
                ],
                'content_ar' => [
                    'العلامات الحديثة تعيش في كل مكان. أنظمة الهوية تجعل علامتك متسقة دون إبطائك.',
                    'نبني قواعد — وليس مجرد أصول.',
                    'النتيجة تنفيذ يبدو متميزاً اليوم ويصمد غداً.',
                ],
                'sort_order' => 3,
            ],
            [
                'slug' => 'voice-and-archetypes',
                'published_at' => '2026-03-05',
                'title_en' => 'Voice & archetypes: the emotional DNA of your brand',
                'title_ar' => 'الصوت والنماذج: الحمض النووي العاطفي لعلامتك',
                'badge_en' => 'Strategy',
                'badge_ar' => 'استراتيجية',
                'image_url' => $img2,
                'author_name' => 'Lina Farouk',
                'author_title_en' => 'Strategy Partner',
                'author_title_ar' => 'شريكة الاستراتيجية',
                'author_avatar_url' => $img2,
                'lead_en' => 'Voice & archetypes: the emotional DNA of your brand — a Deepmarks perspective.',
                'lead_ar' => 'الصوت والنماذج — منظور ديبماركس.',
                'content_en' => [
                    'Brand archetypes define the emotional “why” behind your voice. They guide language choices so your messaging feels coherent at every touchpoint.',
                    'When archetypes and naming agree, everything else becomes easier: tone, visuals, and the way your story lands.',
                    'Deepmarks turns this into a practical direction you can use immediately.',
                ],
                'content_ar' => [
                    'نماذج العلامة تحدد الـ"لماذا" العاطفي خلف صوتك.',
                    'عندما تتوافق النماذج والتسمية يصبح كل شيء أسهل.',
                    'يحول ديبماركس هذا إلى اتجاه عملي يمكنك استخدامه فوراً.',
                ],
                'sort_order' => 4,
            ],
            [
                'slug' => 'from-brief-to-launch',
                'published_at' => '2026-02-22',
                'title_en' => 'From brief to launch: a process that delivers premium results',
                'title_ar' => 'من الموجز إلى الإطلاق: عملية تُنتج نتائج متميزة',
                'badge_en' => 'Process',
                'badge_ar' => 'عملية',
                'image_url' => $img1,
                'author_name' => 'Youssef Nader',
                'author_title_en' => 'Project Lead',
                'author_title_ar' => 'قائد المشروع',
                'author_avatar_url' => $img1,
                'lead_en' => 'From brief to launch: a process that delivers premium results — a Deepmarks perspective.',
                'lead_ar' => 'من الموجز إلى الإطلاق — منظور ديبماركس.',
                'content_en' => [
                    'A great outcome is the result of a great process. Clear inputs reduce ambiguity and speed up high-quality decisions.',
                    'We collaborate in steps — strategy first, then design execution, then systemization — so each stage improves the next.',
                    "That's how premium work stays consistent and repeatable.",
                ],
                'content_ar' => [
                    'النتيجة العظيمة نتيجة عملية عظيمة.',
                    'نتعاون على مراحل — الاستراتيجية أولاً ثم التنفيذ ثم التنظيم.',
                    'هكذا يبقى العمل المتميز متسقاً وقابلاً للتكرار.',
                ],
                'sort_order' => 5,
            ],
            [
                'slug' => 'market-authority',
                'published_at' => '2026-01-10',
                'title_en' => 'Earning market authority through consistent brand execution',
                'title_ar' => 'اكتساب سلطة السوق عبر تنفيذ علامة متسق',
                'badge_en' => 'Execution',
                'badge_ar' => 'تنفيذ',
                'image_url' => $img2,
                'author_name' => 'Sara Khalil',
                'author_title_en' => 'Execution Partner',
                'author_title_ar' => 'شريكة التنفيذ',
                'author_avatar_url' => $img2,
                'lead_en' => 'Earning market authority through consistent brand execution — a Deepmarks perspective.',
                'lead_ar' => 'اكتساب سلطة السوق — منظور ديبماركس.',
                'content_en' => [
                    'Market authority is built through consistency. People remember patterns — and trust forms when those patterns hold up over time.',
                    'Identity systems make that possible: you can ship new content without breaking the brand.',
                    'Deepmarks helps teams execute with clarity, so the brand voice stays unmistakable.',
                ],
                'content_ar' => [
                    'سلطة السوق تُبنى عبر الاتساق. يتذكر الناس الأنماط ويثقون عندما تصمد.',
                    'أنظمة الهوية تجعل ذلك ممكناً.',
                    'يساعد ديبماركس الفرق على التنفيذ بوضوح.',
                ],
                'sort_order' => 6,
            ],
        ];
        foreach ($blogs as $blog) {
            BlogPost::create(array_merge($blog, ['is_active' => true]));
        }

        Faq::query()->delete();
        $faqs = [
            [
                'question_en' => 'How Does Deepmarks Work?',
                'question_ar' => 'كيف يعمل ديبماركس؟',
                'answer_en' => "You submit your brand details through our form, we review your goals and match you with the right designer. You'll then collaborate on a brand identity tailored to your business — from concept to final deliverables.",
                'answer_ar' => 'تقدم تفاصيل علامتك عبر النموذج، نراجع أهدافك ونطابقك مع المصمم المناسب، ثم تتعاون على هوية مخصصة لعملك.',
                'sort_order' => 1,
            ],
            [
                'question_en' => 'What Branding Services Can I Request?',
                'question_ar' => 'ما خدمات العلامة التي يمكنني طلبها؟',
                'answer_en' => 'We offer logo design, brand strategy consulting, full visual identity systems, color palette creation, typography selection, business card design, social media templates, and website UI direction.',
                'answer_ar' => 'نقدم تصميم الشعار واستشارات الاستراتيجية وأنظمة الهوية الكاملة ولوحات الألوان والخطوط وبطاقات العمل وقوالب التواصل واتجاه واجهة الموقع.',
                'sort_order' => 2,
            ],
            [
                'question_en' => 'How Are Top Designers Chosen for My Project?',
                'question_ar' => 'كيف يتم اختيار أفضل المصممين لمشروعي؟',
                'answer_en' => 'Our team curates matches based on your brief, budget, and aesthetic direction. Each designer goes through a vetting process to ensure quality, style diversity, and strategic thinking.',
                'answer_ar' => 'ينتقي فريقنا المطابقات بناءً على موجزك وميزانيتك واتجاهك الجمالي. يمر كل مصمم بعملية تحقق لضمان الجودة.',
                'sort_order' => 3,
            ],
            [
                'question_en' => 'What Happens After I Submit My Brief?',
                'question_ar' => 'ماذا يحدث بعد تقديم الموجز؟',
                'answer_en' => "Our team reviews your submission within 1–2 business days and reaches out to align on vision, timeline, and package. You'll receive a clear proposal and a designer match before any work begins.",
                'answer_ar' => 'يراجع فريقنا طلبك خلال يوم إلى يومين عمل ويتواصل لمواءمة الرؤية والجدول والباقة.',
                'sort_order' => 4,
            ],
            [
                'question_en' => 'Is Deepmarks a Marketplace or Full-Service Agency?',
                'question_ar' => 'هل ديبماركس سوق أم وكالة متكاملة؟',
                'answer_en' => 'Deepmarks is a full-service branding agency. We manage the entire process — from brand strategy to final file delivery — giving you a curated, high-touch experience rather than an open marketplace.',
                'answer_ar' => 'ديبماركس وكالة علامة متكاملة. ندير العملية بالكامل من الاستراتيجية إلى تسليم الملفات.',
                'sort_order' => 5,
            ],
            [
                'question_en' => 'Do I Work Directly With the Designer?',
                'question_ar' => 'هل أعمل مباشرة مع المصمم؟',
                'answer_en' => 'Yes. Once matched, you communicate directly with your assigned designer — sharing feedback, approving concepts, and guiding the direction. Our team stays available to support at every stage.',
                'answer_ar' => 'نعم. بمجرد المطابقة تتواصل مباشرة مع مصممك المعين. يبقى فريقنا متاحاً للدعم في كل مرحلة.',
                'sort_order' => 6,
            ],
        ];
        foreach ($faqs as $faq) {
            Faq::create(array_merge($faq, ['is_active' => true]));
        }

        PricingPackage::query()->delete();
        $packages = [
            [
                'slug' => 'basic',
                'name_en' => 'Basic',
                'name_ar' => 'أساسي',
                'price_display' => '499',
                'currency_symbol' => '$',
                'description_en' => 'For early-stage founders validating their brand — everything you need to start strong.',
                'description_ar' => 'للمؤسسين في المراحل المبكرة — كل ما تحتاجه للبداية القوية.',
                'features_en' => [
                    '1 Project',
                    'Brand name refinement (if needed)',
                    'Custom Logo Design',
                    '2 revision rounds',
                    'Color palette',
                    'Logo files (PNG, SVG, PDF)',
                ],
                'features_ar' => [
                    'مشروع واحد',
                    'تحسين اسم العلامة (إن لزم)',
                    'تصميم شعار مخصص',
                    'جولتان للمراجعات',
                    'لوحة ألوان',
                    'ملفات الشعار (PNG, SVG, PDF)',
                ],
                'is_recommended' => false,
                'cta_label_en' => 'Start Now',
                'cta_label_ar' => 'ابدأ الآن',
                'cta_url' => '/contact',
                'sort_order' => 1,
            ],
            [
                'slug' => 'growth',
                'name_en' => 'Growth',
                'name_ar' => 'نمو',
                'price_display' => '1,200',
                'currency_symbol' => '$',
                'description_en' => 'For growing businesses looking to appear more established and cohesive across platforms.',
                'description_ar' => 'للأعمال النامية التي تريد حضوراً أكثر تماسكاً عبر المنصات.',
                'features_en' => [
                    '250+ Revisions',
                    'Everything in Basic',
                    '3 Logo concepts',
                    '4 revision rounds',
                    'Business Card Design',
                    'Social Media Post Templates',
                ],
                'features_ar' => [
                    'أكثر من 250 مراجعة',
                    'كل ما في الأساسي',
                    '3 مفاهيم شعار',
                    '4 جولات مراجعة',
                    'تصميم بطاقة عمل',
                    'قوالب منشورات التواصل',
                ],
                'badge_en' => 'Recommended',
                'badge_ar' => 'موصى به',
                'is_recommended' => true,
                'cta_label_en' => 'Start Now',
                'cta_label_ar' => 'ابدأ الآن',
                'cta_url' => '/contact',
                'sort_order' => 2,
            ],
            [
                'slug' => 'premium',
                'name_en' => 'Premium',
                'name_ar' => 'متميز',
                'price_display' => '2,500',
                'currency_symbol' => '$',
                'description_en' => 'For established businesses, agencies, and brands preparing for scaling or premium positioning.',
                'description_ar' => 'للأعمال الراسخة والوكالات والعلامات المستعدة للتوسع أو التموضع المتميز.',
                'features_en' => [
                    '250+ Revisions',
                    'Everything in Growth',
                    'Advanced Brand Strategy Direction',
                    'Unlimited Revisions',
                    'Full Brand Identity System',
                    'Website UI Direction / Visual Assets',
                ],
                'features_ar' => [
                    'أكثر من 250 مراجعة',
                    'كل ما في النمو',
                    'توجيه استراتيجية علامة متقدم',
                    'مراجعات غير محدودة',
                    'نظام هوية كامل',
                    'اتجاه واجهة الموقع / أصول بصرية',
                ],
                'is_recommended' => false,
                'cta_label_en' => 'Start Now',
                'cta_label_ar' => 'ابدأ الآن',
                'cta_url' => '/contact',
                'sort_order' => 3,
            ],
        ];
        foreach ($packages as $package) {
            PricingPackage::create(array_merge($package, ['is_active' => true]));
        }
    }
}
