# Developer Checklist - Before Any Changes

Use this checklist EVERY TIME you make changes to the system.

---

## ✅ Before Starting

- [ ] Open and read `/README.md` 
- [ ] Check **User System** section for current test user
- [ ] Check **Features Status** section for current phase
- [ ] Check **Database Schema** section for relevant tables
- [ ] Check **Common Issues** if you encounter errors

---

## ✅ User Management (Critical)

**DO:**
- [ ] Use `test@example.com` as primary test user
- [ ] If creating new user, add to README.md **first**
- [ ] If changing user data (role, quota, subscription), document it
- [ ] Reference README for user snapshot when debugging

**DON'T:**
- [ ] Create test users without documenting
- [ ] Delete or modify test@example.com without reason
- [ ] Assume user state - always check README

---

## ✅ Database Changes

**DO:**
- [ ] Create migration file for schema changes
- [ ] Test migration: `php artisan migrate:fresh`
- [ ] Document changes in README.md Database Schema section
- [ ] Update README with new table/field descriptions
- [ ] Add indices and constraints to migrations

**DON'T:**
- [ ] Modify schema without migration
- [ ] Change existing migrations after running
- [ ] Forget to document schema changes

---

## ✅ New Routes/Features

**DO:**
- [ ] Add routes to `routes/web.php`
- [ ] Document routes in README.md Routes section
- [ ] Create controller(s) in `app/Http/Controllers/`
- [ ] Add validation rules
- [ ] Create policy if authorization needed
- [ ] Test with test@example.com user

**DON'T:**
- [ ] Add routes without documenting
- [ ] Forget authorization checks
- [ ] Use deprecated functions (check Laravel version)

---

## ✅ Code Quality

**DO:**
- [ ] Follow PSR-12 coding standards
- [ ] Use Eloquent ORM (not raw queries for sensitive data)
- [ ] Use Model mutators for encryption
- [ ] Add proper error handling
- [ ] Test feature end-to-end

**DON'T:**
- [ ] Store sensitive data unencrypted
- [ ] Skip authorization checks
- [ ] Use deprecated functions
- [ ] Leave TODO comments without context

---

## ✅ Testing & Verification

**DO:**
- [ ] Test with test@example.com user
- [ ] Clear cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Check database: `php artisan tinker`
- [ ] Verify no console errors
- [ ] Check authorization (403 on wrong user)
- [ ] Test happy path AND error cases

**DON'T:**
- [ ] Deploy without testing
- [ ] Skip cache clearing
- [ ] Assume features work without verification

---

## ✅ Documentation Updates

**BEFORE marking task complete, UPDATE README.md:**

- [ ] **User System section** - if any user changes
- [ ] **Features Status section** - mark feature as COMPLETE
- [ ] **Database Schema section** - if schema changed
- [ ] **Routes section** - if routes added/modified
- [ ] **Common Issues section** - if you found/fixed a new issue
- [ ] **Recent Changes section** - date and summary

---

## ✅ Final Commit Checklist

**Before considering task DONE:**

- [ ] All code changes complete
- [ ] All tests passing
- [ ] README.md fully updated
- [ ] Cache cleared
- [ ] No console errors
- [ ] No debugging logs left in code
- [ ] Feature works with test@example.com
- [ ] Database backups exist (if needed)
- [ ] Next developer can understand changes

---

## Common Tasks

### Adding New User
1. Create user via Tinker or form
2. Add entry to README.md User System table
3. Note purpose and credentials
4. Document any special roles/permissions

### Adding New Feature
1. Create database migration (if needed)
2. Create model (if needed)
3. Create controller/routes
4. Create views
5. Add authorization (policy)
6. Test thoroughly
7. **Update README.md sections:**
   - Database Schema (if applicable)
   - Routes section
   - Features Status section

### Fixing Bug
1. Understand root cause
2. Fix code
3. Test fix
4. If common issue, add to README Common Issues section
5. Update Recent Changes section

### Adding New Database Table
1. Create migration: `php artisan make:migration create_xxx_table`
2. Define schema in migration
3. Create model: `php artisan make:model Xxx`
4. Add relationships to other models
5. **Update README.md Database Schema section with:**
   - Table name
   - All fields with types
   - Indices and constraints
   - Example values

---

## Emergency: User/Database Inconsistency

If you notice user data is inconsistent:

1. Check README.md User System section
2. This is the **source of truth**
3. If README says `Free` but user has `Pro`:
   - Update user in database to match README
   - Document why (if accidental)
4. Never assume, always check README first

---

## Quick Links

- **Main Documentation:** `/README.md`
- **How to Use README:** `/HOW_TO_USE_README.md` (this file)
- **Website Migration Details:** `/MIGRATION_WEBSITE_MANAGEMENT.md`
- **Website Testing Guide:** `/WEBSITE_MANAGEMENT_QUICKREF.md`
- **Architecture:** `/ARCHITECTURE_GUIDELINES.md`

---

## Questions?

1. Check README.md first
2. Check HOW_TO_USE_README.md (this file)
3. Check Common Issues in README
4. Check other documentation files
5. Inspect the code in `app/Http/Controllers/`
6. Use `php artisan tinker` to inspect database

**Most answers are in README.md. Read it thoroughly.**
