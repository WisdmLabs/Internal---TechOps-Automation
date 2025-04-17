# WordPress Updates Email Integration

This document outlines the approach for implementing email notifications for WordPress update reports.

## Overview
The system will send email notifications containing the WordPress updates report after each check, providing stakeholders with direct access to update information without needing to check GitHub.

## Implementation Options

### 1. GitHub Actions Native SMTP
```yaml
- name: Send Email Report
  if: always()  # Run even if previous steps fail
  uses: dawidd6/action-send-mail@v3
  with:
    server_address: ${{ secrets.SMTP_SERVER }}
    server_port: ${{ secrets.SMTP_PORT }}
    username: ${{ secrets.SMTP_USERNAME }}
    password: ${{ secrets.SMTP_PASSWORD }}
    subject: "WordPress Updates Report - ${{ steps.date.outputs.date }}"
    body: file://update_report.txt
    to: ${{ secrets.REPORT_RECIPIENTS }}
    from: WordPress Updates Checker
```

### 2. Email Service Integration
Alternative services that could be used:
- SendGrid
- AWS Simple Email Service (SES)
- Microsoft 365/Exchange Online
- Google Workspace

### Required Setup

1. **GitHub Secrets**
   - Add the following secrets to the repository:
     ```
     SMTP_SERVER
     SMTP_PORT
     SMTP_USERNAME
     SMTP_PASSWORD
     REPORT_RECIPIENTS
     ```

2. **Report Formatting**
   - Format the JSON report for email readability
   - Include summary section at the top
   - Highlight critical updates
   - Include links to GitHub issues/PRs

3. **Error Handling**
   - Implement retry mechanism for failed email attempts
   - Log email delivery status
   - Fallback notification method if email fails

## Example Implementation

```yaml
jobs:
  send-report:
    needs: check-updates
    runs-on: ubuntu-latest
    steps:
      - name: Format Report
        run: |
          node scripts/format-email-report.js

      - name: Send Report
        uses: dawidd6/action-send-mail@v3
        with:
          server_address: ${{ secrets.SMTP_SERVER }}
          server_port: ${{ secrets.SMTP_PORT }}
          username: ${{ secrets.SMTP_USERNAME }}
          password: ${{ secrets.SMTP_PASSWORD }}
          subject: "WordPress Updates Report - ${{ steps.date.outputs.date }}"
          body: file://formatted_report.html
          html_body: true
          to: ${{ secrets.REPORT_RECIPIENTS }}
          from: WordPress Updates Checker
          
      - name: Handle Email Failure
        if: failure()
        run: |
          echo "Email delivery failed. Creating GitHub issue for notification failure."
          # Create GitHub issue about email failure
```

## Best Practices

1. **Email Content**
   - Use HTML formatting for better readability
   - Include direct links to relevant resources
   - Provide action items if updates are needed

2. **Security**
   - Use environment secrets for all sensitive data
   - Implement email authentication (SPF, DKIM)
   - Validate email addresses

3. **Maintenance**
   - Regular testing of email delivery
   - Monitor bounce rates and delivery issues
   - Keep email templates updated

## Future Enhancements

1. **Customizable Notifications**
   - Allow subscribers to choose update types
   - Configure notification frequency
   - Custom email templates

2. **Advanced Features**
   - Priority-based notifications
   - Integration with ticketing systems
   - Automated follow-up reminders

## Notes
- Test email delivery in staging environment first
- Consider rate limits of email service
- Plan for scalability if recipient list grows 