# Phase 6: Full End-to-End Testing Guide

## Overview
This document provides comprehensive testing procedures for the Livewire migration of the snippet management system. Test all features to verify functionality and ensure no regressions.

---

## Test Environment Setup

### Prerequisites
- Laravel application running (php artisan serve or Octane)
- Database seeded with test data
- Browser DevTools available for debugging
- Multiple test users created

### Test User Accounts
Create at least 2 test users:
```bash
User 1: test1@example.com (password: password)
User 2: test2@example.com (password: password)
```

---

## Test Categories

### 1. CREATE SNIPPET WORKFLOW ✓

#### 1.1 Navigate to Create Page
- [ ] Navigate to `/snippets/create`
- [ ] Page loads without errors
- [ ] All form fields visible (Title, Description, Code, Language, Tags, Visibility)
- [ ] TagAutocomplete component renders
- [ ] Language dropdown shows all 30+ languages

#### 1.2 Real-Time Validation (Title Field)
- [ ] Leave title empty → Shows red border immediately
- [ ] Type title → Green border appears when valid
- [ ] Character counter shows "X/255"
- [ ] See green checkmark (✓) when valid
- [ ] See red X (✕) when invalid
- [ ] Exceed 255 characters → Error message appears

#### 1.3 Real-Time Validation (Description Field)
- [ ] Optional field - no error when empty
- [ ] Type description → Shows green border
- [ ] Character counter shows "X/1000"
- [ ] Exceed 1000 characters → Error message appears

#### 1.4 Real-Time Validation (Code Field)
- [ ] Leave code empty → Red border, "✕ Required" label
- [ ] Type code → Green border, "✓ Valid" label
- [ ] Code field shows syntax highlighting (if CodeMirror initialized)

#### 1.5 Tag Autocomplete
- [ ] Type in tag field → Shows suggestions dropdown
- [ ] Dropdown shows max 5 tags
- [ ] Click tag → Added to selected tags list
- [ ] Selected tags show with # prefix
- [ ] Tag count displays ("X selected")
- [ ] Click × on tag → Removes it
- [ ] Character counter in tag component works

#### 1.6 Language Selection
- [ ] Select different languages
- [ ] Language value updates in component state
- [ ] All 30+ languages available

#### 1.7 Visibility Toggle
- [ ] Toggle public/private checkbox
- [ ] Checkbox state updates

#### 1.8 Create Snippet (Valid Data)
- [ ] Fill all required fields with valid data
- [ ] Click "Create Snippet" button
- [ ] Loading state shows "Creating..."
- [ ] Form submits successfully
- [ ] Redirect to `/snippets` (index page)
- [ ] Success message shows: "Snippet created successfully!"
- [ ] New snippet appears in the list
- [ ] Tags associated correctly

#### 1.9 Create Snippet (Invalid Data)
- [ ] Submit form without title → Error message
- [ ] Submit form without code → Error message
- [ ] Error messages display in red box at top
- [ ] Form does not submit on validation failure

#### 1.10 Create Snippet (Edge Cases)
- [ ] Title at exactly 255 characters → Accepts
- [ ] Title at 256 characters → Rejects
- [ ] Description at exactly 1000 characters → Accepts
- [ ] Empty description → Accepts (optional)
- [ ] Special characters in title → Accepts
- [ ] HTML/script in code field → Stores as plain text
- [ ] Tags with special characters → Handles correctly

---

### 2. LIST SNIPPETS WORKFLOW (/snippets) ✓

#### 2.1 Page Load
- [ ] Navigate to `/snippets`
- [ ] Page loads without errors
- [ ] All snippets display (or first 15 if paginated)
- [ ] Filter section visible (Search, Language, Tags, Visibility)

#### 2.2 Search Functionality
- [ ] Type in search field → Results filter in real-time
- [ ] Search by partial title match
- [ ] Clear search → Shows all snippets
- [ ] Case-insensitive search works

#### 2.3 Language Filter
- [ ] Select a language → Only snippets in that language show
- [ ] Multiple languages available in dropdown
- [ ] "All" option resets language filter

