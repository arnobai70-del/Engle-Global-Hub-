# Company legal documents: owner review and placement

Source documents supplied by the owner on 24 Nov 2026 (not copied to the repository):
1. RJSC Certificate of Incorporation (26 October 2026).
2. RJSC Form XII (directors and personal details).
3. Memorandum & Articles of Association (14 pages).
4. DNCC e-Trade Licence (2026–2027).
5. Eagle Global Hub corporate portfolio (8 pages; **company-authored promotional material, not government evidence**).

## Safe public facts and exact admin placement

Enter **only after checking the official copies and, when possible, the issuing authority's verification service**. Do not hard-code facts in templates or seeders. Existing Admin fields are preserved; this change adds two optional date fields.

| Field | Suggested value from the provided PDFs | Where to enter | Public display |
|---|---|---|---|
| Company legal name | EAGLE-GLOBAL HUB LTD. | Admin → Homepage → Site name (only if owner wants to use legal spelling; do not unexpectedly replace branding) | Global header/footer |
| RJSC registration number | C-215102/2026 (certificate) | Admin → Business facts & numbers → Company registration number (RJSC) | About → Registered and accountable; footer |
| RJSC incorporation date | 2026-10-26 | Admin → Business facts & numbers → RJSC incorporation date | About, when registration number is filled |
| DNCC e-Trade Licence number | TRAD/DNCC/027606/2026 | Admin → Business facts & numbers → Trade licence number | About; footer/trust strip |
| DNCC trade licence valid until | 2027-06-30 | Admin → Business facts & numbers → Trade licence valid until | About, when trade licence number is filled |
| Registered/business office | Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212 | Admin → Homepage → Contact address (check current mailing/address details) | Support/About and other configured contact areas |
| Contact phone/email | From owner-approved current contact details; portfolio has two public phones and two emails | Admin → Homepage → Contact; Admin → Business facts for WhatsApp | Support, footer |
| Mission & Vision | Page 2 of corporate portfolio; editorial company statements, not official certification | Admin → Website Content → Page Sections or existing company settings, then content approval | About Us, once approved |
| Company About/story | Portfolio pages 1, 7 and 8 after owner review | Admin → Business facts → About us: your story; or Admin → Website Content → About intro (avoid duplication) | About Us |
| Team names, photos, titles | Form XII can establish **director names**, not every corporate job title | Admin → Team; confirm each public role/photo and obtain consent first | About Us team, only when published with photo |

The official legal company name appears with a hyphen as **EAGLE-GLOBAL HUB LTD.** The current website brand may display **Eagle Global Hub LTD**. Do not silently replace public branding or imply they are two different legal entities.

### Documents that MUST remain private

- **Full Form XII**: contains directors' private NIDs, personal phone numbers, residential addresses, TINs and emails.
- **Full MOA/AOA**: contains shareholders', directors' and witnesses' personal identifiers, birth dates, addresses and share allocations.
- **Unredacted Trade Licence**: includes a director photo, personal NID, private contact details and signature.
- Any QR code / digital signature verification link should be assessed before publishing; redaction must not accidentally disclose sensitive fields via OCR layer, metadata or QR.

Do not upload the unredacted PDFs to GitHub, public media library, publicly reachable storage, or a publicly readable Admin attachment URL. Store any internal copies with authorization, access controls and retention rules. If the owner wants public proof documents, create **separate true-redacted copies**, flatten/sanitize them, verify the rendering **and extracted text**, and obtain approval before publication.

### Legal interpretation and content guardrails

- **Certificate of Incorporation (RJSC)** establishes corporate incorporation, **not** a blanket government licence to conduct all travel, recruitment, immigration or financial services.
- **DNCC Trade Licence** describes a consultancy, tours and travel business for the stated licence year; check current validity/renewal.
- **MOA Clause III** explicitly conditions planned business objects on the required government/competent-authority permissions **before commencement**. MOA business objects are not themselves operational licences.
- No independently verified travel-agent registration, overseas recruitment licence, Hajj/Umrah approval, education-institution agency appointment or payment-gateway approval was supplied in this batch. Never present these as approved.
- The corporate portfolio is marketing copy. It does **not** prove the stated services or partnerships. Page 4 country lists differ from the older Travel & Visa Agency requirements list and current site; reconcile them before editing services. Page 8 warns that illustrative photos, flags, routes and packages need confirmation.
- The official documents list **Sharmeen Akter Rosy** and **Md. Shoaib Akter**, while older seeded team drafts may spell names or titles differently. Verify the exact owner-approved public presentation instead of automatically changing records.
- Terms & Conditions and Privacy Policy remain separate legal notices; official documents are **not** substitutes for either. Do not alter those notices without legal review.

## Admin Legal Documents workflow (implemented in PR #53)

- Navigate to **Admin → Legal Documents** (`/admin/legal-documents`).
- An admin or super-admin with `content.manage` may upload one PDF at a time (max 8 MB) into `storage/app/private/legal-documents/originals`. Originals are never put on the public disk and cannot be replaced through the form.
- Only the **super-admin** can download an unredacted original, review the official source, mark it verified and approved, and separately publish it. Approval **does not publish**.
- A separately prepared, truly redacted PDF may be uploaded for public viewing. It is initially private and requires the owner to inspect **visual content, embedded text, attachments, metadata, and QR codes** and tick a confirmation before approval. The application rejects a byte-for-byte copy of the original, but cannot automatically prove that a changed PDF is actually safe.
- Only approved/published/current metadata is shown at `/legal-documents`. The separate redacted-copy download route returns 404 for unpublished, unapproved or expired items, and **never** streams the private original.
- Editing any document resets verification, approval and publication. Unpublishing immediately revokes public access, and old URLs cannot retrieve private copies.
- Expiry dates are displayed in the Admin listing; an expiry within 30 days displays a renewal warning, and expired records leave the public listing automatically. There is **no scheduled email/SMS expiry notification yet**.
- A new renewal is uploaded as a **new record**, keeping the older private record for audit/retention. Identical original PDF bytes are rejected to avoid duplicate uploads.
- Legacy **Admin → Business facts & numbers** fields remain owner-managed and are separate from this document approval workflow; do not assume they have passed the document-verification gate.

## Publication process

1. Confirm document authenticity and date against issuer verification options.
2. Enter public identifiers/dates in **Admin → Business facts & numbers** (existing fields + two new optional dates).
3. Update office/contact info only if the owner confirms these are the current public business contacts.
4. Review the company-authored portfolio and enter approved mission/vision/story into CMS, retaining draft and approval workflow.
5. Obtain consent and verified role/photo before publishing any member.
6. Have a local legal professional review claims and customer terms for actual scope of authorised services.
7. Preview EN/BN About Us, header and footer; test for leaked private details before production publishing.

The current branch changes **do not set any public credential values automatically**, publish any drafts, or upload any source PDFs.
