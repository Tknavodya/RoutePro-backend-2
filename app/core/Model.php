<?php

require_once __DIR__ . '/Database.php';

class Model {
    protected $db;

    public function __construct() {
        $database = new Database();
        $this->db = new PDO("mysql:host=localhost;dbname=route_pro_db", "root", "pubz");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (!empty($value) && isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'Invalid email format';
            }
            
            if (!empty($value) && isset($rule['min']) && strlen($value) < $rule['min']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min'] . ' characters';
            }
            
            if (!empty($value) && isset($rule['max']) && strlen($value) > $rule['max']) {
                $errors[$field] = ucfirst($field) . ' must be less than ' . $rule['max'] . ' characters';
            }
        }
        
        return $errors;
    }
}
