# Master Testing Checklist - Print Version

## PHASE 6: LIVEWIRE MIGRATION - MASTER TESTING CHECKLIST

**Date:** ________________  
**Tester:** ________________  
**Build:** ________________  

---

## SECTION 1: CREATE SNIPPET PAGE (`/snippets/create`)

### Page Load & Components
- [ ] Page loads without errors
- [ ] All form fields visible
- [ ] Validation indicators present
- [ ] Tag autocomplete visible
- [ ] Language dropdown has 30+ options

### Field Validation - Title
- [ ] Empty title shows red border/X
- [ ] Valid title shows green border/checkmark (✓)
- [ ] Character counter shows (X/255)
- [ ] 255 characters accepted
- [ ] 256 characters rejected with error
- [ ] Special characters accepted

### Field Validation - Description
- [ ] Optional field - no error when empty
- [ ] Character counter shows (X/1000)
- [ ] Valid data shows green indicator
- [ ] 1000 characters accepted
- [ ] 1001 characters rejected
- [ ] Multiline input works

### Field Validation - Code
- [ ] Empty code shows red X and "Required"
- [ ] Code entered shows green ✓ and "Valid"
- [ ] Character counter works
- [ ] Multiline code accepted
- [ ] Special characters accepted
- [ ] HTML/Script tags handled correctly

### Tag Autocomplete
- [ ] Typing in tag field shows dropdown
- [ ] Suggestions appear immediately
- [ ] Max 5 suggestions shown
- [ ] Clicking suggestion adds tag
- [ ] Selected tags show with # prefix
- [ ] Tag count displays ("X selected")
- [ ] × button removes tag
- [ ] Removing tag updates count
- [ ] Multiple tags supported

### Form Submission
- [ ] All required fields filled → Form submits
- [ ] Missing title → Form rejected
- [ ] Missing code → Form rejected
- [ ] Invalid data → Error message shows
- [ ] Valid data → Success message: "Snippet created successfully!"
- [ ] Redirects to `/snippets`
- [ ] New snippet visible in list

### Dark Mode (Create Page)
- [ ] Form fields visible in dark mode
- [ ] Labels readable
- [ ] Input borders visible
- [ ] Error messages readable
- [ ] All checkmarks/X marks visible
- [ ] Dropdown visible and usable

---

## SECTION 2: LIST PAGE (`/snippets`)

### Page Display
- [ ] Page loads without errors
- [ ] All snippets display (or first page if paginated)
- [ ] Snippet cards show: title, language, visibility, code preview, line count

### Search Filter
- [ ] Type in search → Results filter in real-time
- [ ] Partial title match works
- [ ] Clear search → All snippets return
- [ ] Case-insensitive search works

### Language Filter
- [ ] Select language → Only that language shows
- [ ] All languages available in dropdown
- [ ] Selecting "All" shows all languages

### Tag Filter
- [ ] Select tag → Only snippets with tag show
- [ ] All tags available in dropdown
- [ ] Selecting "All" shows all tags

### Visibility Filter
- [ ] "Public" selected → Only public snippets show
- [ ] "Private" selected → Only private snippets show
- [ ] "All" selected → Both public and private show

### Filter Combinations
- [ ] Search + Language work together
- [ ] Language + Tag work together
- [ ] All filters work together
- [ ] Clear button resets all filters

### Action Buttons
- [ ] Export JSON button works and downloads file
- [ ] Export PDF button works and downloads file
- [ ] Edit button visible for own snippets only
- [ ] Delete button visible for own snippets only
- [ ] "Read-only" message for others' snippets

### Results Count
- [ ] Shows "Found X snippet(s)"
- [ ] Count accurate
- [ ] Updates when filters change

### Pagination
- [ ] Pagination shows if > 15 snippets
- [ ] Can navigate between pages
- [ ] Current page highlighted
- [ ] Previous/Next buttons work

---

## SECTION 3: MY SNIPPETS PAGE (`/snippets/my`)

### Page Display
- [ ] Only user's own snippets display
- [ ] Other users' snippets not visible
- [ ] All snippet data displays correctly

### Filters (All Same as /snippets)
- [ ] Search filter works
- [ ] Language filter works
- [ ] Tag filter works
- [ ] Visibility filter works
- [ ] Clear button works

### Edit/Delete Access
- [ ] Edit button visible for all snippets
- [ ] Delete button visible for all snippets
- [ ] No "Read-only" messages

