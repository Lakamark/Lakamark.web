# Changelog

All notable changes to this project will be documented in this file.

## v3.0.0 - Design System Refactor

### Added
- New design system based on tokens (colors, spacing, typography)
- Utility-first layer (spacing, layout) generated with Sass
- Improved component consistency (cards, carousel, header)

### Changed
- Full redesign of the UI (mobile-first)
- Updated spacing scale (4/8 system)
- Refactored Sass architecture (settings, base, utilities, components)
- Reworked Vite integration for production (TS entries support)

### Fixed
- CSS not loading in production due to incorrect entry mapping (.js → .ts)
- Manifest resolution issues in AssetBridge

### Removed
- Legacy Sass structure and deprecated variables
- Old design tokens and unused styles

---

## 2.0.0

### Breaking changes

- Introduced FrontendKernel architecture
- Added module lifecycle system
- Introduced AbstractModule base class
- Added AppRunner module orchestration
- Refactored frontend modules

### Improvements

- Improved Turbo lifecycle integration
- Migrated Vite config to TypeScript
- Cleaner frontend structure
- Web components organized per module

---

## 1.0.0

Initial release.