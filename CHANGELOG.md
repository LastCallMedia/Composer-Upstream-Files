# Changelog
All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased
## Changed
- Fix undefined index warning parsing origin URL in ManifestFactory.

## 1.1.0
### Added
- Allow referencing manifests - JSON files containing lists of files.
- Allow exclusion of files from referenced manifests.  Files can be excluded by source or destination.

### Changed
- Ensure a directory exists before trying to download to it.

## 1.0.0
### Added
- Initial release
