# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-12-19

### Added

- **Dark Mode Support**: Full dark mode support for all admin panel components and views
- **Improved Uninstallation**: Enhanced uninstall process that properly clears sidebar menu items and restores default configurations

### Improved

- **Route Removal**: Route removal process now completes without errors, ensuring clean uninstallation
- **Sidebar Menu Clearing**: Uninstall command now properly clears all sidebar menu items added during installation
- **Default Restoration**: Fortify `CreateNewUser.php` file is now restored to default state during uninstallation, removing any custom role attachments

### Changed

- **Backward Compatibility**: This release maintains full backward compatibility with previous versions while introducing new features and improvements

## [1.0.5] - 2024-12-19

### Improved

- **Sidebar Menu Installation**: The installation command now correctly finds and inserts the sidebar menu after the Platform `navlist.group` closing tag instead of after the Dashboard menu item. This provides more reliable insertion point detection.
- **Sidebar Menu Uninstallation**: The uninstall command has been enhanced to read exact patterns from the stub file (`sidebar-menu-to-add.blade.php`) for more accurate removal of menu items. This ensures all menu items are properly removed during uninstallation.
- **Pattern Matching**: Improved pattern matching logic for sidebar menu injection with better handling of whitespace variations and formatting differences.

### Changed

- Sidebar menu insertion point changed from "after Dashboard menu item" to "after Platform navlist.group closing tag"
- Uninstall command now uses a two-pass approach: first identifies lines to remove, then builds new content without removed lines

### Fixed

- Fixed sidebar menu injection to work correctly with the Platform group structure
- Improved accuracy of sidebar menu removal during uninstallation

## [1.0.4] - Previous Release

[Previous release notes...]