#### 2.4 Tag Filter
- [ ] Select a tag → Only snippets with that tag show
- [ ] All tags from database available
- [ ] "All" option resets tag filter

#### 2.5 Visibility Filter
- [ ] Set to "Public" → Only public snippets show
- [ ] Set to "Private" → Only private snippets show (even others' private if shown)
- [ ] "All" option shows both public and private

#### 2.6 Clear Filters Button
- [ ] Click "Clear" → All filters reset
- [ ] Shows all snippets again

#### 2.7 Snippet Display
- [ ] Title visible
- [ ] Language badge shows
- [ ] Public/Private indicator shows (🌍 Public or 🔒 Private)
- [ ] Tags display with # prefix
- [ ] Code preview visible
- [ ] Line count shows
- [ ] Results count shows "Found X snippet(s)"

#### 2.8 Action Buttons
- [ ] Export JSON button available for all snippets
- [ ] Export PDF button available for all snippets
- [ ] Edit button visible only for own snippets
- [ ] Delete button visible only for own snippets
- [ ] "Read-only (not your snippet)" message for others' snippets

#### 2.9 Pagination
- [ ] Pagination controls visible if > 15 snippets
- [ ] Can navigate between pages
- [ ] Page number shows current page
- [ ] Previous/Next buttons work

---

### 3. MY SNIPPETS WORKFLOW (/snippets/my) ✓

#### 3.1 Page Load
- [ ] Navigate to `/snippets/my`
- [ ] Page loads without errors
- [ ] Only user's own snippets display
- [ ] User cannot see other users' snippets

#### 3.2 Filters Work the Same
- [ ] All filters work identically to /snippets
- [ ] Search, Language, Tag, Visibility filters work

#### 3.3 Action Buttons
- [ ] Edit button visible for all snippets
- [ ] Delete button visible for all snippets
- [ ] Export buttons available
- [ ] No "Read-only" messages (all owned)

#### 3.4 Different User Access
- [ ] Login as User 1
- [ ] Create snippet as User 1
- [ ] Login as User 2
- [ ] User 2 cannot see User 1's private snippets in /snippets/my
- [ ] User 2 can see User 1's public snippets in /snippets (but marked as read-only)

---

### 4. EDIT SNIPPET WORKFLOW ✓

#### 4.1 Navigate to Edit Page
- [ ] Click Edit button on any owned snippet
- [ ] Navigate to `/snippets/{id}/edit`
- [ ] Page loads without errors
- [ ] All fields pre-populated with current values

#### 4.2 Edit Form Fields
- [ ] Title field shows current title
- [ ] Description field shows current description
- [ ] Code field shows current code
- [ ] Language select shows current language
- [ ] Tags show current tags (comma-separated)
- [ ] Public/Private checkbox shows current state

#### 4.3 Real-Time Validation (Same as Create)
- [ ] Character counters work
- [ ] Green/red indicators appear
- [ ] Validation triggers on field change
- [ ] All validation rules enforced

#### 4.4 Modify Fields
- [ ] Change title → Validates
- [ ] Change description → Validates
- [ ] Change code → Validates
- [ ] Change language → Updates
- [ ] Modify tags → TagAutocomplete works
- [ ] Toggle visibility → Checkbox updates

#### 4.5 Save Changes
- [ ] Click "Update Snippet" button
- [ ] Loading state shows "Updating..."
- [ ] Form submits
- [ ] Success message shows: "Snippet updated successfully!"
- [ ] Redirect to `/snippets`
- [ ] Changes persisted in database

#### 4.6 Save with Validation Errors
- [ ] Clear title field → Error appears
- [ ] Try to save → Form rejects
- [ ] Error message displays

#### 4.7 Delete Button (on Edit Page)
- [ ] Delete button visible on left side of form actions
- [ ] Click Delete → Confirmation modal appears
- [ ] Modal shows snippet title
- [ ] Warning message displays: "⚠️ This action cannot be undone"
- [ ] Can Cancel or Delete Permanently

---

### 5. DELETE SNIPPET WORKFLOW ✓

#### 5.1 Delete from SnippetsIndex Page
- [ ] Click Delete button on snippet
- [ ] Confirmation modal appears
- [ ] Modal shows title in warning section
- [ ] Modal shows "Are you sure you want to delete this snippet?"
- [ ] Warning shows "⚠️ This action cannot be undone"

#### 5.2 Delete from MySnippets Page
- [ ] Same modal appears
- [ ] Same confirmation flow

#### 5.3 Delete from Edit Page
- [ ] Delete button positioned on left side
- [ ] Same modal appears
- [ ] Same confirmation flow

#### 5.4 Cancel Delete
- [ ] Click Cancel button → Modal closes
- [ ] Returns to previous page
- [ ] Snippet NOT deleted

#### 5.5 Confirm Delete
- [ ] Click "Delete Permanently" → Loading state shows "Deleting..."
- [ ] Snippet deleted from database
- [ ] Success message shows: "Snippet '[title]' deleted successfully!"
- [ ] Redirect to `/snippets/my`
- [ ] Snippet no longer in list

#### 5.6 Authorization Check
- [ ] Login as User 1
- [ ] Create snippet as User 1
- [ ] Login as User 2
- [ ] Try to access User 1's snippet edit page `/snippets/{user1_snippet_id}/edit`
- [ ] Should show User 2's snippets or error (not User 1's)
- [ ] User 2 cannot delete User 1's snippet

