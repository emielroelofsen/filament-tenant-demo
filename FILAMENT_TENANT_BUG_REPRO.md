# Filament Tenancy Bug: Relation manager crash when `$record->relationship` is null (cross-tenant)

This project reproduces a bug that occurs in Filament v4/v5 when:

1. **Panel has tenancy** (e.g. `->tenant(Organisation::class)`).
2. A **non-tenant-scoped resource** (e.g. Admin Purchase Orders) shows records from all tenants.
3. That resource has a **relation manager** for a related model (e.g. Line Items).
4. The **related model** (e.g. `PurchaseOrderLineItem`) is also the **model of another, tenant-scoped resource** (e.g. `PurchaseOrderLineItemResource`), so Filament registers a tenancy global scope on it.
5. The **parent model** (e.g. `PurchaseOrder`) is the model of a **tenant-scoped resource** (e.g. `PurchaseOrderResource`), so it gets a tenancy global scope too.

When an admin opens the **edit page** of a record (e.g. a Purchase Order) that **belongs to another tenant**, the relation manager table loads the related records (line items). When the table renders, callbacks that use `$record->purchaseOrder` (or any parent relationship) **lazy-load** the parent. That query runs on the **parent model**, which has the **tenancy scope** from the tenant-scoped resource. The current tenant is e.g. Organisation A, but the record belongs to Organisation B, so the query returns **null**. Any use of `$record->purchaseOrder->status` (or similar) then throws:

**Error:** `Attempt to read property "status" on null`  
**Location:** In the relation manager, in a `->visible()`, `->disabled()`, or similar callback that uses `$record->purchaseOrder->...` without null-safety.

## Where the bug is in this repo

- **File:** `app/Filament/Resources/PurchaseOrders/RelationManagers/LineItemsRelationManager.php`
- **Line:** `DeleteAction::make()->visible(fn (PurchaseOrderLineItem $record): bool => $record->purchaseOrder->status === 'draft')`
- The same pattern can cause crashes in `->disabled()` callbacks on columns (e.g. an inline-editable column that uses `$record->purchaseOrder->status`).

## How to reproduce

1. **Seed data:** Ensure you have at least two organisations (tenants) and purchase orders:
   - Organisation 1 with at least one purchase order (with line items).
   - Organisation 2 with at least one purchase order (with line items).

2. **Log in** to the Filament panel.

3. **Select tenant Organisation 1** (use the tenant switcher if present).

4. Go to **Admin → Purchase Orders** (the non-tenant-scoped resource that lists all POs).

5. **Edit a purchase order that belongs to Organisation 2** (not the current tenant).

6. The edit page loads; when the **Line Items** relation manager table is rendered, the `visible()` callback runs for each row with `$record->purchaseOrder->status`. For those line items, `$record->purchaseOrder` is **null** (because the PurchaseOrder model is scoped to the current tenant), so you get:
   - **Exception:** `Attempt to read property "status" on null`
   - **ViewException** pointing at the relation manager file and the table view.

## Suggested fix (for app code)

Use the **null-safe operator** so the UI degrades gracefully when the relationship is null (e.g. in cross-tenant admin context):

- **Before:** `$record->purchaseOrder->status === 'draft'`
- **After:** `$record->purchaseOrder?->status === 'draft'`

Same for any `->disabled()`, `->visible()`, or other callbacks that use `$record->purchaseOrder` (or similar parent relationship) in relation managers used by **non-tenant-scoped** resources.

## Possible Filament-side improvements

- Document that in relation managers for non-tenant-scoped resources, lazy-loaded parent relationships may be null when the parent model has a tenancy scope and the record belongs to another tenant.
- Optionally, when resolving the relationship for a relation manager, consider the owner record’s tenant (e.g. use the owner’s `organisation_id`) so that lazy-loading the parent uses the correct tenant context, or document that developers should use null-safe access or eager-load the parent when needed.
