# Automatic Changelog Tracking Rule

Every time you modify, add, or refactor code, database schemas, or platform features in this workspace:

1. **Mandatory CHANGELOG Update**:
   - You MUST update `CHANGELOG.md` at the project root before finishing the turn.
   - Insert a new entry at the very top (under the header line `---`), formatted in reverse chronological order (newest on top).

2. **Entry Format**:
   ```markdown
   ## 📅 DD-MM-YY | hh:mm AM/PM IST
   
   ### 🛠️ [Feature / Bug Fix Title]
   - **Goal & Summary:** Brief explanation of the problem solved or feature added.
   - **Key Changes:** Detailed bullet points of functional logic changes.
   - **Files Modified:** List of all files added, edited, or deleted.
   - **Database Changes:** Any SQL queries, new columns, tables, or seed changes (or "None").
   ```

3. **Never Skip**:
   - Do not wait for the user to ask for a changelog update. Update it automatically on every code change.
