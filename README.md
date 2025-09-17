# Survey Management API

This is a robust, PHP-based backend API designed for creating and managing highly customizable surveys, polls, questionnaires, and forms. It provides a comprehensive set of endpoints to handle complex survey structures, including nested sections and a wide variety of question types, with all data persisted in a MySQL database.

## Features

- **Dynamic Survey Creation**: Build complex surveys with extensive customization options for appearance, behavior, and security.
- **Structured Content**: Organize surveys into logical sections, each containing multiple questions.
- **Rich Question Types**: Supports various question formats, from simple text inputs to multiple-choice and ratings.
- **Transactional Integrity**: Ensures that a survey and all its components are created atomically, preventing partial data insertion.
- **Authentication**: Secures survey creation to authenticated users via session management.

## Technologies Used

| Technology | Description |
| :--- | :--- |
| **PHP** | Core server-side language for API logic and data processing. |
| **MySQL** | Relational database for storing all survey, section, and question data. |
| **JSON** | Standard data format for API requests and responses. |

## Usage Examples

### Fetching Available Question Types

You can retrieve a list of all supported question types with a simple `POST` request.

```bash
curl -X POST https://your-domain.com/dashboard/question_types.php
```

### Creating a Full Survey

Create a new survey, complete with sections and questions, by sending a JSON payload.

```bash
curl -X POST https://your-domain.com/dashboard/create_survey.php \
-H "Content-Type: application/json" \
-b "PHPSESSID=your_session_id" \
-d '{
    "survey_type": "questionnaire",
    "title": "Customer Feedback Survey",
    "description": "Please share your feedback on our services.",
    "status": "draft",
    "sections": [
        {
            "title": "Section 1: Your Experience",
            "order_number": 1,
            "questions": [
                {
                    "question_text": "How would you rate our service?",
                    "question_type_id": 5,
                    "order_number": 1,
                    "is_required": true,
                    "options": { "min": 1, "max": 5, "label_min": "Poor", "label_max": "Excellent" }
                },
                {
                    "question_text": "Any additional comments?",
                    "question_type_id": 2,
                    "order_number": 2,
                    "is_required": false,
                    "placeholder": "Type your comments here..."
                }
            ]
        }
    ]
}'
```

---

# Survey Management API

## Overview
This is a PHP-based backend API for creating and managing dynamic surveys, polls, and questionnaires. It leverages a MySQL database via PDO for data persistence and provides a structured system for handling complex survey structures including sections and various question types.

## Getting Started
### Environment Variables
This project requires a `db_connect.php` file in the parent directory. This file must establish a PDO or MySQLi connection and should use environment variables for security. Create a configuration file or set the following server variables:

- `DB_HOST`: The database server hostname. Example: `localhost`
- `DB_NAME`: The name of the database. Example: `survey_db`
- `DB_USER`: The database user. Example: `root`
- `DB_PASS`: The database user's password. Example: `your_secure_password`

## API Documentation
### Base URL
`/`

### Endpoints
#### POST /dashboard/create_survey.php
Creates a new survey along with its associated sections and questions in a single transaction. This endpoint requires an active user session for authentication.

**Request**:
The request body must be a JSON object containing the survey details.

*   **Top-Level Fields:**
    *   `survey_type` (string, **required**): Type of survey. Must be one of `poll`, `questionnaire`, `quiz`, `form`.
    *   `title` (string, **required**): The main title of the survey.
    *   `description` (string, optional): A brief description of the survey.
    *   `category_id` (integer, optional): The ID of an existing category.
    *   `status` (string, optional): The initial status. Default: `draft`. Can be `draft`, `published`, `archived`.
    *   `require_login` (boolean, optional): If true, respondents must be logged in. Default: `false`.
    *   `max_submissions` (integer, optional): The maximum number of responses allowed.
    *   `start_date` (string, optional): Survey start date in `YYYY-MM-DD HH:MM:SS` format.
    *   `end_date` (string, optional): Survey end date in `YYYY-MM-DD HH:MM:SS` format.
    *   `allow_multiple_responses` (boolean, optional): If true, allows a single user to submit multiple times. Default: `false`.
    *   `custom_url` (string, optional): A unique custom URL slug for the survey.
    *   ...*(and many other optional customization fields as seen in the source code)*
*   **`sections`** (array, optional): An array of section objects.
    *   `title` (string, **required**): The title of the section.
    *   `description` (string, optional): A description for the section.
    *   `order_number` (integer, optional): The display order of the section.
    *   **`questions`** (array, optional): An array of question objects within the section.
        *   `question_text` (string, **required**): The text of the question.
        *   `question_type_id` (integer, **required**): The ID corresponding to a type in the `question_types` table.
        *   `order_number` (integer, optional): The display order of the question.
        *   `is_required` (boolean, optional): Whether the question must be answered. Default: `false`.
        *   `options` (object, optional): A JSON object containing options for question types like multiple-choice, dropdown, etc. Example: `{"choices": ["A", "B", "C"]}`.
        *   `placeholder` (string, optional): Placeholder text for input fields.

_Payload Example:_
```json
{
  "survey_type": "questionnaire",
  "title": "New Product Feedback",
  "description": "Share your thoughts on our latest product.",
  "category_id": 3,
  "status": "published",
  "start_date": "2024-01-01 00:00:00",
  "sections": [
    {
      "title": "User Experience",
      "order_number": 1,
      "questions": [
        {
          "question_text": "How easy was it to use the new product?",
          "question_type_id": 5,
          "order_number": 1,
          "is_required": true,
          "options": {
            "min": 1,
            "max": 5,
            "label_min": "Very Difficult",
            "label_max": "Very Easy"
          }
        },
        {
          "question_text": "What feature did you like the most?",
          "question_type_id": 1,
          "order_number": 2,
          "is_required": false
        }
      ]
    }
  ]
}
```

**Response**:
_Success (200 OK):_
```json
{
  "status": "success",
  "message": "Survey, sections, and questions created successfully",
  "survey_id": 123,
  "section_ids": [
    45
  ]
}
```

**Errors**:
- `400 Bad Request`: A required field is missing or a value is invalid (e.g., `survey_type` is not one of the allowed values, `question_type_id` does not exist). The response message will specify the error.
- `401 Unauthorized`: The user is not logged in. An active session is required to create a survey.
- `500 Internal Server Error`: A database error occurred during the transaction. The transaction is rolled back, and no data is saved.

---
#### POST /dashboard/question_types.php
Retrieves a list of all available question types from the database. This endpoint does not require authentication.

**Request**:
The request body is empty.

**Response**:
_Success (200 OK):_
```json
{
  "status": "success",
  "data": [
    {
      "id": "1",
      "type_name": "Short Text",
      "description": "A single-line text input."
    },
    {
      "id": "2",
      "type_name": "Paragraph",
      "description": "A multi-line text input."
    },
    {
      "id": "3",
      "type_name": "Multiple Choice",
      "description": "Select one option from a list."
    }
  ]
}
```

**Errors**:
- `500 Internal Server Error`: The server failed to connect to the database or execute the query.

## License
This project is licensed under the MIT License.

## Author

Connect with the project author for questions or collaboration.

- **Twitter**: [@your_twitter_handle](https://twitter.com/your_twitter_handle)
- **LinkedIn**: [your_linkedin_profile](https://linkedin.com/in/your_linkedin_profile)

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge"/>
  <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Badge"/>
</p>

[![Readme was generated by Dokugen](https://img.shields.io/badge/Readme%20was%20generated%20by-Dokugen-brightgreen)](https://www.npmjs.com/package/dokugen)