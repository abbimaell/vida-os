# PROJECT

## Project

VIDA OS

## Version

v0.1.0

## Status

First official MVP release.

## Components

- WordPress plugin: `plugin/vida-os`.
- Frontend: `frontend/radiografia`.
- Release artifacts: `releases`.
- Documentation: `docs`.

## API

```text
POST /wp-json/vida-os/v1/assessment
```

## Payload Contract

```json
{
  "email": "persona@example.com",
  "display_name": "Nombre Persona",
  "evaluation_period": "2026-02",
  "responses": {}
}
```
