<?php

namespace App\Traits;

trait BilingualValidationTrait
{
    /**
     * Get custom messages for validator errors with bilingual support
     */
    public function messages(): array
    {
        $messages = [];
        
        // Get all validation rules
        $rules = $this->rules();
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_array($fieldRules) ? $fieldRules : [$fieldRules];
            
            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $ruleName = explode(':', $rule)[0];
                    $messages[$field . '.' . $ruleName] = $this->getBilingualMessage($field, $ruleName);
                }
            }
        }
        
        return $messages;
    }

    /**
     * Get custom attributes for validator errors with bilingual support
     */
    public function attributes(): array
    {
        $attributes = [];
        
        // Get all validation rules
        $rules = $this->rules();
        
        foreach ($rules as $field => $fieldRules) {
            $attributes[$field] = $this->getBilingualAttribute($field);
        }
        
        return $attributes;
    }

    /**
     * Get bilingual validation message
     */
    protected function getBilingualMessage(string $field, string $rule): string
    {
        $messageKey = "validation.custom.{$field}.{$rule}";
        
        // Try to get custom message first
        $customMessage = __($messageKey);
        if (is_string($customMessage) && $customMessage !== $messageKey) {
            return $customMessage;
        }
        
        // Fallback to general validation message
        $generalMessage = __("validation.{$rule}");
        if (is_string($generalMessage)) {
            return $generalMessage;
        }
        
        // Final fallback
        return "The {$field} field is invalid.";
    }

    /**
     * Get bilingual attribute name
     */
    protected function getBilingualAttribute(string $field): string
    {
        $attributeKey = "validation.attributes.{$field}";
        
        // Try to get custom attribute first
        $customAttribute = __($attributeKey);
        if (is_string($customAttribute) && $customAttribute !== $attributeKey) {
            return $customAttribute;
        }
        
        // Fallback to field name
        return ucfirst(str_replace(['_', '.'], ' ', $field));
    }

    /**
     * Get bilingual success message
     */
    protected function getSuccessMessage(string $action): string
    {
        $message = __("messages.{$action}_successfully");
        return is_string($message) ? $message : "Operation completed successfully";
    }

    /**
     * Get bilingual error message
     */
    protected function getErrorMessage(string $action): string
    {
        $message = __("messages.{$action}_failed");
        return is_string($message) ? $message : "Operation failed";
    }
}
