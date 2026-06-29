[README.md](https://github.com/user-attachments/files/29482186/README.md)
# VIDA OS

VIDA OS is the WordPress-based operating layer for Vida Maxima ministry tools.

The first official release focuses on the Radiografia de Formacion MVP: a frontend assessment that sends evaluation data to a WordPress plugin through a REST endpoint.

## Objective

VIDA OS centralizes people and assessment records so future pastoral tools can work from a shared source of truth.

## Requirements

- WordPress 7.0 or later.
- PHP 8.3 or later.
- WordPress REST API enabled.
- Browser access to the Radiografia frontend.

## Installation

1. Upload `releases/vida-os-v0.1.0.zip` through the WordPress Plugins screen.
2. Install and activate the plugin.
3. Confirm that the plugin creates its database tables during activation.
4. Place or serve `frontend/radiografia/index.html` from the same WordPress site, or from an environment that can reach `/wp-json/vida-os/v1/assessment`.

## Activation

When the plugin activates, it creates the Vida OS database tables using the active WordPress table prefix:

- `vidaos_people`
- `vidaos_assessments`

The plugin also stores the database schema version in the `vidaos_db_version` option.

## Frontend Usage

Open `frontend/radiografia/index.html`, complete the initial identity fields, answer the assessment, and submit the final form.

The frontend sends a POST request to:

```text
/wp-json/vida-os/v1/assessment
```

The payload includes the email, display name, evaluation period, and complete assessment responses.

## Architecture

- `plugin/vida-os`: WordPress plugin source.
- `plugin/vida-os/includes`: Plugin bootstrap helpers, activation, deactivation, and admin shell.
- `plugin/vida-os/modules/isd`: ISD assessment service and REST controller.
- `frontend/radiografia`: Standalone Radiografia de Formacion frontend.
- `docs`: Release documentation and validation notes.
- `releases`: Official v0.1.0 ZIP artifacts.

## License

License placeholder. Final license to be defined by Vida Maxima.
