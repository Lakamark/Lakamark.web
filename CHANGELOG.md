# Changelog

All notable changes to this project will be documented in this file.

## 4.0.0 - Application Architecture Stabilization
### Added
- Fluent bootstrap API via `AppRunner`
- `AppContextFactory` for dynamic context generation (Turbo support)
- `MODULE_NAMES` registry for typed module identifiers
- `attachKernelToWindow` helper for global kernel management
- Modular Theme system (Manager, Applier, Storage, Resolver)
- `ThemeModule` and `ThemeSwitcherElement` custom element

### Changed
- Refactored `TurboKernel` to use a context factory instead of static context
- Application now mounts via `turbo:load` instead of immediate boot
- Improved module lifecycle handling (`mount` / `destroy`)
- Updated modules to use `AppContext` instead of static config
- Refactored application entry point to a cleaner, fluent structure

### Fixed
- Fixed stale context after Turbo navigation
- Fixed duplicate mount on initial load
- Fixed modules not re-mounting after Turbo page transitions
- Fixed custom elements initialization timing issues
- Fixed inconsistent module lifecycle state

### Breaking Changes
- `TurboKernel` now requires `AppContextFactory` instead of `AppContext`
- Initial app mount is now triggered by `turbo:load`
- Modules must implement `onMount(context: AppContext)`
- Static context usage is no longer supported

### Internal
- Stabilized application lifecycle from bootstrap to modules
- Improved separation of concerns (kernel / modules / UI)
- Architecture is now fully Turbo-compatible and extensible

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