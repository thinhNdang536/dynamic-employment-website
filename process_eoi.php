<?php
    /**
        * EOI Application Form Processing and Validation
        *
        * This script handles form submission for an Expression of Interest (EOI) application.
        * It validates and sanitizes user input according to predefined rules, creates a database
        * table if it does not exist, checks for duplicate email and phone entries, and inserts
        * a new record into the database. If any errors occur, they are logged and the user is
        * redirected to an error page.
        *
        * PHP version 8.2.12
        *
        * @category   Management
        * @package    Assignment2
        * @author     Dang Quang Thinh
        * @student-id 105551875
        * @version    1.0.0
    */

    declare(strict_types=1);
    require_once 'settings.php';

    /**
        * Final class ValidationRules
        *
        * This class defines the regex patterns, valid state/postcode pairs, error messages, and 
        * form field validation rules used by the FormValidator class.
        * useing final for preventing inheritance=))
        * aka data class:vv
    */
    final class ValidationRules {
        const PATTERNS = [
            'JOB_REF_NUM' => '/^[a-zA-Z0-9]{5}$/',
            'NAME' => '/^[a-zA-Z]{1,20}$/',
            'DATE' => '/^\d{4}-\d{2}-\d{2}$/',
            'PHONE' => '/^[0-9]{8,12}$/',
            'ADDRESS' => '/^[a-zA-Z0-9\s,.\-#]{3,100}$/',
            'SUBURB_TOWN' => '/^[a-zA-Z\s\-\'\.]{2,50}$/',
            'POSTCODE' => '/^\d{4}$/',
            'EMAIL' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ];

        // Valid states and their postcode prefixes
        const STATE_POSTCODES = [
            'VIC' => ['3', '8'],
            'NSW' => ['1', '2'], 
            'QLD' => ['4', '9'],
            'NT' => ['0'], 
            'WA' => ['6'],
            'SA' => ['5'],
            'TAS' => ['7'],
            'ACT' => ['0']
        ];

        // Error messages
        const MESSAGES = [
            'JOB_REF_NUM' => 'Job reference number must be exactly 5 alphanumeric characters',
            'NON_EXIST_JOBREF' => 'There is no job with this job reference number',
            'FIRST_NAME' => 'First name must be up to 20 alphabetic characters',
            'LAST_NAME' => 'Last name must be up to 20 alphabetic characters',
            'GENDER' => 'Invalid gender selection',
            'EMAIL' => 'Invalid email format',
            'PHONE_NUM' => 'Invalid phone number',
            'ADDRESS' => 'Invalid address',
            'SUBURB_TOWN' => 'Invalid suburb/town',
            'STATE' => 'Invalid state',
            'POSTCODE' => 'Postcode does not match state',
            'DATE' => 'Invalid date format',
            'DOB' => 'Age must be between 15 and 80',
            'SKILLS' => 'At least one skill must be selected',
            'OTHER_SKILLS' => 'Other skills description required',
            'DUPLICATE_EMAIL' => 'An application with this email already exists',
            'DUPLICATE_PHONE' => 'An application with this phone number already exists',
            'INVALID_REQUEST' => 'Invalid request method',
            'UNEXPECTED_ERROR' => 'An unexpected error occurred'
        ];

        // Form field validation rules
        const FIELDS = [
            'job_ref_num' => [
                'name' => 'Job Reference Number',
                'pattern' => self::PATTERNS['JOB_REF_NUM'],
                'error' => self::MESSAGES['JOB_REF_NUM']
            ],
            'first_name' => [
                'name' => 'First Name',
                'pattern' => self::PATTERNS['NAME'],
                'error' => self::MESSAGES['FIRST_NAME']
            ],
            'last_name' => [
                'name' => 'Last Name',
                'pattern' => self::PATTERNS['NAME'],
                'error' => self::MESSAGES['LAST_NAME']
            ],
            'email' => [
                'name' => 'Email',
                'pattern' => self::PATTERNS['EMAIL'],
                'error' => self::MESSAGES['EMAIL']
            ],
            'phone_num' => [
                'name' => 'Phone Number',
                'pattern' => self::PATTERNS['PHONE'],
                'error' => self::MESSAGES['PHONE_NUM']
            ],
            'address' => [
                'name' => 'Address',
                'pattern' => self::PATTERNS['ADDRESS'],
                'error' => self::MESSAGES['ADDRESS']
            ],
            'town' => [
                'name' => 'Suburb/Town',
                'pattern' => self::PATTERNS['SUBURB_TOWN'],
                'error' => self::MESSAGES['SUBURB_TOWN']
            ]
        ];

        public const VALID_GENDERS = ['Male', 'Female', 'Other'];
        public const REQUIRED_SKILLS = ['problem_solving', 'leadership', 'communication', 'responsibility'];
    }



    /**
        * Class FormValidator
        *
        * This class handles sanitization and validation of form input data.
        * It validates inputs using regex patterns and custom rules, and stores sanitized
        * data for further processing.
    */
    class FormValidator {
        private array $errors = [];
        private array $sanitizedData = [];

        /**
            * Sanitize input data recursively.
            *
            * @param mixed $data Input data (string or array)
            * @return mixed Sanitized data
        */
        public function sanitize($data) {
            // Check if the input is an array:vvv
            if (is_array($data)) {
                // what I love=))) Recursion!!!
                // "sanitize" call itself to sanitize each $data. Recursion!!!
                return array_map([$this, 'sanitize'], $data);
            }
            return htmlspecialchars(stripslashes(trim($data)));
        }

        /**
            * Validate inputs based on regex patterns defined in ValidationRules::FIELDS.
            *
            * @param array $post Form input data
        */
        private function validatePatternInput(array $post) {
            foreach (ValidationRules::FIELDS as $field => $rules) {
                $value = $this -> sanitize($post[$field] ?? '');

                try {
                    if (empty($value)) {
                        throw new InvalidArgumentException("{$rules['name']} is required");
                    }

                    if (!preg_match($rules['pattern'], $value)) {
                        throw new InvalidArgumentException($rules['error']);
                    }

                    $this -> sanitizedData[$field] = $value;
                } catch (Exception $error) {
                    $this->errors[] = $error->getMessage();
                }
            }
        }

        /**
            * Validate date format and return a DateTime object.
            *
            * @param string $date Date string in d/m/Y format
            * @return DateTime Validated DateTime object
            * @throws InvalidArgumentException If date is invalid
        */
        private function validateDateFormat(string $date) {
            if (!preg_match(ValidationRules::PATTERNS['DATE'], $date)) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['DATE']);
            }

            $parts = array_map('intval', explode('-', $date));
            if (count($parts) !== 3 || !checkdate($parts[1], $parts[2], $parts[0])) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['DATE']);
            }

            // Create a DateTime object from the date string
            $dateTime = DateTime::createFromFormat('Y-m-d', $date);

            // Check if DateTime creation was successful
            if ($dateTime === false) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['DATE']);
            }
        
            return $dateTime;
        }

        /**
            * Validate age from DateTime object.
            *
            * @param DateTime $dob Date of birth
            * @return string Formatted date string (Y-m-d)
        */
        private function validateAge(DateTime $dob): string {
            $age = $dob->diff(new DateTime())->y;

            if ($age < 15 || $age > 80) {
                $this -> errors[] = ValidationRules::MESSAGES['DOB'];
            }

            return $dob->format('Y-m-d');
        }

        /**
            * Validate that the postcode matches the state's valid postcode prefixes.
            *
            * @param string $postcode Postcode value
            * @param string $state State abbreviation
            * @throws InvalidArgumentException If state is invalid or postcode prefix does not match
        */
        private function validatePostcode(string $postcode, string $state): void {
            if (!isset(ValidationRules::STATE_POSTCODES[$state])) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['STATE']);
            }
    
            $prefix = substr($postcode, 0, 1);
            if (!in_array($prefix, ValidationRules::STATE_POSTCODES[$state], true)) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['POSTCODE']);
            }
        }

        /**
            * Validate non-pattern inputs such as address, DOB, gender, state, postcode, and skills.
            *
            * @param array $post Form input data
        */
        private function validateNonPatternInput(array $post): void {
            $this->sanitizedData['address'] = $this->sanitize($post['address'] ?? '');
            $this->sanitizedData['suburb'] = $this->sanitize($post['town'] ?? '');

            // Validate DOB
            $dob = $post['dob'] ?? '';
            try {
                $dobDate = $this->validateDateFormat($dob);
                $this->sanitizedData['dob'] = $this->validateAge($dobDate);
            } catch (Exception $error) {
                $this->errors[] = $error->getMessage();
            }
            
            // Validate Gender
            $gender = ucfirst($post['gender'] ?? '');
            if (in_array($gender, ValidationRules::VALID_GENDERS, true)) {
                $this->sanitizedData['gender'] = $gender;
            } else {
                $this->errors[] = ValidationRules::MESSAGES['GENDER'];
            }

            // Validate State and Postcode
            try {
                $this->validatePostcode(
                    $this->sanitize($post['postcode']),
                    $this->sanitize($post['state'])
                );

                $this->sanitizedData['state'] = $this->sanitize($post['state'] ?? '');
                $this->sanitizedData['postcode'] = $this->sanitize($post['postcode'] ?? '');
            } catch (Exception $error) {
                $this->errors[] = $error->getMessage();
            }

            // Validate Skills
            $skills = (array)($post['skills'] ?? []);
            if (!empty($skills)) {
                $this->sanitizedData['skills'] = json_encode($this->sanitize($skills));
            } else {
                $this->errors[] = ValidationRules::MESSAGES['SKILLS'];
            }
              
            // Validate Other Skills if selected
            if (in_array('others', $skills, true)) {
                $otherSkills = $this->sanitize($post['other_skills'] ?? '');
                if (!empty($otherSkills)) {
                    $this->sanitizedData['other_skills'] = $otherSkills;
                } else {
                    throw new InvalidArgumentException('Other skills description required');
                }
            }
        }

        /**
            * Validate the form input data.
            *
            * This method validates both pattern-based inputs (using regex) and non-pattern inputs
            * (e.g., date, gender, state, postcode, skills) by calling the respective private validation
            * methods. It returns true if no errors were encountered during validation.
            *
            * @param array $post An associative array containing the raw form input data.
            * @return bool True if validation passes (no errors), false otherwise.
        */
        public function validateInput(array $post): bool {
            $this->validatePatternInput($post);
            $this->validateNonPatternInput($post);
            return empty($this->errors);
        }

        /**
            * Retrieve validation error messages.
            *
            * Returns an array of error messages generated during the validation process.
            *
            * @return array An array of error messages.
        */
        public function getErrors(): array {
            return $this->errors;
        }

        /**
            * Retrieve sanitized form input data.
            *
            * Returns an associative array containing the sanitized data from the form.
            *
            * @return array The sanitized form input data.
        */
        public function getSanitizedData(): array {
            return $this->sanitizedData;
        }
    }

    // Start the session if it hasn't been started yet, it maybe a little bit unnecessary:))
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear form data if reset button clicked
    if (isset($_POST['reset'])) {
        unset($_SESSION['form_data']);
        header('Location: apply.php');
        exit();
    }
    $_SESSION['form_data'] = $_POST;

    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new RuntimeException(ValidationRules::MESSAGES['INVALID_REQUEST']);
        }

        // Check if fields meet pattern
        $validator = new FormValidator();
        if (!$validator->validateInput($_POST)) {
            $_SESSION['errors'] = $validator->getErrors();
        }
        $data = $validator->getSanitizedData();

        // Connect to db
        $db = new Database();
        $conn = $db -> getConnection();
        
        $create_table_sql = "CREATE TABLE IF NOT EXISTS eoi (
            EOInum int NOT NULL AUTO_INCREMENT,
            jobRef varchar(255) NOT NULL,
            firstName varchar(100) NOT NULL,
            lastName varchar(100) NOT NULL,
            dob date NOT NULL,
            gender enum('Male','Female','Other') NOT NULL,
            address varchar(255) NOT NULL,
            subTown varchar(100) NOT NULL,
            state enum('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
            postcode varchar(10) NOT NULL,
            email varchar(255) NOT NULL,
            phoneNum varchar(15) NOT NULL,
            skills text NOT NULL,
            otherSkills text DEFAULT NULL,
            status enum('New','Current','Final') NOT NULL DEFAULT 'New',
            submitTime timestamp DEFAULT NULL,
            PRIMARY KEY (EOInum),
            UNIQUE KEY email (email),
            UNIQUE KEY phone (phoneNum)
        )";
        $conn -> query($create_table_sql);

        // Check if jobRef not exists
        $stmt = $conn->prepare("SELECT jobRef FROM jobs WHERE jobRef = ?");
        $stmt->bind_param('s', $data['job_ref_num']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $_SESSION['errors'] = ValidationRules::MESSAGES['NON_EXIST_JOBREF'];
            header('Location: error.php');
            exit();
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT EOInum FROM eoi WHERE email = ?");
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['errors'][] = ValidationRules::MESSAGES['DUPLICATE_EMAIL'];
        }

        // Check if phonenumber already exists
        $stmt = $conn->prepare("SELECT EOInum FROM eoi WHERE phoneNum = ?");
        $stmt->bind_param('s', $data['phone_num']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['errors'][] = ValidationRules::MESSAGES['DUPLICATE_PHONE'];
        }

        if (!empty($_SESSION['errors'])) {
            header('Location: error.php');
            exit();
        }

        // // Prepare INSERT statement
        $stmt = $conn->prepare("INSERT INTO eoi (
            jobRef, firstName, lastName, dob, gender, 
            address, subTown, state, postcode, 
            email, phoneNum, skills, otherSkills
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param('sssssssssssss',
            $data['job_ref_num'],
            $data['first_name'],
            $data['last_name'],
            $data['dob'],
            $data['gender'],
            $data['address'],
            $data['suburb'],
            $data['state'],
            $data['postcode'],
            $data['email'],
            $data['phone_num'],
            $data['skills'],
            $data['other_skills']
        );

        if (!$stmt->execute()) {
            throw new RuntimeException('Execute failed: ' . $stmt->error);
        }

        $newEoiNum = $conn->insert_id;
        if ($newEoiNum === 0) {
            throw new RuntimeException('No new auto-increment ID generated.');
        }

        // Fetch the user's current data (including eoiNums) from the database
        $stmt = $conn->prepare("SELECT eoiNums FROM users WHERE id = ?");
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Parse existing EOI numbers or create a new array if null
        $eoiNums = [];
        if (isset($user['eoiNums']) && $user['eoiNums'] !== null) {
            $decoded = json_decode($user['eoiNums'], true);
            $eoiNums = is_array($decoded) ? $decoded : [];
        }

        // Append the new EOI number (obtained from the insert operation)
        $eoiNums[] = $newEoiNum;

        // Store the new EOI number in session (optional)
        $_SESSION['eoi_number'] = $newEoiNum;

        // Convert the updated array to JSON
        $eoiNumsJson = json_encode($eoiNums);

        // Update the user's eoiNums field with the new JSON data
        $stmt = $conn->prepare("UPDATE users SET eoiNums = ? WHERE id = ?");
        $stmt->bind_param('si', $eoiNumsJson, $_SESSION['user_id']);
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to update user EOI numbers: ' . $stmt->error);
        }
        $stmt->close();

        // Clear form data on success
        // unset($_SESSION['form_data']);

        header('Location: success.php');
        exit();

    } catch (Exception $error) {
        error_log($error->getMessage());
        $_SESSION['errors'] = $error->getMessage();
        header('Location: error.php');
        exit();
    }
?>
