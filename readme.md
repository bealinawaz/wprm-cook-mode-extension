# WP Recipe Maker Cook Mode Extension

**Contributors:** Ali Nawaz
**Tags:** wp recipe maker, recipe, cook mode, screen wake lock, no sleep, cooking
**Requires at least:** 5.0
**Tested up to:** 6.3
**Requires PHP:** 7.4
**Stable tag:** 1.0.3
**License:** GPL v2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

This extension adds a "Cook Mode" toggle to your WP Recipe Maker recipe cards, preventing your users' screens from turning off while they're following a recipe.

---

## Description

Do your visitors ever complain about their screen going dark while they're following one of your amazing recipes? This simple yet powerful plugin solves that exact problem for you.

The **WP Recipe Maker Cook Mode Extension** seamlessly adds a convenient toggle switch directly to your recipe cards. When a user activates "Cook Mode," the plugin uses the modern **Screen Wake Lock API** to keep their screen awake, allowing them to follow along with the recipe without constantly tapping their device.

This is a must-have for anyone who wants to improve the user experience on their recipe blog.

### Key Features:

* **Effortless Integration:** Automatically adds a "Cook Mode" toggle to all your WP Recipe Maker recipe cards.
* **Prevent Screen Timeout:** Keeps your visitors' screens on and active as long as the toggle is enabled.
* **Customizable Settings:** Easily change the toggle's label, description, and position (top or bottom of the recipe card) from the WordPress admin panel.
* **Lightweight & Efficient:** The plugin is small and only runs on pages that contain a WP Recipe Maker recipe, so it won't slow down your website.
* **Modern API:** Utilizes the Screen Wake Lock API for broad compatibility with modern browsers like Chrome, Edge, and Safari.

---

## Installation

1. Upload the `wprm-cook-mode-extension` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure you have the main **WP Recipe Maker** plugin installed and activated, as this is a required dependency.
4. Optionally, configure the settings by navigating to **Settings > WPRM Cook Mode** in your WordPress dashboard.

---

## Frequently Asked Questions

### What are the requirements for this plugin to work?

This plugin requires the **WP Recipe Maker** plugin to be installed and active on your website. It's designed to be a companion extension to that plugin.

### How does "Cook Mode" work?

It uses the **Screen Wake Lock API**, which is a feature built into most modern web browsers. When activated, it requests permission from the browser to keep the screen awake. This prevents the user's device from going into sleep mode or dimming the screen.

### Is this feature supported on all browsers?

The Screen Wake Lock API has wide support on most modern browsers, including:
* Google Chrome (84+)
* Microsoft Edge (84+)
* Safari (16.4+)
* Firefox (limited or no support)

If the feature isn't supported, the toggle will simply not function, and the user's device will behave normally.

### Can I change the text on the "Cook Mode" toggle?

Yes! Go to **Settings > WPRM Cook Mode** in your WordPress admin panel. You can easily customize the label and description text to fit your needs.

---

## Changelog

**1.0.0 (August 26, 2025)**
* Initial stable release.
* **New:** You can now customize the label, description, toggle color and position of the toggle.
* **Improved:** Better compatibility with various WP Recipe Maker templates.
* **Added:** A dedicated admin settings page and a quick link to settings from the plugins page.
