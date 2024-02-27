# CHANGELOG

## 1.4.0

- Installing nodejs via github action and not bash (it uses the runner cache and is much faster/more maintainable)

## 1.3.0

- Now the action is able to comment back on PR if the scan is executed inside a PR

## 1.2.0

- Using actions/checkout@v3 instead of v2

## 1.1.0

- Added SONAR_EXTRA_FLAGS
- Added SKIP_CHECKOUT
- Added SONAR_SOURCES
- Added CODE_REF: It allows you to pull arbitrary tags/sha from the repo

## 1.0.0

- First release