#### 5.7 Delete Non-Existent Snippet
- [ ] Try to delete with invalid ID
- [ ] Error handling prevents deletion
- [ ] Appropriate error message

---

### 6. AUTHORIZATION & SECURITY ✓

#### 6.1 Create Permissions
- [ ] Unauthenticated users redirected to login on `/snippets/create`
- [ ] Authenticated users can access `/snippets/create`

#### 6.2 Edit Permissions
- [ ] User can edit only their own snippets
- [ ] User cannot edit others' snippets
- [ ] Attempted access to others' snippet edit denied (403 or redirect)

#### 6.3 Delete Permissions
- [ ] User can delete only their own snippets
- [ ] DeleteSnippet component checks `user_id === auth()->id()`
- [ ] User cannot delete others' snippets

#### 6.4 View Permissions
- [ ] Private snippets only visible to owner in `/snippets/my`
- [ ] Private snippets invisible in `/snippets` for non-owners
- [ ] Public snippets visible to all in `/snippets`
- [ ] Public snippets show in `/snippets/my` for owner

#### 6.5 Tag Autocomplete Permissions
- [ ] All users can see existing tags in autocomplete
- [ ] Users can create new tags when creating/editing snippets
- [ ] No permission restrictions on tag viewing

---

### 7. EXPORT FUNCTIONALITY ✓

#### 7.1 JSON Export
- [ ] Click "📄 JSON" button on any snippet
- [ ] File downloads with correct name format
- [ ] JSON file contains all snippet data
- [ ] JSON is valid/parseable

#### 7.2 PDF Export
- [ ] Click "📕 PDF" button on any snippet
- [ ] File downloads with correct name format
- [ ] PDF displays snippet content
- [ ] PDF is readable

---

### 8. DARK MODE TESTING ✓

#### 8.1 Dark Mode Toggle
- [ ] Toggle dark mode on/off
- [ ] All pages render in both themes

#### 8.2 Create Page Dark Mode
- [ ] Form fields visible in dark mode
- [ ] Input borders visible
- [ ] Character counters visible
- [ ] Green/red indicators work
- [ ] Checkmarks/X marks visible
- [ ] TagAutocomplete dropdown visible
- [ ] Language dropdown visible

#### 8.3 List Pages Dark Mode
- [ ] Snippet cards readable
- [ ] Badges visible
- [ ] Tags visible with # prefix
- [ ] Filters visible
- [ ] Buttons visible
- [ ] Pagination visible

