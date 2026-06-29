=== Vida OS ===
Contributors: vidaos
Tags: admin, dashboard, rest-api
Requires at least: 7.0
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Vida OS provides the WordPress backend for the Radiografia de Formacion MVP.

== Description ==

Vida OS v0.1.0 includes the base plugin architecture, database schema, assessment storage service, and the first REST endpoint for assessment submissions.

Endpoint:

POST /wp-json/vida-os/v1/assessment

== Installation ==

1. Upload the `vida-os` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the WordPress Plugins screen.
3. Submit assessments from the Radiografia frontend.

== Changelog ==

= 0.1.0 =
* Adds the base plugin architecture.
* Adds database tables for people and assessments.
* Adds versioned database activation.
* Adds the assessment storage service.
* Adds the assessment REST endpoint.
