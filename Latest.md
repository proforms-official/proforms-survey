Items with * are the compulsored fields
/dashboard/create_survey.php (maximized)
```json
{
  "survey_type *": "questionnaire",//poll
  "title *": "Customer Feedback Survey",
  "description *": "Share your thoughts on our services.",
  "category_id": 3, // Not necessary now
  "status *": "draft",//default is draft
  "header_media_type": "image",
  "header_media": "https://example.com/image.jpg",
  "font_family": "Arial",
  "font_size": 16,
  "primary_color": "#000000",
  "secondary_color": "#FFFFFF",
  "active_color": "#0000FF",
  "dark_theme": false,
  "background_type": "gradient",
  "background_media": "https://example.com/gradient.jpg",
  "background_media_input_type": "url",
  "require_login": false,
  "notifications_range": "weekly",
  "max_submissions": 1000,
  "proshield_enabled": true,
  "proshield_degree": "medium",
  "whatsapp_notifications": true,
  "whatsapp_number": "+2341234567890",
  "email_notifications": true,
  "popup_notifications": false,
  "meta_title": "Customer Feedback",
  "meta_description": "Provide feedback on our services.",
  "start_date": "2025-09-20 00:00:00",
  "end_date": "2025-12-31 23:59:59",
  "time_limit": 600,
  "response_anonymity": true,
  "allow_multiple_responses": false,
  "response_editable": false,
  "language": "en",
  "translation_enabled": false,
  "custom_url": "customer-feedback-2025",
  "access_code": "ABC123",
  "ip_restriction": false,
  "geo_restriction": "NG",
  "thank_you_message": "Thanks for your feedback!",
  "thank_you_redirect_url": "https://example.com/thank-you",
  "progress_bar_enabled": true,
  "question_randomization": false,
  "response_export_format": "csv",
  "analytics_enabled": true,
  "recaptcha_enabled": false,
  "custom_css": "body { background: #f0f0f0; }",
  "custom_js": "console.log('Survey loaded');",
  "survey_password": "secure123",
  "response_quota_by_group": {"group1": 100, "group2": 200},
  "embed_enabled": true,
  "embed_code": "<iframe src='https://example.com/survey'></iframe>",
  "sms_notifications": false,
  "sms_number": null,
  "conditional_logic_enabled": false,
  "data_retention_period": 365,
  "isopen": true,
  "sections": [
    {
      "title *": "Experience Feedback",
      "description *": "Tell us about your experience.",
      "order_number *": 1, // this is to know the arrangement of the sections, should be draggeble on thr fronetned
      "is_active": true,
      "media_type": "image",
      "media_url": "https://example.com/section-image.jpg",
      "questions": [
        {
          "question_text" *: "How would you rate our service?",
          "question_uuid" *: "550e8400-e29b-41d4-a716-446655440000",
          "order_number": 1,
          "is_required": true,
          "options": {"min": 1, "max": 5, "label_min": "Poor", "label_max": "Excellent"},
          "min_value": 1,
          "max_value": 5,
          "media_type": "none",
          "media_url": null,
          "placeholder": "Rate from 1 to 5",
          "validation_rule": "numeric"
        },
        {
          "question_text": "Any additional comments?",
          "question_uuid": "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
          "order_number": 2,
          "is_required": false,
          "placeholder": "Type your comments here..."
        }
      ]
    }
  ]
}
```
minimised version
```json
{
  "survey_type": "poll",
  "title": "Quick Feedback",
  "sections": [
    {
      "title": "Feedback Section",
      "questions": [
        {
          "question_text": "How’s the vibe?",
          "question_uuid": "550e8400-e29b-41d4-a716-446655440000"
        }
      ]
    }
  ]
}
```
