# Demo Script

## 1. Client Workflow

1. Log in as `client@example.com`.
2. Open `/bugs/new`.
3. Create a bug report with a title, description, priority, and screenshot.
4. Open the created bug detail page.
5. Add a comment.

## 2. Admin Workflow

1. Log out and log in as `admin@example.com`.
2. Open `/admin/dashboard`.
3. Review the bug statistics.
4. Open `/bugs`.
5. Use filters by status, priority, or project.
6. Open a bug detail page.
7. Click `Manage`.
8. Assign the bug to a developer and update priority/status.

## 3. Developer Workflow

1. Log out and log in as `developer@example.com`.
2. Open `/bugs`.
3. Open an assigned bug.
4. Update the status to `In Progress` or `Fixed`.
5. Add a technical comment.

## 4. Security Demonstration

1. Log in as `client@example.com`.
2. Try to open `/admin/projects`.
3. Confirm access is denied or redirected.
4. Try to open `/bugs/{id}/manage`.
5. Confirm client users cannot manage assignment or status.
