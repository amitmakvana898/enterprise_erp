<?php

namespace App\Helpers;

class Validator {
    protected array $errors = [];

    public function validate(array $data, array $rules): bool {
        $this->errors = [];
        foreach ($rules as $field => $ruleString) {
            $rulesList = explode('|', $ruleString);
            $value = trim($data[$field] ?? '');

            foreach ($rulesList as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
                    break;
                }
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "Invalid email format.";
                    break;
                }
                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.";
                        break;
                    }
                }
                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = ucfirst(str_replace('_', ' ', $field)) . " must be a valid number.";
                    break;
                }
            }
        }
        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): ?string {
        return reset($this->errors) ?: null;
    }
}
