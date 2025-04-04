# GitHub Branch Protection Rules Setup Guide

## Overview

This guide explains how to set up branch protection rules in GitHub for the WordPress Content Sync project. These rules ensure code quality and maintain proper workflow practices.

## Accessing Branch Protection Settings

1. Navigate to your GitHub repository
2. Go to Settings > Branches
3. Under "Branch protection rules", click "Add rule"

## Protection Rules for Main Branch

### Basic Settings
- Branch name pattern: `main`
- Check "Require a pull request before merging"

### Pull Request Requirements
1. Required Approvals:
   - Set "Required number of approvals" to 1
   - Enable "Dismiss stale pull request approvals when new commits are pushed"
   - Enable "Require review from Code Owners"

2. Status Checks:
   - Enable "Require status checks to pass before merging"
   - Enable "Require branches to be up to date before merging"
   - Select relevant status checks (e.g., tests, linting)

3. Conversation Resolution:
   - Enable "Require conversation resolution before merging"

4. Push Protection:
   - Enable "Do not allow bypassing the above settings"
   - Disable "Allow force pushes"
   - Disable "Allow deletions"

## Protection Rules for Release Branch

### Basic Settings
- Branch name pattern: `release`
- Check "Require a pull request before merging"

### Pull Request Requirements
1. Required Approvals:
   - Set "Required number of approvals" to 2
   - Enable "Dismiss stale pull request approvals when new commits are pushed"
   - Enable "Require review from Code Owners"

2. Branch Restrictions:
   - Enable "Restrict who can push to matching branches"
   - Add only admin users/teams
   - Enable "Restrict which branches can be source to this branch"
   - Add `main` as the only allowed source branch

3. Status Checks:
   - Enable "Require status checks to pass before merging"
   - Enable "Require branches to be up to date before merging"
   - Select all relevant status checks

4. Additional Protection:
   - Enable "Require linear history"
   - Enable "Require deployments to succeed before merging"
   - Enable "Lock branch"
   - Enable "Do not allow bypassing the above settings"

## Additional Settings

### Required Status Checks
Configure the following status checks for both branches:
- Workflow runs (e.g., tests, builds)
- Code review tools
- Security scanning
- Deployment checks

### CODEOWNERS File
Create a `.github/CODEOWNERS` file:
```
# Global owners
* @team-lead @senior-devs

# Workflow files
.github/workflows/* @devops-team

# Documentation
docs/* @tech-writers @team-lead
```

## Verification Steps

After setting up protection rules:

1. Test Main Branch Protection:
   - Try direct push (should fail)
   - Create PR without review (should be blocked)
   - Create PR with failed checks (should be blocked)

2. Test Release Branch Protection:
   - Try direct push (should fail)
   - Create PR from non-main branch (should be blocked)
   - Create PR without required reviews (should be blocked)

## Emergency Procedures

In case of emergencies:

1. Only repository administrators can:
   - Temporarily disable specific rules
   - Force push if absolutely necessary
   - Bypass review requirements

2. Documentation Required:
   - Log all protection rule changes
   - Document reason for bypass
   - Create post-mortem report

## Monitoring and Maintenance

Regular tasks:

1. Monthly Review:
   - Check protection rules are intact
   - Review bypass logs
   - Update rules as needed

2. Access Control:
   - Review admin access regularly
   - Update CODEOWNERS file
   - Audit protection bypasses

## Common Issues and Solutions

1. **Cannot Push to Protected Branch**
   - Create a pull request
   - Get required reviews
   - Ensure checks pass

2. **Cannot Merge Pull Request**
   - Check required reviews
   - Update branch if behind
   - Resolve conversations
   - Wait for status checks

3. **Emergency Changes Needed**
   - Contact repository admin
   - Follow emergency procedures
   - Document all actions

## Contact Information

For questions or emergency access:
- Repository Administrators: [List admins]
- DevOps Team: [Contact details]
- Security Team: [Contact details] 