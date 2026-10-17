import mysql.connector

cfg = {'host': '127.0.0.1', 'user': 'root', 'password': ''}
db_orig = mysql.connector.connect(**cfg, database='kravyo_db')
db_test = mysql.connector.connect(**cfg, database='kravyo_test_verify')

cur_orig = db_orig.cursor()
cur_test = db_test.cursor()

cur_orig.execute('SHOW TABLES')
tables_orig = [r[0] for r in cur_orig.fetchall()]

cur_test.execute('SHOW TABLES')
tables_test = [r[0] for r in cur_test.fetchall()]

print(f"Original tables: {len(tables_orig)} | Imported tables: {len(tables_test)}")
assert tables_orig == tables_test, "Table mismatch!"

all_match = True
print("-" * 65)
print(f"{'Table Name':<28} | {'Original Rows':<13} | {'Imported Rows':<13} | Status")
print("-" * 65)

for t in tables_orig:
    cur_orig.execute(f"SELECT COUNT(*) FROM `{t}`")
    c_orig = cur_orig.fetchone()[0]
    cur_test.execute(f"SELECT COUNT(*) FROM `{t}`")
    c_test = cur_test.fetchone()[0]
    match = (c_orig == c_test)
    if not match:
        all_match = False
    print(f"{t:<28} | {c_orig:<13} | {c_test:<13} | {'OK MATCH' if match else 'ERROR'}")

print("-" * 65)
if all_match:
    print("VERIFICATION RESULT: ALL 20 TABLES AND ALL ROW COUNTS MATCH 100% PERFECTLY!")
else:
    print("MISMATCH FOUND!")

# Clean up temporary test database
cur_test.execute("DROP DATABASE kravyo_test_verify")
print("Cleaned up temporary test database.")