#### 8.4 Edit Page Dark Mode
- [ ] All form fields visible
- [ ] Delete button visible
- [ ] Modal visible when opened

#### 8.5 Delete Modal Dark Mode
- [ ] Modal background visible
- [ ] Text readable
- [ ] Buttons visible
- [ ] Title readable in dark background

---

### 9. RESPONSIVE DESIGN ✓

#### 9.1 Desktop (1920x1080)
- [ ] All elements visible
- [ ] Layout optimal for desktop
- [ ] No horizontal scrolling
- [ ] Buttons properly spaced

#### 9.2 Tablet (768x1024)
- [ ] Form fields stack appropriately
- [ ] Filters adapt to tablet width
- [ ] Buttons accessible
- [ ] Modal displays correctly

#### 9.3 Mobile (375x667)
- [ ] Form fields full width
- [ ] Filters stack vertically
- [ ] Buttons accessible with touch
- [ ] Modal fits on screen
- [ ] Text readable
- [ ] No horizontal scrolling (except code)

---

### 10. ERROR HANDLING & EDGE CASES ✓

#### 10.1 Validation Errors
- [ ] Empty title shows error
- [ ] Empty code shows error
- [ ] Exceed character limit shows error
- [ ] Errors display in red at top of form

#### 10.2 Server Errors
- [ ] 404 on non-existent snippet
- [ ] 403 on unauthorized access
- [ ] 500 errors handled gracefully

#### 10.3 Network Errors
- [ ] Livewire handles connection loss
- [ ] Loading states indicate processing
- [ ] User receives feedback on errors

#### 10.4 Edge Cases
- [ ] Very long title (close to 255)
- [ ] Very long description (close to 1000)
- [ ] Code with special characters
- [ ] Code with HTML/JavaScript
- [ ] Multiple tags on single snippet
- [ ] Duplicate tag names
- [ ] Empty code preview
- [ ] Rapid form submission attempts

---

### 11. PERFORMANCE ✓

#### 11.1 Page Load Times
- [ ] `/snippets` loads in < 2 seconds
- [ ] `/snippets/my` loads in < 2 seconds
- [ ] `/snippets/create` loads in < 1 second
- [ ] `/snippets/{id}/edit` loads in < 1 second

#### 11.2 Real-Time Validation
- [ ] Validation happens instantly as you type
- [ ] No noticeable lag on form input

#### 11.3 Tag Autocomplete
- [ ] Suggestions appear immediately
- [ ] Dropdown renders smoothly
- [ ] No lag on tag selection

#### 11.4 Filter Performance
- [ ] Filters update results quickly
- [ ] No lag on filter change

#### 11.5 Database Queries
- [ ] Use Laravel Debugbar or similar to check
- [ ] No N+1 queries in snippet lists
- [ ] Index page shouldn't have excessive queries

---

## Test Results Summary

### Passing Tests
- [ ] All create workflow tests pass
- [ ] All list workflow tests pass
- [ ] All edit workflow tests pass
- [ ] All delete workflow tests pass
- [ ] All authorization tests pass
- [ ] All dark mode tests pass
- [ ] All responsive design tests pass
- [ ] All error handling tests pass
- [ ] All performance tests pass

### Known Issues (if any)
```
(Document any found issues here with severity levels)
```

### Recommendations
```
(Document any improvement suggestions)
```

---

## Sign-Off

**Tester Name:** ________________  
**Date:** ________________  
**Status:** [ ] PASSED [ ] NEEDS WORK [ ] BLOCKED  

**Comments:**
```
(Space for additional notes)
```

---

## Quick Test Checklist (Fast Version)

Use this for quick regression testing:

- [ ] Create snippet with all fields
- [ ] Verify real-time validation works
- [ ] Verify tag autocomplete works
- [ ] Edit snippet and save
- [ ] Delete snippet via confirmation modal
- [ ] Search and filter snippets
- [ ] Export to JSON and PDF
- [ ] Test in dark mode
- [ ] Verify unauthorized access blocked
- [ ] Check all pages responsive on mobile