### Multi-User Verification
- [ ] Login as User 1
- [ ] Create snippet as User 1
- [ ] Login as User 2
- [ ] User 2 cannot see User 1's private snippet
- [ ] User 2 can see User 1's public snippet (read-only in /snippets)

---

## SECTION 4: EDIT PAGE (`/snippets/{id}/edit`)

### Page Load & Fields
- [ ] Page loads without errors
- [ ] Title field pre-populated
- [ ] Description field pre-populated
- [ ] Code field pre-populated
- [ ] Language select shows current value
- [ ] Tags show current tags
- [ ] Public toggle shows current state

### Field Validation (Same as Create)
- [ ] All validation rules enforced
- [ ] Character counters work
- [ ] Visual indicators work
- [ ] Real-time validation active

### Modify & Save
- [ ] Can modify title
- [ ] Can modify description
- [ ] Can modify code
- [ ] Can change language
- [ ] Can add/remove tags
- [ ] Can toggle public/private
- [ ] Can save all changes
- [ ] Success message: "Snippet updated successfully!"
- [ ] Changes persisted in database
- [ ] Redirects to `/snippets`

### Delete Button
- [ ] Delete button visible on left side
- [ ] Clicking Delete opens confirmation modal
- [ ] Modal has all expected content

### Authorization
- [ ] Cannot edit others' snippets
- [ ] Accessing others' edit page redirected/denied
- [ ] Can only see own snippets in editor

---

## SECTION 5: DELETE WORKFLOW

### Delete Button Location
- [ ] Delete button visible in SnippetsIndex
- [ ] Delete button visible in MySnippets
- [ ] Delete button visible in EditSnippet form

### Confirmation Modal
- [ ] Modal appears on delete click
- [ ] Modal shows snippet title
- [ ] Modal shows: "Are you sure you want to delete this snippet?"
- [ ] Modal shows: "⚠️ This action cannot be undone"
- [ ] Cancel button present
- [ ] Delete Permanently button present

### Cancel Functionality
- [ ] Click Cancel → Modal closes
- [ ] Snippet not deleted
- [ ] Returns to same page

### Confirm Delete
- [ ] Click Delete Permanently
- [ ] Loading state shows "Deleting..."
- [ ] Modal closes
- [ ] Snippet deleted from database
- [ ] Success message: "Snippet '[title]' deleted successfully!"
- [ ] Redirects to `/snippets/my`
- [ ] Snippet no longer in list

### Authorization on Delete
- [ ] User 1 can delete User 1's snippets
- [ ] User 2 cannot delete User 1's snippets
- [ ] Authorization error displayed if attempted

---

## SECTION 6: AUTHORIZATION & SECURITY

### Login Required
- [ ] Unauthenticated users cannot access `/snippets/create`
- [ ] Redirected to login page
- [ ] Authenticated users can access

### Snippet Ownership
- [ ] Only owner can edit snippet
- [ ] Only owner can delete snippet
- [ ] Owner indicated by user_id

### Snippet Visibility
- [ ] Public snippets visible to all users
- [ ] Private snippets only visible to owner
- [ ] Private snippets show "🔒 Private"
- [ ] Public snippets show "🌍 Public"

### Read-Only Access
- [ ] Can view public snippets from others
- [ ] Marked as "Read-only" (cannot edit/delete)
- [ ] Export buttons available for all
- [ ] Edit/Delete buttons hidden

### Unauthorized Access
- [ ] Attempt to access others' edit page → Error/Redirect
- [ ] Attempt to delete others' snippet → Error/Authorization fail
- [ ] Clear error messages shown

---

## SECTION 7: DARK MODE TESTING

### Create Page Dark Mode
- [ ] Form visible and readable
- [ ] Input borders visible
- [ ] Checkmarks/X marks visible
- [ ] Text readable
- [ ] Buttons visible
- [ ] Dropdown visible

### List Page Dark Mode
- [ ] Snippet cards readable
- [ ] Language badges visible
- [ ] Public/Private indicators visible
- [ ] Tags visible
- [ ] Code preview readable
- [ ] Action buttons visible

### Edit Page Dark Mode
- [ ] All fields visible
- [ ] Validation indicators work
- [ ] Delete button visible

### Modal Dark Mode
- [ ] Modal background visible
- [ ] Text readable
- [ ] Buttons visible
- [ ] Warning message clear

### Toggle Experience
- [ ] Can toggle dark/light seamlessly
- [ ] No loss of functionality
- [ ] No unreadable text
- [ ] All indicators work in both modes

