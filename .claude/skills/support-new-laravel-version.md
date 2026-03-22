---
description: Add support for a new Laravel version to this package. Updates composer.json, CI matrix, and documentation.
user_invocable: true
---

# Support New Laravel Version

Add support for a new major Laravel version to this package. The user may specify the Laravel version as an argument (e.g., `/support-new-laravel-version 14`), or you should detect it from context.

## Steps

### 1. Determine versions

- **Laravel version**: From the user's argument or prompt (e.g., `14` means `^14.0`)
- **Testbench version**: Orchestra Testbench major version = Laravel major version - 2 (e.g., Laravel 13 → Testbench 11, Laravel 14 → Testbench 12)
- Read `composer.json` and `.github/workflows/tests.yml` to confirm current supported versions

### 2. Update `composer.json`

Add the new version constraint to these dependencies:

- `require.illuminate/support`: append `|^XX.0`
- `require.illuminate/contracts`: append `|^XX.0`
- `require-dev.orchestra/testbench`: append `|^YY.0` (where YY = Laravel version - 2)

### 3. Update `.github/workflows/tests.yml`

- Add the new Laravel version to the `matrix.laravel` array (e.g., `'^14.0'`)
- Add a new `include` entry mapping the Laravel version to its Testbench version:
  ```yaml
  - laravel: '^XX.0'
    testbench: '^YY.0'
  ```
- If the new Laravel version drops support for an older PHP version, add an `exclude` block for that combination

### 4. Update documentation

Update Laravel version references in:

- `CLAUDE.md`: the `**Laravel Version:**` line and the `## Package Dependencies` section
- `README.md`: the "Requires" section and the "Quality Standards" section

### 5. Verify

- Run `vendor/bin/phpunit` — all tests must pass
- Run `vendor/bin/phpstan analyse` — must report zero errors

### 6. Commit, tag, and release

- Commit all changes with message: `feat: add support for Laravel XX and Testbench YY`
- Determine the next tag by reading existing tags (`git tag --sort=-v:refname`) and bumping the minor version (e.g., `0.5.0` → `0.6.0`)
- Tag the commit
- Ask the user before pushing and creating the GitHub release
- When confirmed, push with `git push origin master --tags`
- Create a GitHub release with `gh release create` including a summary of dependency changes
