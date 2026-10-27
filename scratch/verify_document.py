import docx

doc = docx.Document('Kravyo_Project_Report.docx')
text_full = '\n'.join([p.text for p in doc.paragraphs] + [c.text for t in doc.tables for r in t.rows for c in r.cells])

checklist = [
    # Sequence 3
    ('Seq 3: Purpose', 'Purpose'),
    ('Seq 3: Points with description', 'Points with Description for Current Semester Project'),
    ('Seq 3: Tools and technology selected', 'Tools and Technology Selected for Current Semester'),
    
    # Sequence 4
    ('Seq 4: Scope', 'Scope'),
    ('Seq 4: Functional requirements', 'Functional Requirements'),
    ('Seq 4: Non-functional requirements', 'Non-Functional Requirements'),
    ('Seq 4: Exciting functionalities', 'Exciting Functionalities'),
    ('Seq 4: Number of users', 'Number of Users'),
    ('Seq 4: User wise characteristics', 'User-Wise Characteristics'),
    ('Seq 4: Data Dictionary, integrity constraints', 'Data Dictionary and Integrity Constraints'),
    ('Seq 4: Summary about expected & exciting', 'Summary About Expected Functionality and Exciting Functionality'),
    ('Seq 4: Users of the system with their characteristics', 'Users of the System with Their Characteristics'),
    
    # Sequence 5
    ('Seq 5: Use Case Diagram', 'Use Case Diagram'),
    ('Seq 5: Activity Diagram', 'Activity Diagram'),
    ('Seq 5: GUI Design (Functionality wise)', 'GUI Design (Functionality-Wise)'),
    ('Seq 5: Traversals [in hierarchical format]', 'Traversals [in Hierarchical Format]'),
    ('Seq 5: System Development model', 'System Development Model'),
    ('Seq 5: Table with description (attributes, data type, size, constraints)', 'Attribute'),
    ('Seq 5: Purpose of the table with description', 'Purpose: Stores'),
    ('Seq 5: A normal form of a database schema', 'Normal Form of the Database Schema'),
    ('Seq 5: Summary (Number of tables)', '20 fully normalized'),
    
    # Sequence 6
    ('Seq 6: Screenshot of Homepage', 'Screenshot of Homepage & Navigation Controls'),
    ('Seq 6: Used navigation control with justification', 'Used Navigation Controls with Justification'),
    ('Seq 6: Screenshot of UI', 'Detailed Screen Specifications Across All 4 User Roles'),
    ('Seq 6: Purpose of the UI', 'Purpose of the UI'),
    ('Seq 6: Used control with justification', 'Used Controls with Justification'),
    ('Seq 6: Associated tables', 'Associated Tables'),
    ('Seq 6: Data In and Out', 'Data From (Data In)'),
    ('Seq 6: Data validation (if any)', 'Data Validation')
]

print("=== CHECKLIST VERIFICATION RESULTS ===")
all_passed = True
for label, query in checklist:
    found = query.lower() in text_full.lower()
    status = "EXACT MATCH" if found else "MISSING"
    if not found:
        all_passed = False
    print(f"[{status:11}] {label}")

print(f"\nAll criteria found: {all_passed}")
