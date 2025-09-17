<?php
// Start session
session_start();

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../db_connect.php';

// Initialize response
$response = ['status' => 'error', 'message' => '', 'debug' => ''];

// Check session
if (!isset($_SESSION['user']['user_id'])) {
    http_response_code(401);
    $response['message'] = 'Not logged in';
    $response['debug'] = 'User must be authenticated. Check session configuration in ../db_connect.php.';
    exit(json_encode($response));
}

$user_id = $_SESSION['user']['user_id'];

// Get POST data (JSON or form-data)
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $input = $_POST;
}

// Required survey fields
$required_survey_fields = ['survey_type', 'title'];
foreach ($required_survey_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        $response['message'] = "Missing required survey field: $field";
        $response['debug'] = "Ensure '$field' is included in the POST data.";
        exit(json_encode($response));
    }
}

// Validate survey_type
$valid_survey_types = ['poll', 'questionnaire', 'quiz', 'form'];
if (!in_array($input['survey_type'], $valid_survey_types)) {
    http_response_code(400);
    $response['message'] = "Invalid survey_type: {$input['survey_type']}";
    $response['debug'] = "survey_type must be one of: " . implode(', ', $valid_survey_types);
    exit(json_encode($response));
}

// Prepare survey data
$survey_data = [
    ':owner_id' => $user_id,
    ':category_id' => isset($input['category_id']) ? (int)$input['category_id'] : null,
    ':survey_type' => $input['survey_type'],
    ':title' => $input['title'],
    ':description' => $input['description'] ?? null,
    ':header_media_type' => $input['header_media_type'] ?? 'none',
    ':header_media' => $input['header_media'] ?? null,
    ':font_family' => $input['font_family'] ?? null,
    ':font_size' => isset($input['font_size']) ? (int)$input['font_size'] : 16,
    ':primary_color' => $input['primary_color'] ?? '#000000',
    ':secondary_color' => $input['secondary_color'] ?? '#FFFFFF',
    ':active_color' => $input['active_color'] ?? '#0000FF',
    ':dark_theme' => isset($input['dark_theme']) ? filter_var($input['dark_theme'], FILTER_VALIDATE_BOOLEAN) : false,
    ':background_type' => $input['background_type'] ?? 'solid',
    ':background_media' => $input['background_media'] ?? null,
    ':background_media_input_type' => $input['background_media_input_type'] ?? null,
    ':require_login' => isset($input['require_login']) ? filter_var($input['require_login'], FILTER_VALIDATE_BOOLEAN) : false,
    ':notifications_range' => $input['notifications_range'] ?? 'none',
    ':max_submissions' => isset($input['max_submissions']) ? (int)$input['max_submissions'] : null,
    ':proshield_enabled' => isset($input['proshield_enabled']) ? filter_var($input['proshield_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':proshield_degree' => $input['proshield_degree'] ?? null,
    ':whatsapp_notifications' => isset($input['whatsapp_notifications']) ? filter_var($input['whatsapp_notifications'], FILTER_VALIDATE_BOOLEAN) : false,
    ':whatsapp_number' => $input['whatsapp_number'] ?? null,
    ':email_notifications' => isset($input['email_notifications']) ? filter_var($input['email_notifications'], FILTER_VALIDATE_BOOLEAN) : false,
    ':popup_notifications' => isset($input['popup_notifications']) ? filter_var($input['popup_notifications'], FILTER_VALIDATE_BOOLEAN) : false,
    ':meta_title' => $input['meta_title'] ?? null,
    ':meta_description' => $input['meta_description'] ?? null,
    ':status' => $input['status'] ?? 'draft',
    ':start_date' => $input['start_date'] ?? null,
    ':end_date' => $input['end_date'] ?? null,
    ':time_limit' => isset($input['time_limit']) ? (int)$input['time_limit'] : null,
    ':response_anonymity' => isset($input['response_anonymity']) ? filter_var($input['response_anonymity'], FILTER_VALIDATE_BOOLEAN) : true,
    ':allow_multiple_responses' => isset($input['allow_multiple_responses']) ? filter_var($input['allow_multiple_responses'], FILTER_VALIDATE_BOOLEAN) : false,
    ':response_editable' => isset($input['response_editable']) ? filter_var($input['response_editable'], FILTER_VALIDATE_BOOLEAN) : false,
    ':language' => $input['language'] ?? 'en',
    ':translation_enabled' => isset($input['translation_enabled']) ? filter_var($input['translation_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':custom_url' => $input['custom_url'] ?? null,
    ':access_code' => $input['access_code'] ?? null,
    ':ip_restriction' => isset($input['ip_restriction']) ? filter_var($input['ip_restriction'], FILTER_VALIDATE_BOOLEAN) : false,
    ':geo_restriction' => $input['geo_restriction'] ?? null,
    ':thank_you_message' => $input['thank_you_message'] ?? null,
    ':thank_you_redirect_url' => $input['thank_you_redirect_url'] ?? null,
    ':progress_bar_enabled' => isset($input['progress_bar_enabled']) ? filter_var($input['progress_bar_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':question_randomization' => isset($input['question_randomization']) ? filter_var($input['question_randomization'], FILTER_VALIDATE_BOOLEAN) : false,
    ':response_export_format' => $input['response_export_format'] ?? 'csv',
    ':analytics_enabled' => isset($input['analytics_enabled']) ? filter_var($input['analytics_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':recaptcha_enabled' => isset($input['recaptcha_enabled']) ? filter_var($input['recaptcha_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':custom_css' => $input['custom_css'] ?? null,
    ':custom_js' => $input['custom_js'] ?? null,
    ':survey_password' => isset($input['survey_password']) ? password_hash($input['survey_password'], PASSWORD_BCRYPT) : null,
    ':response_quota_by_group' => isset($input['response_quota_by_group']) ? json_encode($input['response_quota_by_group']) : null,
    ':embed_enabled' => isset($input['embed_enabled']) ? filter_var($input['embed_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':embed_code' => $input['embed_code'] ?? null,
    ':sms_notifications' => isset($input['sms_notifications']) ? filter_var($input['sms_notifications'], FILTER_VALIDATE_BOOLEAN) : false,
    ':sms_number' => $input['sms_number'] ?? null,
    ':conditional_logic_enabled' => isset($input['conditional_logic_enabled']) ? filter_var($input['conditional_logic_enabled'], FILTER_VALIDATE_BOOLEAN) : false,
    ':data_retention_period' => isset($input['data_retention_period']) ? (int)$input['data_retention_period'] : null,
    ':api_key' => $input['api_key'] ?? null,
    ':isopen' => isset($input['isopen']) ? filter_var($input['isopen'], FILTER_VALIDATE_BOOLEAN) : true
];

// Validate optional ENUM fields
$valid_enums = [
    'header_media_type' => ['none', 'image', 'video', 'animation'],
    'background_type' => ['solid', 'gradient', 'image', 'video', 'animation'],
    'background_media_input_type' => ['upload', 'url', 'embed_code', 'stock_library'],
    'notifications_range' => ['none', 'daily', 'weekly', 'monthly'],
    'proshield_degree' => ['low', 'medium', 'high'],
    'status' => ['draft', 'published', 'archived'],
    'response_export_format' => ['csv', 'json', 'pdf']
];

foreach ($valid_enums as $field => $values) {
    if (!empty($survey_data[":" . $field]) && !in_array($survey_data[":" . $field], $values)) {
        http_response_code(400);
        $response['message'] = "Invalid $field: {$survey_data[":" . $field]}";
        $response['debug'] = "$field must be one of: " . implode(', ', $values);
        exit(json_encode($response));
    }
}

// Validate category_id if provided
if (!empty($survey_data[':category_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$survey_data[':category_id']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        $response['message'] = "Invalid category_id: {$survey_data[':category_id']}";
        $response['debug'] = "Ensure category_id exists in the categories table.";
        exit(json_encode($response));
    }
}

// Start transaction for survey, sections, and questions
try {
    $pdo->beginTransaction();

    // Insert survey
    $sql = "INSERT INTO surveys (
        owner_id, category_id, survey_type, title, description, header_media_type, header_media,
        font_family, font_size, primary_color, secondary_color, active_color, dark_theme,
        background_type, background_media, background_media_input_type, require_login,
        notifications_range, max_submissions, proshield_enabled, proshield_degree,
        whatsapp_notifications, whatsapp_number, email_notifications, popup_notifications,
        meta_title, meta_description, status, created_at, updated_at, start_date, end_date,
        time_limit, response_anonymity, allow_multiple_responses, response_editable, language,
        translation_enabled, custom_url, access_code, ip_restriction, geo_restriction,
        thank_you_message, thank_you_redirect_url, progress_bar_enabled, question_randomization,
        response_export_format, analytics_enabled, recaptcha_enabled, custom_css, custom_js,
        survey_password, response_quota_by_group, embed_enabled, embed_code, sms_notifications,
        sms_number, conditional_logic_enabled, data_retention_period,
       api_key, isopen
    ) VALUES (
        :owner_id, :category_id, :survey_type, :title, :description, :header_media_type, :header_media,
        :font_family, :font_size, :primary_color, :secondary_color, :active_color, :dark_theme,
        :background_type, :background_media, :background_media_input_type, :require_login,
        :notifications_range, :max_submissions, :proshield_enabled, :proshield_degree,
        :whatsapp_notifications, :whatsapp_number, :email_notifications, :popup_notifications,
        :meta_title, :meta_description, :status, NOW(), NULL, :start_date, :end_date,
        :time_limit, :response_anonymity, :allow_multiple_responses, :response_editable, :language,
        :translation_enabled, :custom_url, :access_code, :ip_restriction, :geo_restriction,
        :thank_you_message, :thank_you_redirect_url, :progress_bar_enabled, :question_randomization,
        :response_export_format, :analytics_enabled, :recaptcha_enabled, :custom_css, :custom_js,
        :survey_password, :response_quota_by_group, :embed_enabled, :embed_code, :sms_notifications,
        :sms_number, :conditional_logic_enabled, :data_retention_period,
       :api_key, :isopen
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($survey_data);
    $survey_id = $pdo->lastInsertId();

    // Handle sections (optional)
    $sections = $input['sections'] ?? [];
    $section_ids = [];
    foreach ($sections as $index => $section) {
        // Required section fields
        if (empty($section['title'])) {
            $pdo->rollBack();
            http_response_code(400);
            $response['message'] = "Missing required section field: title at index $index";
            $response['debug'] = "Ensure each section has a 'title' field.";
            exit(json_encode($response));
        }

        $section_data = [
            ':survey_id' => $survey_id,
            ':title' => $section['title'],
            ':description' => $section['description'] ?? null,
            ':order_number' => isset($section['order_number']) ? (int)$section['order_number'] : $index + 1,
            ':is_active' => isset($section['is_active']) ? filter_var($section['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            ':media_type' => $section['media_type'] ?? 'none',
            ':media_url' => $section['media_url'] ?? null
        ];

        // Validate section media_type
        $valid_media_types = ['none', 'image', 'video', 'animation'];
        if (!empty($section_data[':media_type']) && !in_array($section_data[':media_type'], $valid_media_types)) {
            $pdo->rollBack();
            http_response_code(400);
            $response['message'] = "Invalid media_type in section $index: {$section_data[':media_type']}";
            $response['debug'] = "media_type must be one of: " . implode(', ', $valid_media_types);
            exit(json_encode($response));
        }

        $section_sql = "INSERT INTO survey_sections (survey_id, title, description, order_number, is_active, media_type, media_url, created_at)
                        VALUES (:survey_id, :title, :description, :order_number, :is_active, :media_type, :media_url, NOW())";
        $section_stmt = $pdo->prepare($section_sql);
        $section_stmt->execute($section_data);
        $section_ids[] = $pdo->lastInsertId();
    }

    // Handle questions (optional)
    foreach ($sections as $section_index => $section) {
        $questions = $section['questions'] ?? [];
        foreach ($questions as $question_index => $question) {
            // Required question fields
            $required_question_fields = ['question_text', 'question_type_id'];
            foreach ($required_question_fields as $field) {
                if (empty($question[$field])) {
                    $pdo->rollBack();
                    http_response_code(400);
                    $response['message'] = "Missing required question field: $field in section $section_index, question $question_index";
                    $response['debug'] = "Ensure each question has '$field'.";
                    exit(json_encode($response));
                }
            }

            // Validate question_type_id
            $stmt = $pdo->prepare("SELECT id FROM question_types WHERE id = ?");
            $stmt->execute([(int)$question['question_type_id']]);
            if (!$stmt->fetch()) {
                $pdo->rollBack();
                http_response_code(400);
                $response['message'] = "Invalid question_type_id: {$question['question_type_id']} in section $section_index, question $question_index";
                $response['debug'] = "Ensure question_type_id exists in question_types table.";
                exit(json_encode($response));
            }

            $question_data = [
                ':section_id' => $section_ids[$section_index],
                ':question_type_id' => (int)$question['question_type_id'],
                ':question_text' => $question['question_text'],
                ':order_number' => isset($question['order_number']) ? (int)$question['order_number'] : $question_index + 1,
                ':is_required' => isset($question['is_required']) ? filter_var($question['is_required'], FILTER_VALIDATE_BOOLEAN) : false,
                ':max_length' => isset($question['max_length']) ? (int)$question['max_length'] : null,
                ':options' => isset($question['options']) ? json_encode($question['options']) : null,
                ':min_value' => isset($question['min_value']) ? (int)$question['min_value'] : null,
                ':max_value' => isset($question['max_value']) ? (int)$question['max_value'] : null,
                ':media_type' => $question['media_type'] ?? 'none',
                ':media_url' => $question['media_url'] ?? null,
                ':placeholder' => $question['placeholder'] ?? null,
                ':validation_rule' => $question['validation_rule'] ?? null
            ];

            // Validate question media_type
            if (!empty($question_data[':media_type']) && !in_array($question_data[':media_type'], $valid_media_types)) {
                $pdo->rollBack();
                http_response_code(400);
                $response['message'] = "Invalid media_type in section $section_index, question $question_index: {$question_data[':media_type']}";
                $response['debug'] = "media_type must be one of: " . implode(', ', $valid_media_types);
                exit(json_encode($response));
            }

            $question_sql = "INSERT INTO survey_questions (
                section_id, question_type_id, question_text, order_number, is_required, max_length,
                options, min_value, max_value, media_type, media_url, placeholder, validation_rule, created_at
            ) VALUES (
                :section_id, :question_type_id, :question_text, :order_number, :is_required, :max_length,
                :options, :min_value, :max_value, :media_type, :media_url, :placeholder, :validation_rule, NOW()
            )";
            $question_stmt = $pdo->prepare($question_sql);
            $question_stmt->execute($question_data);
        }
    }

    $pdo->commit();
    http_response_code(200);
    $response['status'] = 'success';
    $response['message'] = 'Survey, sections, and questions created successfully';
    $response['survey_id'] = $survey_id;
    $response['section_ids'] = $section_ids;
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    $response['message'] = 'Failed to create survey: ' . $e->getMessage();
    $response['debug'] = 'PDO Error: ' . json_encode($stmt->errorInfo()) . 
                         '. SQL Query: ' . $sql . 
                         '. Parameters: ' . json_encode(array_keys($survey_data)) . 
                         '. Fix: Ensure all placeholders in the SQL query match $data keys. Check for typos or missing/extra parameters. Verify question_types and categories tables.';
}

exit(json_encode($response));
?>