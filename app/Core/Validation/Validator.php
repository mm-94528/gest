<?php
// app/Core/Validation/Validator.php
class Validator {
    private $data;
    private $rules;
    private $messages;
    private $errors = [];
    private $customMessages = [];

    public function __construct($data, $rules, $messages = []) {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->setDefaultMessages();
    }

    private function setDefaultMessages() {
        $this->messages = [
            'required' => 'Il campo :field è obbligatorio.',
            'email' => 'Il campo :field deve essere un indirizzo email valido.',
            'min' => 'Il campo :field deve essere almeno :min caratteri.',
            'max' => 'Il campo :field non può superare :max caratteri.',
            'numeric' => 'Il campo :field deve essere un numero.',
            'integer' => 'Il campo :field deve essere un numero intero.',
            'date' => 'Il campo :field deve essere una data valida.',
            'unique' => 'Il valore del campo :field è già in uso.',
            'confirmed' => 'La conferma del campo :field non corrisponde.',
            'in' => 'Il valore selezionato per :field non è valido.',
            'regex' => 'Il formato del campo :field non è valido.',
            'alpha' => 'Il campo :field può contenere solo lettere.',
            'alpha_num' => 'Il campo :field può contenere solo lettere e numeri.',
        ];
    }

    public function validate() {
        foreach ($this->rules as $field => $rules) {
            $rulesArray = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($rulesArray as $rule) {
                $this->validateRule($field, $rule);
            }
        }
        
        return empty($this->errors);
    }

    private function validateRule($field, $rule) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        $value = $this->data[$field] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'required');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'email');
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$parameter) {
                    $this->addError($field, 'min', ['min' => $parameter]);
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$parameter) {
                    $this->addError($field, 'max', ['max' => $parameter]);
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'numeric');
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'integer');
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->addError($field, 'date');
                }
                break;

            case 'unique':
                if (!empty($value)) {
                    list($table, $column) = explode(',', $parameter);
                    if ($this->checkUnique($table, $column, $value)) {
                        $this->addError($field, 'unique');
                    }
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'confirmed');
                }
                break;

            case 'in':
                $values = explode(',', $parameter);
                if (!empty($value) && !in_array($value, $values)) {
                    $this->addError($field, 'in');
                }
                break;

            case 'regex':
                if (!empty($value) && !preg_match($parameter, $value)) {
                    $this->addError($field, 'regex');
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'alpha');
                }
                break;

            case 'alpha_num':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'alpha_num');
                }
                break;
        }
    }

    private function checkUnique($table, $column, $value) {
        $app = Application::getInstance();
        $database = $app->get('database');
        
        $result = $database->fetchOne(
            "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?",
            [$value]
        );
        
        return $result['count'] > 0;
    }

    private function addError($field, $rule, $parameters = []) {
        $message = $this->customMessages[$field . '.' . $rule] ?? 
                  $this->customMessages[$rule] ?? 
                  $this->messages[$rule];

        $message = str_replace(':field', ucfirst(str_replace('_', ' ', $field)), $message);
        
        foreach ($parameters as $key => $value) {
            $message = str_replace(':' . $key, $value, $message);
        }

        $this->errors[$field][] = $message;
    }

    public function passes() {
        return empty($this->errors);
    }

    public function fails() {
        return !$this->passes();
    }

    public function errors() {
        return $this->errors;
    }

    public function validated() {
        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }
}