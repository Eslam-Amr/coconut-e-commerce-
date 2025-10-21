<?php

return [
    // General messages
    'success' => 'Operation completed successfully',
    'error' => 'An error occurred',
    'validation_failed' => 'Validation failed',
    'unauthorized' => 'Unauthorized access',
    'forbidden' => 'Access forbidden',
    'not_found' => 'Resource not found',
    'resource_not_found' => 'The requested :resource was not found',
    'route_not_found' => 'The requested route could not be found',
    'method_not_allowed' => 'Method not allowed',
    'server_error' => 'Internal server error',
    'database_error' => 'Database error occurred',
    'database_connection_error' => 'Database connection error',
    'file_not_found' => 'File not found',
    'unauthenticated' => 'Authentication required',
    'already_authenticated' => 'You are already logged in',
    'token_expired' => 'Token has expired',
    'token_invalid' => 'Invalid token',
    'rate_limit_exceeded' => 'Too many requests',
    'maintenance_mode' => 'Application is in maintenance mode',

    // CRUD operations
    'created_successfully' => 'Resource created successfully',
    'updated_successfully' => 'Resource updated successfully',
    'deleted_successfully' => 'Resource deleted successfully',
    'retrieved_successfully' => 'Resource retrieved successfully',
    'list_retrieved_successfully' => 'Resources retrieved successfully',

    // Authentication
    'login_successful' => 'Login successful',
    'logout_successful' => 'Logout successful',
    'registration_successful' => 'Registration successful',
    'password_reset_successful' => 'Password reset successful',
    'email_verification_sent' => 'Email verification sent',
    'phone_verification_sent' => 'Phone verification sent',
    'otp_verified' => 'OTP verified successfully',
    'otp_expired' => 'OTP has expired',
    'otp_invalid' => 'Invalid OTP',
    'account_verified' => 'Account verified successfully',
    'password_changed' => 'Password changed successfully',
    'profile_updated' => 'Profile updated successfully',

    // Validation messages
    'required' => 'This field is required',
    'email' => 'Please enter a valid email address',
    'phone' => 'Please enter a valid phone number',
    'password' => 'Password must be at least 8 characters',
    'password_confirmation' => 'Password confirmation does not match',
    'unique' => 'This value is already taken',
    'exists' => 'The selected value does not exist',
    'min' => 'This field must be at least :min characters',
    'max' => 'This field may not be greater than :max characters',
    'numeric' => 'This field must be a number',
    'integer' => 'This field must be an integer',
    'boolean' => 'This field must be true or false',
    'date' => 'This field must be a valid date',
    'image' => 'This field must be an image',
    'file' => 'This field must be a file',
    'mimes' => 'This field must be a file of type: :values',
    'size' => 'This field must be :size kilobytes',
    'string' => 'This field must be a string',
    'email_required' => 'Email is required',

    // Custom validation messages
    'phone_or_email_required' => 'Either phone or email is required',
    'password_required' => 'Password is required',
    'phone_required' => 'Phone number is required',
    
    // Service-specific messages
    'cart_empty' => 'Cart is empty',
    'order_confirmation_failed' => 'Order confirmation failed',
    'order_processing_failed' => 'Order processing failed',
    'order_confirmed' => 'Order confirmed successfully',
    'retrieval_failed' => 'Failed to retrieve data',
    'creation_failed' => 'Creation failed',
    'update_failed' => 'Update failed',
    'deletion_failed' => 'Deletion failed',
    'not_found_or_unauthorized' => 'Not found or unauthorized',
    'no_cart_found' => 'No cart found',
    'variant_mismatch' => 'Variant does not belong to product',
    'insufficient_stock' => 'Insufficient stock available',
    'flash_sale_limit_exceeded' => 'Flash sale limit exceeded',
    'added_to_cart' => 'Item added to cart successfully',
    'cart_add_failed' => 'Failed to add to cart',
    'cannot_deactivate_self' => 'You cannot deactivate your own account',
    'phone_exists' => 'This phone number is not registered',
    'email_exists' => 'This email is not registered',
    'invalid_credentials' => 'Invalid credentials',
    'account_locked' => 'Account is locked',
    'account_suspended' => 'Account is suspended',
    'email_already_verified' => 'Email is already verified',
    'phone_already_verified' => 'Phone is already verified',

    // Business logic messages
    'cart_updated' => 'Cart updated successfully',
    'item_added_to_cart' => 'Item added to cart successfully',
    'item_removed_from_cart' => 'Item removed from cart successfully',
    'cart_cleared' => 'Cart cleared successfully',
    'order_created' => 'Order created successfully',
    'order_updated' => 'Order updated successfully',
    'order_cancelled' => 'Order cancelled successfully',
    'payment_successful' => 'Payment successful',
    'payment_failed' => 'Payment failed',
    'refund_processed' => 'Refund processed successfully',
    'wishlist_updated' => 'Wishlist updated successfully',
    'review_submitted' => 'Review submitted successfully',
    'rating_submitted' => 'Rating submitted successfully',

    // File upload messages
    'file_uploaded' => 'File uploaded successfully',
    'file_deleted' => 'File deleted successfully',
    'file_too_large' => 'File is too large',
    'invalid_file_type' => 'Invalid file type',
    'upload_failed' => 'File upload failed',

    // Notification messages
    'notification_sent' => 'Notification sent successfully',
    'notification_marked_read' => 'Notification marked as read',
    'notifications_cleared' => 'Notifications cleared successfully',

    // Settings messages
    'settings_updated' => 'Settings updated successfully',
    'language_changed' => 'Language changed successfully',
    'theme_changed' => 'Theme changed successfully',
    'privacy_settings_updated' => 'Privacy settings updated successfully',

    // Admin messages
    'admin_created' => 'Admin created successfully',
    'admin_updated' => 'Admin updated successfully',
    'admin_deleted' => 'Admin deleted successfully',
    'role_assigned' => 'Role assigned successfully',
    'permission_granted' => 'Permission granted successfully',
    'permission_revoked' => 'Permission revoked successfully',

    // Product messages
    'product_created' => 'Product created successfully',
    'product_updated' => 'Product updated successfully',
    'product_deleted' => 'Product deleted successfully',
    'product_published' => 'Product published successfully',
    'product_unpublished' => 'Product unpublished successfully',
    'variant_created' => 'Product variant created successfully',
    'variant_updated' => 'Product variant updated successfully',
    'variant_deleted' => 'Product variant deleted successfully',

    // Category messages
    'category_created' => 'Category created successfully',
    'category_updated' => 'Category updated successfully',
    'category_deleted' => 'Category deleted successfully',

    // Brand messages
    'brand_created' => 'Brand created successfully',
    'brand_updated' => 'Brand updated successfully',
    'brand_deleted' => 'Brand deleted successfully',

    // Banner messages
    'banner_created' => 'Banner created successfully',
    'banner_updated' => 'Banner updated successfully',
    'banner_deleted' => 'Banner deleted successfully',
    'banner_activated' => 'Banner activated successfully',
    'banner_deactivated' => 'Banner deactivated successfully',

    // Slider messages
    'slider_created' => 'Slider created successfully',
    'slider_updated' => 'Slider updated successfully',
    'slider_deleted' => 'Slider deleted successfully',
    'slider_activated' => 'Slider activated successfully',
    'slider_deactivated' => 'Slider deactivated successfully',

    // Static page messages
    'static_page_created' => 'Static page created successfully',
    'static_page_updated' => 'Static page updated successfully',
    'static_page_deleted' => 'Static page deleted successfully',

    // Flash sale messages
    'flash_sale_created' => 'Flash sale created successfully',
    'flash_sale_updated' => 'Flash sale updated successfully',
    'flash_sale_deleted' => 'Flash sale deleted successfully',
    'flash_sale_started' => 'Flash sale started successfully',
    'flash_sale_ended' => 'Flash sale ended successfully',

    // Voucher messages
    'voucher_created' => 'Voucher created successfully',
    'voucher_updated' => 'Voucher updated successfully',
    'voucher_deleted' => 'Voucher deleted successfully',
    'voucher_applied' => 'Voucher applied successfully',
    'voucher_removed' => 'Voucher removed successfully',
    'voucher_expired' => 'Voucher has expired',
    'voucher_invalid' => 'Invalid voucher code',
    'voucher_already_used' => 'Voucher has already been used',

    // Location messages
    'country_created' => 'Country created successfully',
    'country_updated' => 'Country updated successfully',
    'country_deleted' => 'Country deleted successfully',
    'city_created' => 'City created successfully',
    'city_updated' => 'City updated successfully',
    'city_deleted' => 'City deleted successfully',
    'district_created' => 'District created successfully',
    'district_updated' => 'District updated successfully',
    'district_deleted' => 'District deleted successfully',

    // Attribute messages
    'attribute_created' => 'Attribute created successfully',
    'attribute_updated' => 'Attribute updated successfully',
    'attribute_deleted' => 'Attribute deleted successfully',
    'attribute_value_created' => 'Attribute value created successfully',
    'attribute_value_updated' => 'Attribute value updated successfully',
    'attribute_value_deleted' => 'Attribute value deleted successfully',
];
