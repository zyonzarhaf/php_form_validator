<?php
/**
 * Validates HTML form fields.
 *
 * It provides methods to check if fields are empty, numeric,
 * valid email addresses, conform to specified length requirements, and more.
 *
 * @author Zyon Zarhaf <zyonzarhaf@gmail.com>
 */
class FormValidator {
    /**
     * @var array A copy of the submitted HTML form data.
     */
    private $body;

    /**
     * @var array The current field being validated, including its name and value.
     */
    private $currentValidation;

    /**
     * @var array The results of validation checks for fields that failed validation.
     */
    private $validationResult;

    /**
     * FormValidator constructor.
     *
     * Initializes the FormValidator with the provided form data.
     *
     * @param array $body An associative array representing the form data.
     */
    public function __construct(array $body) {
        $this->body = $body;
        $this->currentValidation = [];
        $this->validationResult = [];
    }

    /**
     * Retrieves the name (key) of the field currently being validated.
     *
     * @return string The name (key) of the field.
     */
    private function getFieldName(): string {
        return key($this->currentValidation);
    }

    /**
     * Retrieves the value of the field currently being validated.
     *
     * @return mixed The value of the field.
     */
    private function getFieldValue(): mixed {
        return $this->currentValidation[$this->getFieldName()];
    }

    /**
     * Sets the field to be validated and throws an exception if the field does not exist in the form data.
     *
     * @param string $field The name (key) of the field to validate.
     * @return FormValidator The instance of the Validator for method chaining.
     * @throws Exception If the specified field does not exist in the form data.
     */
    public function validateField(string $field): FormValidator {
        if (!isset($this->body[$field])) {
            throw new Exception("Field '$field' does not exist");
        }

        $this->currentValidation = [$field => $this->body[$field]];
        return $this;
    }

    /**
     * Checks if the target field is empty. A field is considered empty if its value evaluates to false.
     *
     * @return FormValidator The instance of the Validator for method chaining.
     */
    public function isEmpty(): FormValidator {
        $key = $this->getFieldName();
        $value = $this->getFieldValue();

        if (empty($value)) {
            $this->validationResult[$key] = "Field '$key' is empty";
        }

        return $this;
    }

    /**
     * Checks if the target field contains a numeric value. Optionally checks against min and max limits.
     *
     * @param array $opt Optional parameters for validation:
     *                   - 'min': Minimum acceptable numeric value (optional).
     *                   - 'max': Maximum acceptable numeric value (optional).
     *
     * @return FormValidator The instance of the Validator for method chaining.
     * @throws InvalidArgumentException If 'min' or 'max' are specified but are not numbers.
     */
    public function isNumeric(array $opt = ['min' => null, 'max' => null]): FormValidator {
        $key = $this->getFieldName();
        $value = $this->getFieldValue();

        if (!is_numeric($value)) {
            $this->validationResult[$key] = "Field '$key' is not a number";
        }

        if (isset($opt['min'])) {
            $min = $opt['min'];
            if (!is_numeric($min)) {
                throw new InvalidArgumentException("Option 'min' must be a number");
            }

            if ($min > $value) {
                $this->validationResult[$key] = "Field '$key' must be at least $min";
            }
        }

        if (isset($opt['max'])) {
            $max = $opt['max'];
            if (!is_numeric($max)) {
                throw new InvalidArgumentException("Option 'max' must be a number");
            }

            if ($max < $value) {
                $this->validationResult[$key] = "Field '$key' cannot exceed $max";
            }
        }

        return $this;
    }

    /**
     * Validates that the target field contains a properly formatted email address.
     *
     * @return FormValidator The instance of the Validator for method chaining.
     */
    public function isEmail(): FormValidator {
        $key = $this->getFieldName();
        $value = $this->getFieldValue();

        if (!preg_match('/^[\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/', $value)) {
            $this->validationResult[$key] = "Field '$key' is not a valid email";
        }

        return $this;
    }

    /**
     * Checks if the target field is a string with specified minimum and maximum length constraints.
     *
     * @param array $opt Optional parameters for validation:
     *                   - 'min': Minimum acceptable length (optional).
     *                   - 'max': Maximum acceptable length (optional).
     *
     * @return FormValidator The instance of the Validator for method chaining.
     * @throws InvalidArgumentException If 'min' or 'max' are specified but are not numbers.
     */
    public function isLength(array $opt = ['min' => null, 'max' => null]): FormValidator {
        $key = $this->getFieldName();
        $value = $this->getFieldValue();
        $length = strlen($value);

        if (isset($opt['min'])) {
            $min = $opt['min'];
            if (!is_numeric($min)) {
                throw new InvalidArgumentException("Option 'min' must be a number");
            }

            if ($min > $length) {
                $this->validationResult[$key] = "Field '$key' must be at least {$min} characters long";
            }
        }

        if (isset($opt['max'])) {
            $max = $opt['max'];
            if (!is_numeric($max)) {
                throw new InvalidArgumentException("Option 'max' must be a number");
            }

            if ($max < $length) {
                $this->validationResult[$key] = "Field '$key' cannot exceed {$max} characters";
            }
        }

        return $this;
    }

    /**
     * Retrieves validation results for all fields that failed validation checks.
     *
     * @return array An associative array containing field names as keys and their respective error messages as values.
     */
    public function getValidationResult(): array {
        return $this->validationResult;
    }
}

// Example usage
$validator = new FormValidator(
    [
        'name' => 'Datena',
        'age' => '63',
        'occupation' => 'jornalista',
        'email' => 'datena_dando_cadeirada_ao_vivo@gmail.com'
    ]
);

// Tests
$validator->validateField('name')
          ->isEmpty()
          ->isLength(['min' => 3, 'max' => 25]);

$validator->validateField('age')
          ->isEmpty()
          ->isNumeric();

$validator->validateField('occupation')
          ->isEmpty();

$validator->validateField('email')
          ->isEmpty()
          ->isEmail();

var_dump($validator->getValidationResult());
