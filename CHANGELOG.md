# Changelog

All notable changes to this project will be documented in this file.

## 1.2.11 - 2026-03-05

### Added
- Admin configuration field `Request Exclusion Regex (one per line)` at `System -> Configuration -> CleanTalk -> Anti-Spam`.
- Default exclusion patterns for checkout JSON endpoints:
  - `^/opc/json/`
  - `^/[^/]+/opc/json/`

### Changed
- Added centralized request exclusion matching in `Cleantalk_Antispam_Model_Api::isRequestExcludedByRegexConfig()`.
- Skip CleanTalk checks early when the current `REQUEST_URI` matches any configured exclusion pattern.
- Support both fully delimited regex patterns and plain regex bodies in exclusion config.
- Added debug logging when an exclusion pattern matches.
