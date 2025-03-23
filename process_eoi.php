<?php
    declare(strict_types=1);
    require_once 'settings.php';

    // This is non-inheritable class because it is a dataclass:vv
    // use final for prevent inheritence
    final class ValidationRules {
        const PATTERNS = [
            'JOB_REF_NUM' => '/^[a-zA-Z0-9]{5}$/',
            'NAME' => '/^[a-zA-Z]{1,20}$/',
            'DATE' => '/^\d{2}\/\d{2}\/\d{4}$/',
            'PHONE' => '/^[0-9]{8,12}$/',
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
            'FIRST_NAME' => 'First name must be up to 20 alphabetic characters',
            'LAST_NAME' => 'Last name must be up to 20 alphabetic characters',
            'GENDER' => 'Invalid gender selection',
            'EMAIL' => 'Invalid email format',
            'PHONE_NUM' => 'Invalid phone number',
            'STATE' => 'Invalid state',
            'POSTCODE' => 'Postcode does not match state',
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
            ]
        ];

        public const VALID_GENDERS = ['Male', 'Female', 'Other'];
        public const REQUIRED_SKILLS = ['problem_solving', 'leadership', 'communication', 'responsibility'];
    }

    class FormValidator {
        private array $errors = [];
        private array $sanitizedData = [];

        public function sanitize($data) {
            // Check if the input is an array:vvv
            if (is_array($data)) {
                // what I love=))) Recursion!!!
                // "sanitize" call itself to sanitize each $data. Recursion!!!
                return array_map([$this, 'sanitize'], $data);
            }
            return htmlspecialchars(stripslashes(trim($data)));
        }

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

        private function validateDateFormat(string $date) {
            if (!preg_match(ValidationRules::PATTERNS['DATE'], $date)) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['DOB']);
            }

            $parts = array_map('intval', explode('/', $date));
            if (count($parts) !== 3 || !checkdate($parts[1], $parts[0], $parts[2])) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['DOB']);
            }

            return DateTime::createFromFormat('d/m/Y', $date);
        }

        private function validateAge(DateTime $dob): string {
            $age = $dob->diff(new DateTime())->y;
            
            if ($age < 15 || $age > 80) {
                $this -> errors[] = ValidationRules::MESSAGES['DOB'];
            }

            return $dob->format('Y-m-d');
        }

        private function validatePostcode(string $postcode, string $state): void {
            if (!isset(ValidationRules::STATE_POSTCODES[$state])) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['STATE']);
            }
    
            $prefix = substr($postcode, 0, 1);
            if (!in_array($prefix, ValidationRules::STATE_POSTCODES[$state], true)) {
                throw new InvalidArgumentException(ValidationRules::MESSAGES['POSTCODE']);
            }
        }

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

        public function validateInput(array $post): bool {
            $this->validatePatternInput($post);
            $this->validateNonPatternInput($post);
            return empty($this->errors);
        }

        public function getErrors(): array {
            return $this->errors;
        }

        public function getSanitizedData(): array {
            return $this->sanitizedData;
        }
    }

    // Start the session if it hasn't been started yet, it maybe a little bit unnecessary:))
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?apply=error");
        exit();
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

        $validator = new FormValidator();
        if (!$validator->validateInput($_POST)) {
            $_SESSION['errors'] = $validator->getErrors();
            header('Location: error.php');
            exit();
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
            submitTime timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            submitTime timestamp DEFAULT NULL,
            PRIMARY KEY (EOInum),
            UNIQUE KEY email (email),
            UNIQUE KEY phone (phoneNum)
        )";
        $conn -> query($create_table_sql);

        $stmt = $conn->prepare("SELECT EOInum FROM eoi WHERE email = ?");
        $stmt->bind_param('s', $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new InvalidArgumentException(ValidationRules::MESSAGES['DUPLICATE_EMAIL']);
        }

        // Check phone
        $stmt = $conn->prepare("SELECT EOInum FROM eoi WHERE phoneNum = ?");
        $stmt->bind_param('s', $data['phone_num']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new InvalidArgumentException(ValidationRules::MESSAGES['DUPLICATE_PHONE']);
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

        // Parse existing EOI numbers or create new array
        $eoiNums = [];
        if ($user['eoiNums'] !== null) {
            $eoiNums = json_decode($user['eoiNums'], true) ?? [];
        }

        // Add new EOI number
        $eoiNums[] = $conn->insert_id;
        // Store EOI number in session
        $_SESSION['eoi_number'] = $conn->insert_id;

        // Update user's EOI numbers
        $stmt = $conn->prepare("UPDATE users SET eoiNums = ? WHERE id = ?");
        $eoiNumsJson = json_encode($eoiNums);
        $stmt->bind_param('si', $eoiNumsJson, $_SESSION['user_id']);
        
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to update user EOI numbers: ' . $stmt->error);
        }

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