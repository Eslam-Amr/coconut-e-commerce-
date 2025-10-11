<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttributeValueSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $attributeValues = [
            // Color (attribute_id = 1)
            [
                'attribute_id' => 1,
                'values' => [
                    ['en' => 'Red', 'ar' => 'أحمر', 'es' => 'Rojo'],
                    ['en' => 'Blue', 'ar' => 'أزرق', 'es' => 'Azul'],
                    ['en' => 'Green', 'ar' => 'أخضر', 'es' => 'Verde'],
                    ['en' => 'Black', 'ar' => 'أسود', 'es' => 'Negro'],
                    ['en' => 'White', 'ar' => 'أبيض', 'es' => 'Blanco'],
                    ['en' => 'Yellow', 'ar' => 'أصفر', 'es' => 'Amarillo'],
                    ['en' => 'Orange', 'ar' => 'برتقالي', 'es' => 'Naranja'],
                    ['en' => 'Purple', 'ar' => 'بنفسجي', 'es' => 'Púrpura'],
                    ['en' => 'Pink', 'ar' => 'وردي', 'es' => 'Rosa'],
                    ['en' => 'Brown', 'ar' => 'بني', 'es' => 'Marrón'],
                    ['en' => 'Gray', 'ar' => 'رمادي', 'es' => 'Gris'],
                    ['en' => 'Navy', 'ar' => 'كحلي', 'es' => 'Azul Marino'],
                    ['en' => 'Beige', 'ar' => 'بيج', 'es' => 'Beige'],
                    ['en' => 'Maroon', 'ar' => 'عنابي', 'es' => 'Granate'],
                    ['en' => 'Gold', 'ar' => 'ذهبي', 'es' => 'Dorado'],
                    ['en' => 'Silver', 'ar' => 'فضي', 'es' => 'Plateado'],
                ],
            ],
            // Size (attribute_id = 2)
            [
                'attribute_id' => 2,
                'values' => [
                    ['en' => 'XS', 'ar' => 'صغير جداً', 'es' => 'XS'],
                    ['en' => 'S', 'ar' => 'صغير', 'es' => 'S'],
                    ['en' => 'M', 'ar' => 'وسط', 'es' => 'M'],
                    ['en' => 'L', 'ar' => 'كبير', 'es' => 'L'],
                    ['en' => 'XL', 'ar' => 'كبير جداً', 'es' => 'XL'],
                    ['en' => 'XXL', 'ar' => 'كبير جداً جداً', 'es' => 'XXL'],
                    ['en' => 'XXXL', 'ar' => 'كبير جداً جداً جداً', 'es' => 'XXXL'],
                ],
            ],
            // Numeric Size (attribute_id = 3)
            [
                'attribute_id' => 3,
                'values' => [
                    ['en' => '28', 'ar' => '28', 'es' => '28'],
                    ['en' => '30', 'ar' => '30', 'es' => '30'],
                    ['en' => '32', 'ar' => '32', 'es' => '32'],
                    ['en' => '34', 'ar' => '34', 'es' => '34'],
                    ['en' => '36', 'ar' => '36', 'es' => '36'],
                    ['en' => '38', 'ar' => '38', 'es' => '38'],
                    ['en' => '40', 'ar' => '40', 'es' => '40'],
                    ['en' => '42', 'ar' => '42', 'es' => '42'],
                    ['en' => '44', 'ar' => '44', 'es' => '44'],
                    ['en' => '46', 'ar' => '46', 'es' => '46'],
                    ['en' => '48', 'ar' => '48', 'es' => '48'],
                    ['en' => '50', 'ar' => '50', 'es' => '50'],
                ],
            ],
            // Storage (attribute_id = 4)
            [
                'attribute_id' => 4,
                'values' => [
                    ['en' => '16GB', 'ar' => '16 جيجا', 'es' => '16GB'],
                    ['en' => '32GB', 'ar' => '32 جيجا', 'es' => '32GB'],
                    ['en' => '64GB', 'ar' => '64 جيجا', 'es' => '64GB'],
                    ['en' => '128GB', 'ar' => '128 جيجا', 'es' => '128GB'],
                    ['en' => '256GB', 'ar' => '256 جيجا', 'es' => '256GB'],
                    ['en' => '512GB', 'ar' => '512 جيجا', 'es' => '512GB'],
                    ['en' => '1TB', 'ar' => '1 تيرا', 'es' => '1TB'],
                    ['en' => '2TB', 'ar' => '2 تيرا', 'es' => '2TB'],
                ],
            ],
            // RAM (attribute_id = 5)
            [
                'attribute_id' => 5,
                'values' => [
                    ['en' => '2GB', 'ar' => '2 جيجا', 'es' => '2GB'],
                    ['en' => '4GB', 'ar' => '4 جيجا', 'es' => '4GB'],
                    ['en' => '6GB', 'ar' => '6 جيجا', 'es' => '6GB'],
                    ['en' => '8GB', 'ar' => '8 جيجا', 'es' => '8GB'],
                    ['en' => '12GB', 'ar' => '12 جيجا', 'es' => '12GB'],
                    ['en' => '16GB', 'ar' => '16 جيجا', 'es' => '16GB'],
                    ['en' => '32GB', 'ar' => '32 جيجا', 'es' => '32GB'],
                ],
            ],
            // Material (attribute_id = 6)
            [
                'attribute_id' => 6,
                'values' => [
                    ['en' => 'Cotton', 'ar' => 'قطن', 'es' => 'Algodón'],
                    ['en' => 'Polyester', 'ar' => 'بوليستر', 'es' => 'Poliéster'],
                    ['en' => 'Leather', 'ar' => 'جلد', 'es' => 'Cuero'],
                    ['en' => 'Wool', 'ar' => 'صوف', 'es' => 'Lana'],
                    ['en' => 'Silk', 'ar' => 'حرير', 'es' => 'Seda'],
                    ['en' => 'Denim', 'ar' => 'دينم', 'es' => 'Mezclilla'],
                    ['en' => 'Linen', 'ar' => 'كتان', 'es' => 'Lino'],
                    ['en' => 'Synthetic', 'ar' => 'صناعي', 'es' => 'Sintético'],
                    ['en' => 'Plastic', 'ar' => 'بلاستيك', 'es' => 'Plástico'],
                    ['en' => 'Metal', 'ar' => 'معدن', 'es' => 'Metal'],
                    ['en' => 'Wood', 'ar' => 'خشب', 'es' => 'Madera'],
                    ['en' => 'Glass', 'ar' => 'زجاج', 'es' => 'Vidrio'],
                ],
            ],
            // Weight (attribute_id = 7)
            [
                'attribute_id' => 7,
                'values' => [
                    ['en' => '100g', 'ar' => '100 جرام', 'es' => '100g'],
                    ['en' => '200g', 'ar' => '200 جرام', 'es' => '200g'],
                    ['en' => '500g', 'ar' => '500 جرام', 'es' => '500g'],
                    ['en' => '1kg', 'ar' => '1 كيلو', 'es' => '1kg'],
                    ['en' => '2kg', 'ar' => '2 كيلو', 'es' => '2kg'],
                    ['en' => '5kg', 'ar' => '5 كيلو', 'es' => '5kg'],
                    ['en' => '10kg', 'ar' => '10 كيلو', 'es' => '10kg'],
                ],
            ],
            // Length (attribute_id = 8)
            [
                'attribute_id' => 8,
                'values' => [
                    ['en' => 'Short', 'ar' => 'قصير', 'es' => 'Corto'],
                    ['en' => 'Medium', 'ar' => 'متوسط', 'es' => 'Medio'],
                    ['en' => 'Long', 'ar' => 'طويل', 'es' => 'Largo'],
                    ['en' => 'Extra Long', 'ar' => 'طويل جداً', 'es' => 'Extra Largo'],
                ],
            ],
            // Shoe Size (US) (attribute_id = 9)
            [
                'attribute_id' => 9,
                'values' => [
                    ['en' => '5', 'ar' => '5', 'es' => '5'],
                    ['en' => '5.5', 'ar' => '5.5', 'es' => '5.5'],
                    ['en' => '6', 'ar' => '6', 'es' => '6'],
                    ['en' => '6.5', 'ar' => '6.5', 'es' => '6.5'],
                    ['en' => '7', 'ar' => '7', 'es' => '7'],
                    ['en' => '7.5', 'ar' => '7.5', 'es' => '7.5'],
                    ['en' => '8', 'ar' => '8', 'es' => '8'],
                    ['en' => '8.5', 'ar' => '8.5', 'es' => '8.5'],
                    ['en' => '9', 'ar' => '9', 'es' => '9'],
                    ['en' => '9.5', 'ar' => '9.5', 'es' => '9.5'],
                    ['en' => '10', 'ar' => '10', 'es' => '10'],
                    ['en' => '10.5', 'ar' => '10.5', 'es' => '10.5'],
                    ['en' => '11', 'ar' => '11', 'es' => '11'],
                    ['en' => '11.5', 'ar' => '11.5', 'es' => '11.5'],
                    ['en' => '12', 'ar' => '12', 'es' => '12'],
                    ['en' => '13', 'ar' => '13', 'es' => '13'],
                    ['en' => '14', 'ar' => '14', 'es' => '14'],
                ],
            ],
            // Screen Size (attribute_id = 10)
            [
                'attribute_id' => 10,
                'values' => [
                    ['en' => '5.5"', 'ar' => '5.5 بوصة', 'es' => '5.5"'],
                    ['en' => '6.1"', 'ar' => '6.1 بوصة', 'es' => '6.1"'],
                    ['en' => '6.5"', 'ar' => '6.5 بوصة', 'es' => '6.5"'],
                    ['en' => '6.7"', 'ar' => '6.7 بوصة', 'es' => '6.7"'],
                    ['en' => '13"', 'ar' => '13 بوصة', 'es' => '13"'],
                    ['en' => '14"', 'ar' => '14 بوصة', 'es' => '14"'],
                    ['en' => '15.6"', 'ar' => '15.6 بوصة', 'es' => '15.6"'],
                    ['en' => '17"', 'ar' => '17 بوصة', 'es' => '17"'],
                    ['en' => '24"', 'ar' => '24 بوصة', 'es' => '24"'],
                    ['en' => '27"', 'ar' => '27 بوصة', 'es' => '27"'],
                    ['en' => '32"', 'ar' => '32 بوصة', 'es' => '32"'],
                    ['en' => '43"', 'ar' => '43 بوصة', 'es' => '43"'],
                    ['en' => '55"', 'ar' => '55 بوصة', 'es' => '55"'],
                    ['en' => '65"', 'ar' => '65 بوصة', 'es' => '65"'],
                    ['en' => '75"', 'ar' => '75 بوصة', 'es' => '75"'],
                ],
            ],
        ];

        $valueId = 1;
        foreach ($attributeValues as $attributeValue) {
            foreach ($attributeValue['values'] as $valueTranslations) {
                // Insert attribute value
                DB::table('attribute_values')->insert([
                    'id' => $valueId,
                    'attribute_id' => $attributeValue['attribute_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Insert translations for each locale
                foreach ($valueTranslations as $locale => $value) {
                    DB::table('attribute_value_translations')->insert([
                        'attribute_value_id' => $valueId,
                        'locale' => $locale,
                        'value' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $valueId++;
            }
        }

        $this->command->info('Attribute values seeded successfully!');
    }
}