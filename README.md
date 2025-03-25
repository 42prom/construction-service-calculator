=== Construction Service Cost Calculator ===
Contributors: Mikheili Nakeuri
Tags: calculator, construction, cost calculator, service calculator, estimate
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A comprehensive cost calculator for construction and renovation services with real-time calculations and no registration required.

== Description ==

The Construction Service Cost Calculator allows clients to estimate the cost of various construction and renovation services instantly without any registration. This plugin provides a flexible, user-friendly interface for both clients and administrators.

### Key Features

**For Clients:**
* Select from multiple construction services (tile installation, wall plastering, flooring, painting, etc.)
* Calculate costs based on area measurements and service rates in real-time
* Support for multiple unit types (square meters, square feet, hours, items)
* Display proper unit symbols throughout the interface (m², ft², hr, pc)
* Calculate subtotals, tax, and grand totals instantly as selections are made
* Submit estimates as inquiries without registration

**For Administrators:**
* Manage services including rates, descriptions, and SVG icons
* Organize services into customizable categories
* Configure units of measurement with proper symbols
* Set currency, tax rates, and other global options
* Review and respond to client submissions
* Generate HTML estimates for sharing
* Import/export services via CSV
* View analytics on popular services and submissions

### Shortcode Usage

Basic usage:
`[construction_calculator]`

With options:
`[construction_calculator title="Renovation Calculator" category="flooring" theme="blue" columns="2"]`

Available attributes:
* title - Custom calculator title
* description - Custom description text
* category - Display services from a specific category only
* services - Display specific services only (comma-separated IDs)
* theme - Color theme (default, blue, orange, dark)
* show_contact_form - Show or hide the contact form (yes/no)
* columns - Number of columns for service display (1-4)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/construction-service-calculator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure your services, categories, and settings through the 'Construction Calculator' menu in the admin area.
4. Use the shortcode `[construction_calculator]` to display the calculator on any page or post.

== Frequently Asked Questions ==

= How do I add a new service? =

Go to Construction Calculator > Services > Add New. Enter the service name, rate, unit type, and category. You can also add an SVG icon and other optional details.

= Can I customize the calculator appearance? =

Yes, you can choose from multiple color themes (default, blue, orange, dark) and customize many aspects through the Settings page. You can also use shortcode parameters to control appearance.

= Does this plugin support multiple currencies? =

Yes, you can set the currency symbol, position, and formatting options in the Settings page.

= Can I export my services data? =

Yes, go to Construction Calculator > Tools to export your services as a CSV file. You can also import services from CSV.

== Screenshots ==

1. Front-end calculator interface
2. Admin dashboard
3. Service management
4. Settings page
5. Submissions management

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Credits ==

Plugin developed by Mikheili Nakeuri.
