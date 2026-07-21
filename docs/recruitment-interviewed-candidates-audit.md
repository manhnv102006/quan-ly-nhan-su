# Interviewed Candidates Admin Flow Audit

## Current structure

- Admin recruitment routes are defined in `routes/web.php` under the `admin` route group.
- Candidate management uses `App\Http\Controllers\Admin\CandidateController`.
- Interview management uses `App\Http\Controllers\Admin\InterviewController`.
- Candidate records store workflow state in `candidates.status` with these active values:
  - `new`
  - `interview`
  - `passed`
  - `failed`
- Interview records store meeting state in `interviews.status`:
  - `scheduled`
  - `completed`
  - `cancelled`
  - `no_show`
- Interview records store interview result in `interviews.result`:
  - `pending`
  - `passed`
  - `failed`

## Existing behavior

- Creating an interview sets the candidate status to `interview`.
- Updating an interview result currently also updates the candidate status:
  - `passed` result sets candidate status to `passed`.
  - `failed` result sets candidate status to `failed`.
  - `pending` result keeps candidate status as `interview`.
- The candidate detail page currently includes controls that can update candidate status directly.
- Candidate conversion to employee is allowed only when candidate status is `passed`.

## Implementation direction

- Add a dedicated admin page for interviewed candidates.
- Let that page own the post-interview decision action: `passed` or `failed`.
- Keep the existing candidate pages focused on viewing candidate records and profile details.
- Reuse existing `candidates.status`, `interviews.status`, and `interviews.result` columns.
- Avoid a new migration unless a later step discovers a hard data requirement.
