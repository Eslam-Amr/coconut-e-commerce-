<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaudiArabiaSeeder extends Seeder
{
    public function run(): void
    {
        // Create Saudi Arabia country
        $countryId = DB::table('countries')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert country translations
        DB::table('country_translations')->insert([
            [
                'country_id' => $countryId,
                'locale' => 'en',
                'name' => 'Saudi Arabia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'country_id' => $countryId,
                'locale' => 'ar',
                'name' => 'المملكة العربية السعودية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Cities with their districts
        $citiesWithDistricts = [
            'Riyadh' => [
                'ar' => 'الرياض',
                'districts' => [
                    ['en' => 'Al Olaya', 'ar' => 'العليا'],
                    ['en' => 'Al Malaz', 'ar' => 'الملز'],
                    ['en' => 'Al Sulaimaniyah', 'ar' => 'السليمانية'],
                    ['en' => 'Al Murabba', 'ar' => 'المربع'],
                    ['en' => 'Al Naseem', 'ar' => 'النسيم'],
                    ['en' => 'Al Nakheel', 'ar' => 'النخيل'],
                    ['en' => 'Al Sahafah', 'ar' => 'الصحافة'],
                    ['en' => 'Granada', 'ar' => 'غرناطة'],
                    ['en' => 'Al Hamra', 'ar' => 'الحمراء'],
                    ['en' => 'Al Izdihar', 'ar' => 'الازدهار'],
                    ['en' => 'Al Mohammadiyah', 'ar' => 'المحمدية'],
                    ['en' => 'Al Yasmin', 'ar' => 'الياسمين'],
                    ['en' => 'King Fahd', 'ar' => 'الملك فهد'],
                    ['en' => 'Al Muruj', 'ar' => 'المروج'],
                    ['en' => 'Al Narjis', 'ar' => 'النرجس'],
                    ['en' => 'Al Aqiq', 'ar' => 'العقيق'],
                    ['en' => 'Al Rabwah', 'ar' => 'الربوة'],
                    ['en' => 'Al Rawdah', 'ar' => 'الروضة'],
                    ['en' => 'Al Wadi', 'ar' => 'الوادي'],
                    ['en' => 'Hittin', 'ar' => 'حطين'],
                ]
            ],
            'Jeddah' => [
                'ar' => 'جدة',
                'districts' => [
                    ['en' => 'Al Balad', 'ar' => 'البلد'],
                    ['en' => 'Al Salamah', 'ar' => 'السلامة'],
                    ['en' => 'Al Zahra', 'ar' => 'الزهراء'],
                    ['en' => 'Al Rawdah', 'ar' => 'الروضة'],
                    ['en' => 'Al Andalus', 'ar' => 'الأندلس'],
                    ['en' => 'Al Hamra', 'ar' => 'الحمراء'],
                    ['en' => 'Al Sharafiyah', 'ar' => 'الشرفية'],
                    ['en' => 'Al Naeem', 'ar' => 'النعيم'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Obhur', 'ar' => 'أبحر'],
                    ['en' => 'Al Basateen', 'ar' => 'البساتين'],
                    ['en' => 'Al Nuzlah Al Yamaniyah', 'ar' => 'النزلة اليمانية'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Baghdadiyah', 'ar' => 'البغدادية'],
                    ['en' => 'Al Murjan', 'ar' => 'المرجان'],
                    ['en' => 'Al Shate', 'ar' => 'الشاطئ'],
                    ['en' => 'Tahliyah', 'ar' => 'التحلية'],
                    ['en' => 'Ar Ruwais', 'ar' => 'الرويس'],
                ]
            ],
            'Mecca' => [
                'ar' => 'مكة المكرمة',
                'districts' => [
                    ['en' => 'Al Haram', 'ar' => 'الحرم'],
                    ['en' => 'Ajyad', 'ar' => 'أجياد'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                    ['en' => 'Al Shubaikah', 'ar' => 'الشبيكة'],
                    ['en' => 'Al Hindawiyah', 'ar' => 'الهنداوية'],
                    ['en' => 'Al Misfalah', 'ar' => 'المسفلة'],
                    ['en' => 'Jabal Omar', 'ar' => 'جبل عمر'],
                    ['en' => 'Al Shawqiyah', 'ar' => 'الشوقية'],
                    ['en' => 'Al Zahir', 'ar' => 'الظاهر'],
                    ['en' => 'Al Utaibiyah', 'ar' => 'العتيبية'],
                    ['en' => 'Al Awali', 'ar' => 'العوالي'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                ]
            ],
            'Medina' => [
                'ar' => 'المدينة المنورة',
                'districts' => [
                    ['en' => 'Al Haram', 'ar' => 'الحرم'],
                    ['en' => 'Quba', 'ar' => 'قباء'],
                    ['en' => 'Al Aqiq', 'ar' => 'العقيق'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Iskan', 'ar' => 'الإسكان'],
                    ['en' => 'Al Shurfa', 'ar' => 'الشرفة'],
                    ['en' => 'Uhud', 'ar' => 'أحد'],
                    ['en' => 'Al Jumuah', 'ar' => 'الجمعة'],
                    ['en' => 'Sultana', 'ar' => 'سلطانة'],
                ]
            ],
            'Dammam' => [
                'ar' => 'الدمام',
                'districts' => [
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Shati', 'ar' => 'الشاطئ'],
                    ['en' => 'Al Mazruiyah', 'ar' => 'المزروعية'],
                    ['en' => 'Al Adamah', 'ar' => 'الأدامة'],
                    ['en' => 'Al Noor', 'ar' => 'النور'],
                    ['en' => 'Al Muhammadiyah', 'ar' => 'المحمدية'],
                    ['en' => 'Al Ferdous', 'ar' => 'الفردوس'],
                    ['en' => 'Al Nakheel', 'ar' => 'النخيل'],
                    ['en' => 'Al Anoud', 'ar' => 'العنود'],
                    ['en' => 'Al Rakah', 'ar' => 'الراكة'],
                ]
            ],
            'Khobar' => [
                'ar' => 'الخبر',
                'districts' => [
                    ['en' => 'Al Corniche', 'ar' => 'الكورنيش'],
                    ['en' => 'Al Aqrabiyah', 'ar' => 'العقربية'],
                    ['en' => 'Al Ulaya', 'ar' => 'العليا'],
                    ['en' => 'Al Thuqbah', 'ar' => 'الثقبة'],
                    ['en' => 'Al Rakah', 'ar' => 'الراكة'],
                    ['en' => 'Al Hizam Al Akhdar', 'ar' => 'الحزام الأخضر'],
                    ['en' => 'Al Yarmouk', 'ar' => 'اليرموك'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                ]
            ],
            'Dhahran' => [
                'ar' => 'الظهران',
                'districts' => [
                    ['en' => 'Al Doha', 'ar' => 'الدوحة'],
                    ['en' => 'Al Khodariyah', 'ar' => 'الخضرية'],
                    ['en' => 'Aramco Compound', 'ar' => 'مجمع أرامكو'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                ]
            ],
            'Taif' => [
                'ar' => 'الطائف',
                'districts' => [
                    ['en' => 'Al Shafa', 'ar' => 'الشفا'],
                    ['en' => 'Al Hada', 'ar' => 'الهدا'],
                    ['en' => 'Al Salamah', 'ar' => 'السلامة'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                    ['en' => 'Al Qim', 'ar' => 'القيم'],
                    ['en' => 'Shubra', 'ar' => 'شبرا'],
                ]
            ],
            'Tabuk' => [
                'ar' => 'تبوك',
                'districts' => [
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                    ['en' => 'Al Muruj', 'ar' => 'المروج'],
                    ['en' => 'Sultana', 'ar' => 'سلطانة'],
                ]
            ],
            'Buraidah' => [
                'ar' => 'بريدة',
                'districts' => [
                    ['en' => 'Al Iskan', 'ar' => 'الإسكان'],
                    ['en' => 'Al Safra', 'ar' => 'الصفراء'],
                    ['en' => 'Al Salamah', 'ar' => 'السلامة'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Safwa', 'ar' => 'الصفوة'],
                ]
            ],
            'Khamis Mushait' => [
                'ar' => 'خميس مشيط',
                'districts' => [
                    ['en' => 'Al Khaldiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Mohamadiyah', 'ar' => 'المحمدية'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                    ['en' => 'Al Samer', 'ar' => 'السامر'],
                ]
            ],
            'Hofuf' => [
                'ar' => 'الهفوف',
                'districts' => [
                    ['en' => 'Al Mubarraz', 'ar' => 'المبرز'],
                    ['en' => 'Al Kut', 'ar' => 'الكوت'],
                    ['en' => 'Al Thuqbah', 'ar' => 'الثقبة'],
                    ['en' => 'Al Faiha', 'ar' => 'الفيحاء'],
                ]
            ],
            'Mubarraz' => [
                'ar' => 'المبرز',
                'districts' => [
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Aziziyah', 'ar' => 'العزيزية'],
                ]
            ],
            'Hafar Al-Batin' => [
                'ar' => 'حفر الباطن',
                'districts' => [
                    ['en' => 'Al Noor', 'ar' => 'النور'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Mustawdah', 'ar' => 'المستودع'],
                ]
            ],
            'Jubail' => [
                'ar' => 'الجبيل',
                'districts' => [
                    ['en' => 'Fanateer', 'ar' => 'الفناتير'],
                    ['en' => 'Al Dana', 'ar' => 'الدانة'],
                    ['en' => 'Al Deffi', 'ar' => 'الدفي'],
                    ['en' => 'Industrial City', 'ar' => 'المدينة الصناعية'],
                ]
            ],
            'Abha' => [
                'ar' => 'أبها',
                'districts' => [
                    ['en' => 'Al Manhal', 'ar' => 'المنهل'],
                    ['en' => 'Al Qabil', 'ar' => 'القابل'],
                    ['en' => 'Al Zahra', 'ar' => 'الزهراء'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Al Mohandeseen', 'ar' => 'المهندسين'],
                ]
            ],
            'Yanbu' => [
                'ar' => 'ينبع',
                'districts' => [
                    ['en' => 'Al Bahar', 'ar' => 'البحر'],
                    ['en' => 'Al Safa', 'ar' => 'الصفا'],
                    ['en' => 'Al Nakheel', 'ar' => 'النخيل'],
                    ['en' => 'Industrial City', 'ar' => 'المدينة الصناعية'],
                ]
            ],
            'Najran' => [
                'ar' => 'نجران',
                'districts' => [
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Qabil', 'ar' => 'القابل'],
                    ['en' => 'Aba Al Saud', 'ar' => 'أبا السعود'],
                ]
            ],
            'Al Qatif' => [
                'ar' => 'القطيف',
                'districts' => [
                    ['en' => 'Darin', 'ar' => 'دارين'],
                    ['en' => 'Safwa', 'ar' => 'صفوى'],
                    ['en' => 'Tarout', 'ar' => 'تاروت'],
                    ['en' => 'Sayhat', 'ar' => 'سيهات'],
                ]
            ],
            'Arar' => [
                'ar' => 'عرعر',
                'districts' => [
                    ['en' => 'Al Yamama', 'ar' => 'اليمامة'],
                    ['en' => 'Al Faisaliyah', 'ar' => 'الفيصلية'],
                    ['en' => 'Al Naseem', 'ar' => 'النسيم'],
                ]
            ],
            'Sakakah' => [
                'ar' => 'سكاكا',
                'districts' => [
                    ['en' => 'Al Iskan', 'ar' => 'الإسكان'],
                    ['en' => 'Al Khalidiyah', 'ar' => 'الخالدية'],
                    ['en' => 'Dumat Al Jandal', 'ar' => 'دومة الجندل'],
                ]
            ],
            'Jizan' => [
                'ar' => 'جازان',
                'districts' => [
                    ['en' => 'Al Corniche', 'ar' => 'الكورنيش'],
                    ['en' => 'Al Safa', 'ar' => 'الصفا'],
                    ['en' => 'Al Rawdah', 'ar' => 'الروضة'],
                    ['en' => 'Al Shati', 'ar' => 'الشاطئ'],
                ]
            ],
            'Al Bahah' => [
                'ar' => 'الباحة',
                'districts' => [
                    ['en' => 'Al Aqiq', 'ar' => 'العقيق'],
                    ['en' => 'Al Mikhwah', 'ar' => 'المخواة'],
                    ['en' => 'Baljurashi', 'ar' => 'بلجرشي'],
                ]
            ],
            'Hail' => [
                'ar' => 'حائل',
                'districts' => [
                    ['en' => 'Al Yasmin', 'ar' => 'الياسمين'],
                    ['en' => 'Al Salam', 'ar' => 'السلام'],
                    ['en' => 'Al Shifa', 'ar' => 'الشفاء'],
                    ['en' => 'Barzan', 'ar' => 'برزان'],
                ]
            ],
        ];

        // Cities without districts
        $citiesWithoutDistricts = [
            ['en' => 'Unaizah', 'ar' => 'عنيزة'],
            ['en' => 'Al Kharj', 'ar' => 'الخرج'],
            ['en' => 'Al Qunfudhah', 'ar' => 'القنفذة'],
            ['en' => 'Rabigh', 'ar' => 'رابغ'],
            ['en' => 'Ras Tanura', 'ar' => 'رأس تنورة'],
            ['en' => 'Bisha', 'ar' => 'بيشة'],
            ['en' => 'Sabya', 'ar' => 'صبيا'],
            ['en' => 'Al Majmaah', 'ar' => 'المجمعة'],
            ['en' => 'Thuwal', 'ar' => 'ثول'],
            ['en' => 'Sharurah', 'ar' => 'شرورة'],
            ['en' => 'Al Wajh', 'ar' => 'الوجه'],
            ['en' => 'Dawadmi', 'ar' => 'الدوادمي'],
            ['en' => 'Qurayyat', 'ar' => 'القريات'],
            ['en' => 'Tarout', 'ar' => 'تاروت'],
            ['en' => 'Al Bukayriyah', 'ar' => 'البكيرية'],
            ['en' => 'Safwa', 'ar' => 'صفوى'],
            ['en' => 'Khafji', 'ar' => 'الخفجي'],
            ['en' => 'Sayhat', 'ar' => 'سيهات'],
            ['en' => 'Al Lith', 'ar' => 'الليث'],
            ['en' => 'Ahad Rafidah', 'ar' => 'أحد رفيدة'],
            ['en' => 'Duba', 'ar' => 'ضباء'],
            ['en' => 'Al Zulfi', 'ar' => 'الزلفي'],
            ['en' => 'Rass', 'ar' => 'الرس'],
            ['en' => 'Afif', 'ar' => 'عفيف'],
            ['en' => 'Marat', 'ar' => 'مرات'],
            ['en' => 'Samtah', 'ar' => 'صامطة'],
            ['en' => 'Al Qurayn', 'ar' => 'القرين'],
            ['en' => 'Abu Arish', 'ar' => 'أبو عريش'],
            ['en' => 'Dhurma', 'ar' => 'ضرماء'],
            ['en' => 'Shaqra', 'ar' => 'شقراء'],
            ['en' => 'Thadiq', 'ar' => 'ثادق'],
            ['en' => 'Tabarjal', 'ar' => 'طبرجل'],
            ['en' => 'Turayf', 'ar' => 'طريف'],
            ['en' => 'Al Artawiyah', 'ar' => 'الأرطاوية'],
            ['en' => 'Rafha', 'ar' => 'رفحاء'],
            ['en' => 'Riyadh Al Khabra', 'ar' => 'رياض الخبراء'],
            ['en' => 'Al Ghat', 'ar' => 'الغاط'],
            ['en' => 'Haql', 'ar' => 'حقل'],
            ['en' => 'Al Uyun', 'ar' => 'العيون'],
            ['en' => 'Farasan', 'ar' => 'فرسان'],
            ['en' => 'Al Namas', 'ar' => 'النماص'],
            ['en' => 'Muhayil', 'ar' => 'محايل'],
            ['en' => 'Tanumah', 'ar' => 'تنومة'],
            ['en' => 'Al Quwayiyah', 'ar' => 'القويعية'],
            ['en' => 'Al Aflaj', 'ar' => 'الأفلاج'],
            ['en' => 'Al Jumum', 'ar' => 'الجموم'],
            ['en' => 'Badr', 'ar' => 'بدر'],
            ['en' => 'Khaybar', 'ar' => 'خيبر'],
            ['en' => 'Al Ula', 'ar' => 'العلا'],
            ['en' => 'Umluj', 'ar' => 'أملج'],
            ['en' => 'Mahd adh Dhahab', 'ar' => 'مهد الذهب'],
        ];

        // Insert cities with districts
        foreach ($citiesWithDistricts as $cityName => $cityData) {
            // Insert city
            $cityId = DB::table('cities')->insertGetId([
                'country_id' => $countryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert city translations
            DB::table('city_translations')->insert([
                [
                    'city_id' => $cityId,
                    'locale' => 'en',
                    'name' => $cityName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'city_id' => $cityId,
                    'locale' => 'ar',
                    'name' => $cityData['ar'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Insert districts for this city
            foreach ($cityData['districts'] as $district) {
                $districtId = DB::table('districts')->insertGetId([
                    'city_id' => $cityId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert district translations
                DB::table('district_translations')->insert([
                    [
                        'district_id' => $districtId,
                        'locale' => 'en',
                        'name' => $district['en'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'district_id' => $districtId,
                        'locale' => 'ar',
                        'name' => $district['ar'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        }

        // Insert cities without districts
        foreach ($citiesWithoutDistricts as $city) {
            $cityId = DB::table('cities')->insertGetId([
                'country_id' => $countryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('city_translations')->insert([
                [
                    'city_id' => $cityId,
                    'locale' => 'en',
                    'name' => $city['en'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'city_id' => $cityId,
                    'locale' => 'ar',
                    'name' => $city['ar'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}