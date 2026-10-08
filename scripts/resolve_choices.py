import sys

def resolve_conflicts(filepath, choices):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    except Exception as e:
        print(f'Error reading {filepath}: {e}')
        return

    in_conflict = False
    resolved_lines = []
    
    current_head = []
    current_main = []
    parsing_head = False
    
    conflict_idx = 0
    
    for line in lines:
        if line.startswith('<<<<<<<'):
            in_conflict = True
            parsing_head = True
            current_head = []
            current_main = []
            conflict_idx += 1
            continue
        elif line.startswith('======='):
            parsing_head = False
            continue
        elif line.startswith('>>>>>>>'):
            in_conflict = False
            
            # Decide which to keep
            choice = choices.get(conflict_idx, 'head') # default head
            if choice == 'head':
                resolved_lines.extend(current_head)
            elif choice == 'main':
                resolved_lines.extend(current_main)
            elif choice == 'both':
                resolved_lines.extend(current_head)
                resolved_lines.extend(current_main)
            elif choice == 'none':
                pass
            continue
            
        if in_conflict:
            if parsing_head:
                current_head.append(line)
            else:
                current_main.append(line)
        else:
            resolved_lines.append(line)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.writelines(resolved_lines)
    print(f'Successfully resolved {conflict_idx} conflicts in {filepath}.')

# choices is a dict {1: 'head', 2: 'main'}
import ast
if len(sys.argv) > 2:
    filepath = sys.argv[1]
    choices = ast.literal_eval(sys.argv[2])
    resolve_conflicts(filepath, choices)
