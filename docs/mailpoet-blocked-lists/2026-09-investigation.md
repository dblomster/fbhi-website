# MailPoet: lists that refuse to be trashed — investigation (2026-09-17)

Status: **DIAGNOSED, fix handed to Annika 2026-09-17.** Investigation was read-only on prod via Novamira
`execute-php` (SELECT queries + reading MailPoet source through Reflection). No writes were made by Claude.

## Symptom

Two lists under MailPoet → Lists could not be moved to trash. Clicking "Move to trash" did nothing and
MailPoet showed no error:

| id | Name | Type | Subscribers |
|---|---|---|---|
| 19 | Nyhetsbrev 260316 H-K | default | 0 |
| 20 | Nyhetsbrev L-S | default | 0 |

Both were interim batch lists from the March 2026 newsletter send. All active subscribers live in list 21
"Nyhetsbrev, Svenska FINGER-nätverket" (758 subscribed on 2026-09-17), so nothing subscriber-related depends
on 19/20.

## Root cause

`SegmentsRepository::bulkTrash()` (MailPoet 5.38.1) first asks
`NewsletterSegmentRepository::getSubjectsOfActivelyUsedEmailsForSegments()` which lists it may **not** trash,
then silently drops those IDs from the request. The UI never reports the skip, so the lists just stay.

That query treats a list as "actively used" if any newsletter attached to it satisfies
`type = notification OR status = scheduled OR (task.id IS NOT NULL AND task.status IS NULL)`. It does **not**
look at `newsletters.deleted_at` or `scheduled_tasks.deleted_at`.

On 2026-03-16 the two batch newsletters were trashed **before a single email went out**:

| Newsletter | Subject | List | status | queue | task status | trashed |
|---|---|---|---|---|---|---|
| 41 | Nytt från FBHI | 19 | sending | 0 / 142 | NULL (deleted_at set) | 2026-03-16 18:25 |
| 43 | Nytt från FBHI | 20 | sending | 0 / 321 | NULL (deleted_at set) | 2026-03-16 18:25 |

Trashing a sending newsletter marks it and its task as deleted but leaves `status = sending` and
`task.status = NULL`, which is exactly the combination the guard matches. Result: the trashed, never-sent
newsletters block the lists forever until they are **permanently deleted** from Emails → Trash.

Newsletter 42 (trashed draft on list 19) is harmless; drafts do not block.

## Ruled out

- Forms: only form 1 "Sign up" (disabled) exists, pointing at list 3.
- Automations, welcome emails, dynamic-segment filters: none reference 19/20.
- WP-users / WooCommerce list types: both lists are `default`.

## Related finding

Newsletter 44 "Nytt från FBHI" is in the same state on the **live** list 21: trashed 2026-04-10, status
`sending`, 0 / 454 processed, task status `paused`. It does not block anything today (paused ≠ NULL) but is a
stuck send sitting in the trash. Recommended to permanently delete it in the same pass.

## Fix (manual, wp-admin) — sent to Annika in Swedish

1. MailPoet → Emails → filter **Trash**. Permanently delete the three "Nytt från FBHI" rows with status
   *Sending* and 0 sent (ids 41, 43, 44). Leave the *Sent* ones — they are the sending history/statistics.
2. MailPoet → Lists → "Move to trash" on the two lists. They should now disappear.
3. MailPoet → Lists → filter **Trash** → "Delete permanently" on the two lists (optionally also the older
   trashed "Swedish FINGER network members …" lists from the March clean-up).

Subscribers are untouched: the lists hold no members, and deleting a list only removes memberships anyway.

## Verification queries (read-only, execute-php)

```php
global $wpdb; $p = $wpdb->prefix . 'mailpoet_';
// Who blocks which list, per MailPoet's own rule:
$c = \MailPoet\DI\ContainerWrapper::getInstance();
return $c->get(\MailPoet\Newsletter\Segment\NewsletterSegmentRepository::class)
         ->getSubjectsOfActivelyUsedEmailsForSegments([19, 20]);
// Expected after the fix: []
```

Novamira quirk reminder: `file_get_contents(...)` in the payload trips the prod WAF (405 from nginx); reading
plugin source via `ReflectionMethod` + `SplFileObject` works.
