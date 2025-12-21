# Daily Dev Habit: Content Prompter

**Contributors:** Lauren Bridges

**Tags:** developer tools, rest api, cloud integration, daily log, productivity, journal

**Requires at least:** 6.0

**Tested up to:** 6.7

**Requires PHP:** 8.0+

**Stable tag:** 1.1.0

**License:** GPLv2 or later

A developer-focused productivity engine that connects your WordPress dashboard to your engineering workflow via GitHub or Cloud API.

## Description

**Daily Dev Habit** is a WordPress plugin designed to solve the "documentation friction" problem. It replaces the blank page with a guided workflow, helping developers, creators, and engineers document their progress, blockers, and wins without breaking flow.

**New in 1.1.0: Two Ways to Save**
1.  **GitHub Mode (Self-Hosted):** The plugin commits your log entries directly to a private GitHub repository of your choice. You own your data 100%.
2.  **Cloud Mode (Managed):** Connects to the Daily Dev Habit Cloud API for centralized storage and future analytics/dashboards.

### Key Features
* **Guided Prompting:** A structured interface (Scope, Summary, Blockers) that reduces cognitive load.
* **Save to GitHub:** Automatically creates a Markdown file in your repo for every entry.
* **Save to Cloud:** A built-in connector for the DDH Cloud ecosystem.
* **Distraction-Free UI:** Built to feel like a native part of your engineering toolset.

## The Ecosystem
This plugin is part of the **Daily Dev Habit** suite, which includes:
* **The WP Plugin:** An Admin form for Daily Standup.
* **The CLI Tool:** A terminal command (`ddh log`) for quick engineering updates right from your code editor.

## Installation

1.  Upload the `daily-devhabit` folder to your `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Daily Dev Log** in the admin menu.
4.  **Configure your Connection:**
    * Go to the **Journal Settings** submenu.
    * Select **Mode:** "GitHub" or "DDH Cloud".
    * **For GitHub:** Enter your Username, Repo Name, and a Personal Access Token (PAT).
    * **For Cloud:** Enter your account JWT (available via the CLI).

## Frequently Asked Questions

**Is an API key required?**
Yes, but you have options. For "GitHub Mode," you use a standard GitHub Personal Access Token (PAT). For "Cloud Mode," you use your DDH account token.

**Where is the data stored?**
* **GitHub Mode:** In your own private repository as `.md` files.
* **Cloud Mode:** Encrypted in the Daily Dev Habit database.
* **Note:** The plugin does *not* store logs in your WordPress database to keep your site lightweight.

## Screenshots

1.  **The Journal Interface:** Clean prompts to help you document your work.
2.  **Settings Panel:** Easily toggle between GitHub and Cloud storage.

## Changelog

**Version 1.1.0**
* **Major Update:** Added "GitHub Mode" to save logs directly to a repository.
* **New Feature:** Added "Cloud Mode" for DDH API integration.
* **UI Polish:** Updated settings screen to handle dynamic fields.
* **Fix:** Removed local log storage in favor of remote APIs.

**Version 0.2.0**
* Added REST API integration basics.
* Refactored for PHP 8.0+.

## Acknowledgments
Development, architecture, and project lead: **Lauren Bridges**.
>>>>>>> dev
