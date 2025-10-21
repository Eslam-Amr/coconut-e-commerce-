<?php

namespace Database\Seeders;

use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // About Us page
        StaticPage::create([
            'ar' => [
                'title' => 'من نحن',
                'content' => 'نحن متجر إلكتروني رائد في المنطقة، نقدم أفضل المنتجات بأعلى جودة وأسعار تنافسية. نسعى لتوفير تجربة تسوق متميزة لعملائنا الكرام.'
            ],
            'en' => [
                'title' => 'About Us',
                'content' => 'We are a leading e-commerce store in the region, offering the best products with the highest quality and competitive prices. We strive to provide an exceptional shopping experience for our valued customers.'
            ]
        ]);

        // Privacy Policy page
        StaticPage::create([
            'ar' => [
                'title' => 'سياسة الخصوصية',
                'content' => 'نحن نحترم خصوصيتك ونلتزم بحماية معلوماتك الشخصية. هذه السياسة توضح كيفية جمع واستخدام وحماية معلوماتك عند استخدام موقعنا.'
            ],
            'en' => [
                'title' => 'Privacy Policy',
                'content' => 'We respect your privacy and are committed to protecting your personal information. This policy explains how we collect, use, and protect your information when using our website.'
            ]
        ]);

        // Terms of Service page
        StaticPage::create([
            'ar' => [
                'title' => 'شروط الخدمة',
                'content' => 'هذه الشروط والأحكام تحكم استخدامك لموقعنا وخدماتنا. باستخدام موقعنا، فإنك توافق على الالتزام بهذه الشروط.'
            ],
            'en' => [
                'title' => 'Terms of Service',
                'content' => 'These terms and conditions govern your use of our website and services. By using our website, you agree to be bound by these terms.'
            ]
        ]);

        // Shipping Information page
        StaticPage::create([
            'ar' => [
                'title' => 'معلومات الشحن',
                'content' => 'نوفر خدمة شحن سريعة وآمنة لجميع أنحاء المملكة. تتراوح مدة التسليم من 1-3 أيام عمل داخل المدن الرئيسية و3-7 أيام للمناطق الأخرى.'
            ],
            'en' => [
                'title' => 'Shipping Information',
                'content' => 'We provide fast and secure shipping service throughout the Kingdom. Delivery time ranges from 1-3 business days within major cities and 3-7 days for other areas.'
            ]
        ]);

        // Return Policy page
        StaticPage::create([
            'ar' => [
                'title' => 'سياسة الإرجاع',
                'content' => 'يمكنك إرجاع المنتجات خلال 14 يوم من تاريخ الاستلام. يجب أن تكون المنتجات في حالتها الأصلية مع الفاتورة. نقدم استرداد كامل أو استبدال.'
            ],
            'en' => [
                'title' => 'Return Policy',
                'content' => 'You can return products within 14 days of receipt. Products must be in their original condition with the invoice. We offer full refund or replacement.'
            ]
        ]);

        // Contact Us page
        StaticPage::create([
            'ar' => [
                'title' => 'اتصل بنا',
                'content' => 'للاستفسارات والدعم الفني، يمكنكم التواصل معنا عبر: الهاتف: +966123456789 البريد الإلكتروني: info@store.com أو عبر نموذج التواصل على الموقع.'
            ],
            'en' => [
                'title' => 'Contact Us',
                'content' => 'For inquiries and technical support, you can contact us via: Phone: +966123456789 Email: info@store.com or through the contact form on the website.'
            ]
        ]);

        // FAQ page
        StaticPage::create([
            'ar' => [
                'title' => 'الأسئلة الشائعة',
                'content' => 'س: كيف يمكنني إلغاء طلب؟ ج: يمكنك إلغاء الطلب خلال ساعة من إتمامه عبر حسابك أو بالتواصل معنا. س: ما هي طرق الدفع المتاحة؟ ج: نقبل الدفع نقداً عند الاستلام، البطاقات الائتمانية، والتحويل البنكي.'
            ],
            'en' => [
                'title' => 'Frequently Asked Questions',
                'content' => 'Q: How can I cancel an order? A: You can cancel the order within one hour of completion through your account or by contacting us. Q: What payment methods are available? A: We accept cash on delivery, credit cards, and bank transfers.'
            ]
        ]);

        // Size Guide page
        StaticPage::create([
            'ar' => [
                'title' => 'دليل المقاسات',
                'content' => 'لضمان الحصول على المقاس المناسب، يرجى مراجعة جدول المقاسات أدناه. يمكنك قياس نفسك باستخدام شريط القياس ومراجعة الجدول للحصول على المقاس المناسب.'
            ],
            'en' => [
                'title' => 'Size Guide',
                'content' => 'To ensure you get the right size, please refer to the size chart below. You can measure yourself using a measuring tape and check the chart to get the appropriate size.'
            ]
        ]);

        // Warranty Information page
        StaticPage::create([
            'ar' => [
                'title' => 'معلومات الضمان',
                'content' => 'جميع منتجاتنا تأتي مع ضمان من المصنع. مدة الضمان تختلف حسب نوع المنتج. للحصول على خدمة الضمان، يرجى الاحتفاظ بالفاتورة وإيصال الشراء.'
            ],
            'en' => [
                'title' => 'Warranty Information',
                'content' => 'All our products come with manufacturer warranty. Warranty period varies by product type. For warranty service, please keep your invoice and purchase receipt.'
            ]
        ]);

        // Careers page
        StaticPage::create([
            'ar' => [
                'title' => 'الوظائف',
                'content' => 'نحن نبحث عن مواهب متميزة للانضمام إلى فريقنا. إذا كنت مهتماً بالعمل معنا، يرجى إرسال سيرتك الذاتية إلى hr@store.com'
            ],
            'en' => [
                'title' => 'Careers',
                'content' => 'We are looking for exceptional talents to join our team. If you are interested in working with us, please send your CV to hr@store.com'
            ]
        ]);

        $this->command->info('Static pages seeder completed successfully!');
        $this->command->info('Created ' . StaticPage::count() . ' static pages');
    }
}