---

## SECTION 8: MOBILE RESPONSIVENESS

### Create Page Mobile (375x667)
- [ ] Form fields full width
- [ ] Title field readable
- [ ] Description field readable
- [ ] Code field readable
- [ ] Buttons fit on screen
- [ ] No horizontal scrolling (except code)
- [ ] Tag input accessible
- [ ] Language dropdown works

### List Page Mobile
- [ ] Filters stack vertically
- [ ] Search input full width
- [ ] Snippet cards readable
- [ ] Action buttons accessible
- [ ] Pagination works

### Edit Page Mobile
- [ ] All fields accessible
- [ ] Visible without excessive scrolling
- [ ] Buttons reachable

### Modal Mobile
- [ ] Modal fits on screen
- [ ] Text readable
- [ ] Buttons accessible

### Tablet (768x1024)
- [ ] All elements visible
- [ ] Good spacing
- [ ] Buttons appropriately sized
- [ ] No horizontal scrolling

---

## SECTION 9: ERROR HANDLING

### Validation Errors
- [ ] Empty title shows error message
- [ ] Empty code shows error message
- [ ] Exceeded character limit shows error
- [ ] Errors display in red
- [ ] Error message is helpful and specific

### Server Errors
- [ ] 404 on invalid snippet ID
- [ ] 403 on unauthorized access
- [ ] 500 errors handled gracefully

### Network Issues
- [ ] Livewire reconnects after connection loss
- [ ] User receives appropriate message
- [ ] Can retry action

### Edge Cases
- [ ] Very long title (254 characters) accepted
- [ ] 255 character title accepted
- [ ] 256 character title rejected
- [ ] Very long description (999 chars) accepted
- [ ] 1000 character description accepted
- [ ] 1001 character description rejected
- [ ] HTML in code field stored as plain text
- [ ] Multiple rapid submissions handled
- [ ] Empty code field properly rejected

---

## SECTION 10: EXPORT FUNCTIONALITY

### JSON Export
- [ ] Click JSON button
- [ ] File downloads
- [ ] Filename correct format
- [ ] JSON valid and parseable
- [ ] Contains all snippet data

### PDF Export
- [ ] Click PDF button
- [ ] File downloads
- [ ] Filename correct format
- [ ] PDF viewable/readable
- [ ] All snippet content included

---

## SECTION 11: PERFORMANCE

### Page Load Times
- [ ] `/snippets` loads in < 2 seconds
- [ ] `/snippets/my` loads in < 2 seconds
- [ ] `/snippets/create` loads in < 1 second
- [ ] `/snippets/{id}/edit` loads in < 1 second

### Real-Time Features
- [ ] Validation feedback instant (< 100ms)
- [ ] Tag autocomplete responsive (< 500ms)
- [ ] Filters update quickly (< 500ms)
- [ ] No noticeable lag on input

### Database Efficiency
- [ ] No N+1 queries on list pages
- [ ] Pagination working efficiently
- [ ] Tag queries optimized

---

## SCORING

### Total Checklist Items: 150+

**Scoring:**
- 0-5 failures → ✅ PASS
- 6-15 failures → ⚠️ NEEDS WORK
- 16+ failures → ❌ FAIL

---

## SUMMARY

### Critical Features (Must Pass)
- [ ] Create snippet
- [ ] Edit snippet
- [ ] Delete snippet with confirmation
- [ ] Real-time validation
- [ ] Authorization checks

### Overall Status

- [ ] ✅ All tests pass
- [ ] ⚠️ Some tests need attention
- [ ] ❌ Multiple issues found

### Issues Found

```
Issue #1: ___________________
  Severity: [ ] Low [ ] Medium [ ] High
  
Issue #2: ___________________
  Severity: [ ] Low [ ] Medium [ ] High
  
Issue #3: ___________________
  Severity: [ ] Low [ ] Medium [ ] High
```

---

## APPROVAL

**Tester Name:** ________________  
**Date:** ________________  
**Status:** [ ] PASS [ ] FAIL [ ] NEEDS WORK  

**Signed:** ________________________  

**Approved for Phase 7:** [ ] YES [ ] NO  

---

## NOTES

```
(Space for additional notes and observations)




```

---

**Testing Completed:** [ ] YES [ ] NO  
**All Issues Resolved:** [ ] YES [ ] NO  
**Ready to Proceed to Phase 7:** [ ] YES [ ] NO  

