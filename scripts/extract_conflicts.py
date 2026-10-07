import sys

def extract_conflicts(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    except Exception as e:
        print(f'Error reading {filepath}: {e}')
        return

    in_conflict = False
    conflict_blocks = []
    current_block = []

    for i, line in enumerate(lines):
        if line.startswith('<<<<<<<'):
            in_conflict = True
            current_block = [{'head': True, 'text': line, 'line_num': i+1}]
        elif line.startswith('======='):
            current_block.append({'divider': True, 'text': line, 'line_num': i+1})
        elif line.startswith('>>>>>>>'):
            current_block.append({'tail': True, 'text': line, 'line_num': i+1})
            conflict_blocks.append(current_block)
            in_conflict = False
        elif in_conflict:
            current_block.append({'text': line, 'line_num': i+1})

    out_file = f'conflicts_{filepath.replace("/", "_")}.txt'
    with open(out_file, 'w', encoding='utf-8') as f:
        for idx, block in enumerate(conflict_blocks):
            f.write(f'\n--- Conflict {idx+1} in {filepath} ---\n')
            for item in block:
                if 'head' in item: f.write(f'<<<<<<< HEAD (line {item["line_num"]})\n')
                elif 'divider' in item: f.write(f'======= (line {item["line_num"]})\n')
                elif 'tail' in item: f.write(f'>>>>>>> origin/main (line {item["line_num"]})\n')
                else: f.write(item['text'])

extract_conflicts('resources/views/dashboard.blade.php')
extract_conflicts('resources/views/student/application/create.blade.php')
extract_conflicts('.antigravity/memory/learnings.md')
