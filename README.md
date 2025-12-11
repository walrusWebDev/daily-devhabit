# Daily Dev Habit: Content Prompter

**Contributors:** Lauren Bridges

**Tags:** developer tools, rest api, cloud integration, daily log, productivity, journal

**Requires at least:** 6.0

**Tested up to:** 6.7

**Requires PHP:** 8.0+

**Stable tag:** 0.2.0

**License:** GPLv2 or later

A developer-focused productivity engine that leverages guided prompting and REST API integration to sync daily engineering logs to the cloud.

## Description

**Daily Dev Habit** is a WordPress plugin designed to solve the "documentation friction" problem. It replaces the blank page with a guided workflow, helping developers, creators, and engineers document their progress, blockers, and wins.

Unlike standard journaling plugins that trap data inside the WordPress database, Daily Dev Habit is built with a **Cloud-First** architecture. It offers dual-mode integration: users can strictly manage logs locally (clipboard/text) or authenticate with a remote endpoint to push structured log data to a centralized repository.

### Key Features
* **Guided Prompting:** A structured interface that reduces cognitive load when documenting daily work.
* **Cloud API Sync:** (New in v0.2.0) A built-in connector that pushes JSON-formatted log entries to a configured external REST API endpoint.
* **Privacy & Compliance:** Includes a lightweight, zero-dependency Cookie Consent module to ensure GDPR/CCPA compliance for user data.
* **Developer-Centric UI:** Clean, distraction-free interface built to fit into a technical workflow.

## Architecture & Engineering
*Designed with extensibility and data portability in mind.*

* **REST API Integration:** The plugin utilizes `wp_remote_post()` with robust error handling to manage communication between the local WordPress environment and the remote cloud database.
* **Data Security:** All API transactions are secured using WordPress nonces and sanitized input validation to prevent XSS and unauthorized data injection.
* **Modular Design:** The Cookie Consent functionality is compartmentalized, allowing it to function independently of the logging logic.

## Installation

1.  Upload the `daily-dev-habit` folder to your `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Daily Dev Habit** in the admin menu.
4.  *(Optional)* Go to the Settings tab to configure your Cloud API Endpoint and Authorization Key.

## Roadmap

* **v0.3.0:** Custom Block Integration (Replacing the current form UI with native Gutenberg blocks).
* **v0.4.0:** Java Bot Integration (Server-side analysis of log sentiment and productivity trends).
* **Future:** Visualization dashboard for "Streak" tracking and productivity metrics.

## Frequently Asked Questions

**Is an API key required to use the plugin?**
No. The plugin functions fully as a local prompting tool. The API key is only required if you wish to sync your data to the cloud.

**Where is the data stored?**
If using "Local Mode," data is generated for your clipboard/local use. If "Cloud Mode" is active, data is pushed to your configured endpoint.

## Changelog

**Version 0.2.0**
* **Architecture Change:** Renamed plugin to `daily-dev-habit`.
* **New Feature:** Added REST API integration for cloud syncing.
* **New Feature:** Integrated lightweight Cookie Consent banner.
* **Update:** Refactored codebase for PHP 8.0+ compatibility.

**Version 0.1.0**
* Initial release.
* Added admin page with guided questionnaire.
* Added "Copy to Clipboard" functionality.

## Acknowledgments
Development, architecture, and project lead: **Lauren Bridges**.
*This project utilizes AI-assisted coding workflows (Google Gemini) for boilerplate generation and documentation support.*
