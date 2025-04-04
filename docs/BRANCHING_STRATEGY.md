# Git Branching Strategy and Workflow Guidelines

## Overview

This document outlines our Git branching strategy and workflow practices for the WordPress Content Sync project. Following these guidelines ensures consistent and reliable content synchronization while maintaining code quality.

## Branch Structure

We maintain two primary branches:

1. `main` - The source of truth
   - All changes must be made here first
   - Contains the latest development changes
   - Used for content synchronization and updates

2. `release` - The production-ready branch
   - Contains stable, tested code
   - Updated only through Pull Requests from `main`
   - Never receives direct commits

## Workflow Rules

### 1. Making Changes

- ✅ DO:
  - Make all changes in the `main` branch first
  - Create Pull Requests from `main` to `release`
  - Review all changes before merging to `release`

- ❌ DON'T:
  - Make direct commits to `release` branch
  - Create branches from `release`
  - Force push to either branch

### 2. Content Sync Workflow

Our automated WordPress content sync follows this process:

1. Workflow triggers on `main` branch
2. Downloads and processes content
3. Commits changes to `main` branch
4. Creates a Pull Request from `main` to `release`
5. Team reviews and merges the PR

### 3. Pull Request Guidelines

All PRs should:
- Have a clear title and description
- Include what content was updated
- Be reviewed by at least one team member
- Pass all automated checks

## Branch Protection

Both `main` and `release` branches are protected with the following rules:

1. `main` branch:
   - Requires pull request reviews
   - Must pass status checks
   - Up-to-date before merging

2. `release` branch:
   - No direct pushes allowed
   - Requires pull request reviews
   - Must pass status checks
   - Only accepts PRs from `main`

## Common Scenarios

### 1. Syncing WordPress Content

```bash
# Content sync workflow will:
1. Make changes in main
2. Create PR to release
3. Team reviews and merges
```

### 2. Handling Emergencies

If urgent changes are needed:
1. Still make changes in `main`
2. Create PR to `release`
3. Use expedited review process
4. Never bypass the PR process

## Best Practices

1. Keep branches synchronized regularly
2. Review PRs promptly
3. Don't let branches diverge for long periods
4. Always pull latest changes before starting work
5. Use meaningful commit messages

## Troubleshooting

### If branches become out of sync:

1. DO NOT force push to fix it
2. Create a sync PR from `main` to `release`
3. Resolve conflicts in the PR
4. Get team review before merging

### If workflow fails:

1. Check the workflow logs
2. Make fixes in `main` branch
3. Re-run the workflow
4. Create new PR if needed

## Questions?

For questions or clarifications about this branching strategy, please contact:
- Team Lead: [Name]
- DevOps Team: [Contact] 