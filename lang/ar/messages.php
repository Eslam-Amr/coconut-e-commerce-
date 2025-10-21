<?php

return [
    // General messages
    'success' => 'تمت العملية بنجاح',
    'error' => 'حدث خطأ',
    'validation_failed' => 'فشل في التحقق من البيانات',
    'unauthorized' => 'غير مصرح بالوصول',
    'forbidden' => 'الوصول محظور',
    'not_found' => 'المورد غير موجود',
    'resource_not_found' => 'المورد المطلوب :resource غير موجود',
    'route_not_found' => 'المسار المطلوب غير موجود',
    'method_not_allowed' => 'الطريقة غير مسموحة',
    'server_error' => 'خطأ في الخادم',
    'database_error' => 'حدث خطأ في قاعدة البيانات',
    'database_connection_error' => 'خطأ في الاتصال بقاعدة البيانات',
    'file_not_found' => 'الملف غير موجود',
    'unauthenticated' => 'مطلوب تسجيل الدخول',
    'already_authenticated' => 'أنت مسجل الدخول بالفعل',
    'token_expired' => 'انتهت صلاحية الرمز المميز',
    'token_invalid' => 'الرمز المميز غير صالح',
    'rate_limit_exceeded' => 'تم تجاوز حد الطلبات',
    'maintenance_mode' => 'التطبيق في وضع الصيانة',

    // CRUD operations
    'created_successfully' => 'تم إنشاء المورد بنجاح',
    'updated_successfully' => 'تم تحديث المورد بنجاح',
    'deleted_successfully' => 'تم حذف المورد بنجاح',
    'retrieved_successfully' => 'تم استرجاع المورد بنجاح',
    'list_retrieved_successfully' => 'تم استرجاع الموارد بنجاح',

    // Authentication
    'login_successful' => 'تم تسجيل الدخول بنجاح',
    'logout_successful' => 'تم تسجيل الخروج بنجاح',
    'registration_successful' => 'تم التسجيل بنجاح',
    'password_reset_successful' => 'تم إعادة تعيين كلمة المرور بنجاح',
    'email_verification_sent' => 'تم إرسال رسالة التحقق من البريد الإلكتروني',
    'phone_verification_sent' => 'تم إرسال رسالة التحقق من الهاتف',
    'otp_verified' => 'تم التحقق من الرمز بنجاح',
    'otp_expired' => 'انتهت صلاحية الرمز',
    'otp_invalid' => 'الرمز غير صالح',
    'account_verified' => 'تم التحقق من الحساب بنجاح',
    'password_changed' => 'تم تغيير كلمة المرور بنجاح',
    'profile_updated' => 'تم تحديث الملف الشخصي بنجاح',

    // Validation messages
    'required' => 'هذا الحقل مطلوب',
    'email' => 'يرجى إدخال عنوان بريد إلكتروني صالح',
    'phone' => 'يرجى إدخال رقم هاتف صالح',
    'password' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
    'password_confirmation' => 'تأكيد كلمة المرور غير متطابق',
    'unique' => 'هذه القيمة مستخدمة بالفعل',
    'exists' => 'القيمة المحددة غير موجودة',
    'min' => 'هذا الحقل يجب أن يكون :min أحرف على الأقل',
    'max' => 'هذا الحقل لا يجب أن يتجاوز :max حرف',
    'numeric' => 'هذا الحقل يجب أن يكون رقماً',
    'integer' => 'هذا الحقل يجب أن يكون رقماً صحيحاً',
    'boolean' => 'هذا الحقل يجب أن يكون صحيح أو خطأ',
    'date' => 'هذا الحقل يجب أن يكون تاريخاً صالحاً',
    'image' => 'هذا الحقل يجب أن يكون صورة',
    'file' => 'هذا الحقل يجب أن يكون ملفاً',
    'mimes' => 'هذا الحقل يجب أن يكون ملف من نوع: :values',
    'size' => 'هذا الحقل يجب أن يكون :size كيلوبايت',
    'string' => 'هذا الحقل يجب أن يكون نصاً',
    'email_required' => 'البريد الإلكتروني مطلوب',

    // Custom validation messages
    'phone_or_email_required' => 'رقم الهاتف أو البريد الإلكتروني مطلوب',
    'password_required' => 'كلمة المرور مطلوبة',
    'phone_required' => 'رقم الهاتف مطلوب',
    
    // Service-specific messages
    'cart_empty' => 'السلة فارغة',
    'order_confirmation_failed' => 'فشل في تأكيد الطلب',
    'order_processing_failed' => 'فشل في معالجة الطلب',
    'order_confirmed' => 'تم تأكيد الطلب بنجاح',
    'retrieval_failed' => 'فشل في استرجاع البيانات',
    'creation_failed' => 'فشل في الإنشاء',
    'update_failed' => 'فشل في التحديث',
    'deletion_failed' => 'فشل في الحذف',
    'not_found_or_unauthorized' => 'غير موجود أو غير مخول',
    'no_cart_found' => 'لم يتم العثور على سلة',
    'variant_mismatch' => 'المتغير لا ينتمي للمنتج',
    'insufficient_stock' => 'المخزون غير كافي',
    'flash_sale_limit_exceeded' => 'تم تجاوز حد العرض السريع',
    'added_to_cart' => 'تم إضافة العنصر للسلة بنجاح',
    'cart_add_failed' => 'فشل في إضافة العنصر للسلة',
    'cannot_deactivate_self' => 'لا يمكنك إلغاء تفعيل حسابك الخاص',
    'phone_exists' => 'رقم الهاتف غير مسجل',
    'email_exists' => 'البريد الإلكتروني غير مسجل',
    'invalid_credentials' => 'بيانات الاعتماد غير صحيحة',
    'account_locked' => 'الحساب مقفل',
    'account_suspended' => 'الحساب معلق',
    'email_already_verified' => 'البريد الإلكتروني محقق بالفعل',
    'phone_already_verified' => 'الهاتف محقق بالفعل',

    // Business logic messages
    'cart_updated' => 'تم تحديث السلة بنجاح',
    'item_added_to_cart' => 'تم إضافة العنصر إلى السلة بنجاح',
    'item_removed_from_cart' => 'تم إزالة العنصر من السلة بنجاح',
    'cart_cleared' => 'تم مسح السلة بنجاح',
    'order_created' => 'تم إنشاء الطلب بنجاح',
    'order_updated' => 'تم تحديث الطلب بنجاح',
    'order_cancelled' => 'تم إلغاء الطلب بنجاح',
    'payment_successful' => 'تم الدفع بنجاح',
    'payment_failed' => 'فشل في الدفع',
    'refund_processed' => 'تم معالجة الاسترداد بنجاح',
    'wishlist_updated' => 'تم تحديث قائمة الأمنيات بنجاح',
    'review_submitted' => 'تم إرسال التقييم بنجاح',
    'rating_submitted' => 'تم إرسال التقييم بنجاح',

    // File upload messages
    'file_uploaded' => 'تم رفع الملف بنجاح',
    'file_deleted' => 'تم حذف الملف بنجاح',
    'file_too_large' => 'الملف كبير جداً',
    'invalid_file_type' => 'نوع الملف غير صالح',
    'upload_failed' => 'فشل في رفع الملف',

    // Notification messages
    'notification_sent' => 'تم إرسال الإشعار بنجاح',
    'notification_marked_read' => 'تم تمييز الإشعار كمقروء',
    'notifications_cleared' => 'تم مسح الإشعارات بنجاح',

    // Settings messages
    'settings_updated' => 'تم تحديث الإعدادات بنجاح',
    'language_changed' => 'تم تغيير اللغة بنجاح',
    'theme_changed' => 'تم تغيير المظهر بنجاح',
    'privacy_settings_updated' => 'تم تحديث إعدادات الخصوصية بنجاح',

    // Admin messages
    'admin_created' => 'تم إنشاء المدير بنجاح',
    'admin_updated' => 'تم تحديث المدير بنجاح',
    'admin_deleted' => 'تم حذف المدير بنجاح',
    'role_assigned' => 'تم تعيين الدور بنجاح',
    'permission_granted' => 'تم منح الصلاحية بنجاح',
    'permission_revoked' => 'تم سحب الصلاحية بنجاح',

    // Product messages
    'product_created' => 'تم إنشاء المنتج بنجاح',
    'product_updated' => 'تم تحديث المنتج بنجاح',
    'product_deleted' => 'تم حذف المنتج بنجاح',
    'product_published' => 'تم نشر المنتج بنجاح',
    'product_unpublished' => 'تم إلغاء نشر المنتج بنجاح',
    'variant_created' => 'تم إنشاء متغير المنتج بنجاح',
    'variant_updated' => 'تم تحديث متغير المنتج بنجاح',
    'variant_deleted' => 'تم حذف متغير المنتج بنجاح',

    // Category messages
    'category_created' => 'تم إنشاء الفئة بنجاح',
    'category_updated' => 'تم تحديث الفئة بنجاح',
    'category_deleted' => 'تم حذف الفئة بنجاح',

    // Brand messages
    'brand_created' => 'تم إنشاء العلامة التجارية بنجاح',
    'brand_updated' => 'تم تحديث العلامة التجارية بنجاح',
    'brand_deleted' => 'تم حذف العلامة التجارية بنجاح',

    // Banner messages
    'banner_created' => 'تم إنشاء البانر بنجاح',
    'banner_updated' => 'تم تحديث البانر بنجاح',
    'banner_deleted' => 'تم حذف البانر بنجاح',
    'banner_activated' => 'تم تفعيل البانر بنجاح',
    'banner_deactivated' => 'تم إلغاء تفعيل البانر بنجاح',

    // Slider messages
    'slider_created' => 'تم إنشاء السلايدر بنجاح',
    'slider_updated' => 'تم تحديث السلايدر بنجاح',
    'slider_deleted' => 'تم حذف السلايدر بنجاح',
    'slider_activated' => 'تم تفعيل السلايدر بنجاح',
    'slider_deactivated' => 'تم إلغاء تفعيل السلايدر بنجاح',

    // Static page messages
    'static_page_created' => 'تم إنشاء الصفحة الثابتة بنجاح',
    'static_page_updated' => 'تم تحديث الصفحة الثابتة بنجاح',
    'static_page_deleted' => 'تم حذف الصفحة الثابتة بنجاح',

    // Flash sale messages
    'flash_sale_created' => 'تم إنشاء العرض السريع بنجاح',
    'flash_sale_updated' => 'تم تحديث العرض السريع بنجاح',
    'flash_sale_deleted' => 'تم حذف العرض السريع بنجاح',
    'flash_sale_started' => 'تم بدء العرض السريع بنجاح',
    'flash_sale_ended' => 'تم إنهاء العرض السريع بنجاح',

    // Voucher messages
    'voucher_created' => 'تم إنشاء القسيمة بنجاح',
    'voucher_updated' => 'تم تحديث القسيمة بنجاح',
    'voucher_deleted' => 'تم حذف القسيمة بنجاح',
    'voucher_applied' => 'تم تطبيق القسيمة بنجاح',
    'voucher_removed' => 'تم إزالة القسيمة بنجاح',
    'voucher_expired' => 'انتهت صلاحية القسيمة',
    'voucher_invalid' => 'رمز القسيمة غير صالح',
    'voucher_already_used' => 'تم استخدام القسيمة مسبقاً',

    // Location messages
    'country_created' => 'تم إنشاء الدولة بنجاح',
    'country_updated' => 'تم تحديث الدولة بنجاح',
    'country_deleted' => 'تم حذف الدولة بنجاح',
    'city_created' => 'تم إنشاء المدينة بنجاح',
    'city_updated' => 'تم تحديث المدينة بنجاح',
    'city_deleted' => 'تم حذف المدينة بنجاح',
    'district_created' => 'تم إنشاء الحي بنجاح',
    'district_updated' => 'تم تحديث الحي بنجاح',
    'district_deleted' => 'تم حذف الحي بنجاح',

    // Attribute messages
    'attribute_created' => 'تم إنشاء الخاصية بنجاح',
    'attribute_updated' => 'تم تحديث الخاصية بنجاح',
    'attribute_deleted' => 'تم حذف الخاصية بنجاح',
    'attribute_value_created' => 'تم إنشاء قيمة الخاصية بنجاح',
    'attribute_value_updated' => 'تم تحديث قيمة الخاصية بنجاح',
    'attribute_value_deleted' => 'تم حذف قيمة الخاصية بنجاح',
];
