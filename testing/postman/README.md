# Postman Testing Artifacts

This folder stores Postman testing assets for this project.

## Structure

- `collections/`: exported Postman collections (`*.collection.json`)
- `environments/`: exported environments (`*.environment.json`)
- `runs/`: optional run reports (JSON/HTML screenshots, etc.)

## How To Save Postman Tabs

Postman tabs are temporary until you save them as requests.

1. In each tab, click `Save` and store the request in a collection (for example `Backend Library API`).
2. Export that collection (v2.1) and save it in `testing/postman/collections/`.
3. Export your environment and save:
   - versionable file (without secrets) in `testing/postman/environments/`
   - local file with secrets as `*.local.json` (ignored by Git)
4. If you run a collection, save report artifacts in `testing/postman/runs/`.

## Recommended Naming

- `collections/backend-library.collection.json`
- `environments/backend-library.environment.json`
- `environments/backend-library.local.json` (ignored)
- `runs/2026-03-10-smoke-test.json`
