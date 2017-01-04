Soft deletion
---

Entities that support soft deletion are deleted in such a way that they can restored.

Deleted entities may restored using `undelete()` or they can be permanently removed using `purge()`.

The `isDeleted()` method check whether this document has been deleted.

Fetch methods do not return deleted entities. Instead use `fetchDeleted($filter)` to load a deleted entity. Use
`fetchAllDeleted($filter)` to fetch all deleted entities from the database.
