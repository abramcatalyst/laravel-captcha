# Changelog

All notable changes to ⁠ laravel-captcha ⁠ will be documented in this file.

## [Unreleased] - Production Readiness Update

### Changed
•⁠  ⁠Replaced hardcoded configuration values with config file references
•⁠  ⁠Improved error handling throughout the package with proper exception handling
•⁠  ⁠Enhanced validation with input sanitization and better type checking
•⁠  ⁠Refactored image generation to remove code duplication
•⁠  ⁠Improved middleware with better error responses for JSON and web requests
•⁠  ⁠Enhanced ServiceProvider with translation loading and improved middleware registration

### Added
•⁠  ⁠Case sensitivity configuration option (`case_sensitive` in config)
•⁠  ⁠Environment variable support for configuration (`CAPTCHA_EXPIRES_MINUTES`, `CAPTCHA_MAX_ATTEMPTS`, `CAPTCHA_CASE_SENSITIVE`)
•⁠  ⁠Comprehensive error handling for image generation failures
•⁠  ⁠Logging support for validation failures and errors
•⁠  ⁠PHPUnit test suite with unit and feature tests
•⁠  ⁠PHPUnit configuration file (`phpunit.xml`)
•⁠  ⁠Composer test scripts
•⁠  ⁠Better translation support with publishable language files
•⁠  ⁠Improved HTTP headers for image responses (Cache-Control, Pragma, Expires)
•⁠  ⁠Better Carbon date handling with fallback for string dates

### Fixed
•⁠  ⁠Fixed duplicate image generation code in CaptchaController
•⁠  ⁠Fixed middleware registration for Laravel 9-12 compatibility
•⁠  ⁠Improved validation message handling with proper translations
•⁠  ⁠Fixed session data structure validation
•⁠  ⁠Fixed Carbon addMinutes() TypeError by casting config values to int
•⁠  ⁠Fixed documentation - clarified separate publish tags for config, views, and lang

### Security
•⁠  ⁠Added input sanitization in validation method
•⁠  ⁠Improved error handling to prevent information leakage
•⁠  ⁠Better exception handling for encrypted code decryption

### Documentation
•⁠  ⁠Updated README with production readiness notes
•⁠  ⁠Added configuration documentation for new options

### Testing
•⁠  ⁠Added PHPUnit test suite
•⁠  ⁠Added test coverage for validation, generation, and middleware
•⁠  ⁠Added tests for case sensitivity configuration

## [v0.1.0-beta] - 2025-10-25

### Added
•⁠  ⁠Initial beta release
•⁠  ⁠Math CAPTCHA support
•⁠  ⁠Word CAPTCHA support
•⁠  ⁠Image CAPTCHA support with GD
•⁠  ⁠Session-based validation
•⁠  ⁠Blade directive ⁠ @captcha ⁠
•⁠  ⁠Validation rule ⁠ captcha ⁠
•⁠  ⁠Middleware ⁠ captcha ⁠
•⁠  ⁠Configurable expiration and attempts
•⁠  ⁠Publishable config and views

### Notes
•⁠  ⁠Beta release - use in production at your own risk
•⁠  ⁠Image CAPTCHA requires GD extension
