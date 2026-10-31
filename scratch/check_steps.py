import glob, json

for log in glob.glob('C:/Users/saisu/.gemini/antigravity-ide/brain/daa2c6e1-5aef-4584-a0e3-730eb587512f/.system_generated/logs/*.jsonl'):
    with open(log, 'r', encoding='utf-8', errors='ignore') as f:
        for line in f:
            data = json.loads(line)
            idx = data.get('step_index')
            if idx in [1033, 1034, 1035, 1039, 1040, 1074, 1075]:
                print(f"=== STEP {idx} ===")
                print(str(data.get('content', ''))[:350])
                print()
