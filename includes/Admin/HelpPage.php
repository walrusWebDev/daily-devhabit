<?php
namespace DailyDevHabit\Admin;

class HelpPage {

    public function __construct() {
        // Priority 20 ensures we run AFTER the parent menu is created
        add_action( 'admin_menu', [ $this, 'register_menu_page' ], 20 );
    }

    public function register_menu_page() {
        add_submenu_page(
            'ddh-dev-log',       // <--- FIXED: Matches your main daily-devhabit.php slug
            'Help & Documentation',
            'Help',
            'manage_options',
            'ddh-help',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        ?>
        <div class="wrap">
            <h1>Daily Dev Habit: Documentation</h1>
            
            <div class="ddh-help-container" style="max-width: 800px; background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                
                <h2 style="border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;">Option A: GitHub Mode (Standalone)</h2>
                <p>This mode allows you to save logs directly to your own private repository. <strong>No DDH account required.</strong></p>
                
                <h3>How to create a GitHub PAT (Personal Access Token)</h3>
                <ol>
                    <li>Log in to your GitHub account.</li>
                    <li>Go to <strong>Settings</strong> -> <strong>Developer settings</strong> -> <strong>Personal access tokens</strong> -> <strong>Tokens (classic)</strong>.</li>
                    <li>Click <strong>Generate new token (classic)</strong>.</li>
                    <li>Give it a Note (e.g., "DDH WordPress").</li>
                    <li><strong>Important:</strong> Check the <code>repo</code> scope (this allows the plugin to write to your repository).</li>
                    <li>Click "Generate token" and copy the string (starts with <code>ghp_</code>).</li>
                    <li>Paste this token into the <a href="<?php echo admin_url('admin.php?page=ddh-log-settings'); ?>">Settings</a> page along with your username and repo name.</li>
                </ol>

                <hr style="margin: 30px 0;">

                <h2 style="border-bottom: 2px solid #f0f0f1; padding-bottom: 10px;">Option B: Cloud Mode (CLI Sync)</h2>
                <p>Connects your WordPress logs to the Daily Dev Habit ecosystem for centralized tracking across multiple projects.</p>

                <h3>How to get your Cloud Token</h3>
                <ol>
                    <li>Install the CLI tool on your computer:
                        <code style="display:block; margin: 10px 0; background: #f0f0f1; padding: 10px;">npm install -g @walruswebdev/daily-devhabit-cli</code>
                    </li>
                    <li>Login to your account:
                        <code style="display:block; margin: 10px 0; background: #f0f0f1; padding: 10px;">ddh login</code>
                    </li>
                    <li>Reveal your token:
                        <code style="display:block; margin: 10px 0; background: #f0f0f1; padding: 10px;">ddh token</code>
                    </li>
                    <li>Copy the token string and paste it into the <a href="<?php echo admin_url('admin.php?page=ddh-log-settings'); ?>">Settings</a> page.</li>
                </ol>

                <hr style="margin: 30px 0;">

                <h3>Frequently Asked Questions</h3>
                
                <h4>How do I download my logs?</h4>
                <p><em>Coming Soon:</em> We are building an export feature in the CLI (<code>ddh export</code>) that will allow you to download all cloud logs as a JSON or CSV file.</p>

                <h4>How do I change my Cloud password?</h4>
                <p>Currently, password resets must be handled via the CLI or by contacting support. A self-service dashboard is planned for v1.2.</p>
            </div>
        </div>
        <?php
    }
}