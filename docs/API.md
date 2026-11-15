# Eagle Global Hub API (v1)

A versioned JSON API for a future mobile app. No Android or iOS app exists yet; this API is the foundation one would use.

- Base URL: `https://eagleglobalhub.com/api/v1`
- Format: JSON. Send `Accept: application/json`.
- Rate limits: 60 requests per minute per user (or per IP when not signed in); token creation is limited to 5 attempts per minute per email and IP.
- Language: add `?lang=bn` for Bengali text where translations exist.

## Authentication

Bearer tokens. Only accounts with a verified email can get one. Tokens expire after 90 days, and expired tokens are deleted daily by the scheduler. The server stores only a SHA-256 hash of each token, so a lost token cannot be recovered; sign in again instead.

### Create a token

`POST /auth/tokens`

```json
{ "email": "student@example.com", "password": "…", "device_name": "Pixel 8" }
```

`201 Created`

```json
{ "token_type": "Bearer", "access_token": "…", "expires_at": "2027-02-13T10:00:00+06:00" }
```

Errors: `422` for wrong credentials or an unverified email, and `429` when rate-limited.

Send the token on every other request:

```
Authorization: Bearer <access_token>
```

### Revoke the current token

`DELETE /auth/tokens/current`, which returns `204 No Content`.

## Account

`GET /me`

```json
{ "data": { "id": 7, "name": "…", "email": "…", "locale": "en", "unread_notifications": 2 } }
```

## Student applications

Only the signed-in student's own applications are returned. Internal staff notes, staff names and file locations are never included.

`GET /applications` returns a list.

`GET /applications/{id}` returns one application with its timeline and document statuses. It returns `404` if the application belongs to someone else.

```json
{
  "data": {
    "id": 12,
    "reference": "EGH-SA-…",
    "status": { "code": "eligibility_review", "label": "Eligibility Review", "stage": "Consultation", "closed": false },
    "destination": "Singapore",
    "institution": null,
    "course": "Diploma in Business",
    "intake": "January 2027",
    "consultant_assigned": true,
    "timeline": [ { "status": "New Enquiry", "note": "Your application file has been opened.", "at": "…" } ],
    "documents": [ { "id": 3, "type": "Academic transcript / mark sheet", "name": "hsc.pdf", "status": "Pending review", "review_note": null } ]
  }
}
```

Status labels are records kept by the consultancy. They are not decisions by an institution or by Singapore's ICA.

## Notifications

- `GET /notifications` returns the 20 newest notifications per page, with `meta.current_page`, `meta.last_page` and `meta.total`.
- `POST /notifications/read` marks all notifications as read and returns `204`.

Push notifications (FCM or APNs) are not connected. An app should poll this endpoint until a push provider is chosen.

## Payment webhooks (server to server)

`POST /api/webhooks/payments/{provider}` is for payment providers, not for apps.

It returns `404` unless a payment gateway adapter is configured in `config/payments.php`; none is configured today.

When a gateway is configured:

- The adapter verifies the provider's signature. Invalid signatures get `400`.
- Each provider event ID is stored once, so repeated deliveries return `"Already processed."`.

## Versioning

Breaking changes will go into `/api/v2`, and `/api/v1` will stay available during a migration period.
