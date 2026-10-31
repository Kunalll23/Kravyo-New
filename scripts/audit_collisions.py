import re

def parse_path_segments(d_str):
    tokens = d_str.strip().split()
    segments = []
    i = 0
    curr_x, curr_y = 0, 0
    while i < len(tokens):
        cmd = tokens[i]
        if cmd == 'M':
            curr_x = float(tokens[i+1])
            curr_y = float(tokens[i+2])
            i += 3
        elif cmd == 'L':
            next_x = float(tokens[i+1])
            next_y = float(tokens[i+2])
            segments.append((curr_x, curr_y, next_x, next_y))
            curr_x, curr_y = next_x, next_y
            i += 3
        else:
            i += 1
    return segments

def parse_and_check_all(svg_str, diagram_name):
    texts = re.findall(r'<text\s+x="([^"]+)"\s+y="([^"]+)"(?:\s+text-anchor="([^"]+)")?[^>]*>(.*?)</text>', svg_str)
    lines = re.findall(r'<line\s+x1="([^"]+)"\s+y1="([^"]+)"\s+x2="([^"]+)"\s+y2="([^"]+)"', svg_str)
    paths = re.findall(r'<path\s+d="([^"]+)"', svg_str)
    rects = re.findall(r'<rect\s+x="([^"]+)"\s+y="([^"]+)"\s+width="([^"]+)"\s+height="([^"]+)"', svg_str)
    
    all_segments = []
    for lx1, ly1, lx2, ly2 in lines:
        all_segments.append((float(lx1), float(ly1), float(lx2), float(ly2)))
        
    for p in paths:
        all_segments.extend(parse_path_segments(p))
        
    collisions = []
    for t_x, t_y, anchor, content in texts:
        if "Figure :" in content:
            continue
        tx = float(t_x)
        ty = float(t_y)
        width = len(content) * 6.5
        height = 12
        if anchor == 'middle':
            t_left = tx - width / 2
            t_right = tx + width / 2
        elif anchor == 'end':
            t_left = tx - width
            t_right = tx
        else:
            t_left = tx
            t_right = tx + width
        t_top = ty - height + 2
        t_bottom = ty + 2
        
        for x1, y1, x2, y2 in all_segments:
            if abs(x1 - x2) < 2:
                top = min(y1, y2)
                bottom = max(y1, y2)
                if (t_left <= x1 <= t_right) and not (bottom <= t_top or top >= t_bottom):
                    collisions.append(f"Vertical line at x={x1} ({top} -> {bottom}) cuts through '{content}'")
            elif abs(y1 - y2) < 2:
                left = min(x1, x2)
                right = max(x1, x2)
                if (t_top <= y1 <= t_bottom) and not (right <= t_left or left >= t_right):
                    collisions.append(f"Horizontal line at y={y1} ({left} -> {right}) cuts through '{content}'")

    # Check rects colliding with each other
    rect_objs = []
    for rx, ry, rw, rh in rects:
        # Ignore lane boundary rects (width > 800 or height > 800)
        w, h = float(rw), float(rh)
        if w > 400 and h > 500:
            continue
        rect_objs.append((float(rx), float(ry), w, h))
        
    for i in range(len(rect_objs)):
        r1_x, r1_y, r1_w, r1_h = rect_objs[i]
        for j in range(i + 1, len(rect_objs)):
            r2_x, r2_y, r2_w, r2_h = rect_objs[j]
            # Check overlap
            if not (r1_x + r1_w <= r2_x or r2_x + r2_w <= r1_x or r1_y + r1_h <= r2_y or r2_y + r2_h <= r1_y):
                collisions.append(f"Rect collision between ({r1_x},{r1_y}) and ({r2_x},{r2_y})")
                    
    print(f"\n=== {diagram_name} ===")
    if collisions:
        for c in collisions:
            print("  [COLLISION]", c)
    else:
        print("  [OK] ZERO line/text/box collisions!")
    return len(collisions)

from generate_all_strict_activity_diagrams import (
    build_act_1, build_act_2, build_act_3, build_act_4, build_act_5,
    build_act_6, build_act_7, build_act_8, build_act_9, build_act_10, build_act_11
)

total_collisions = 0
for idx, fn in enumerate([build_act_1, build_act_2, build_act_3, build_act_4, build_act_5,
                         build_act_6, build_act_7, build_act_8, build_act_9, build_act_10, build_act_11], 1):
    total_collisions += parse_and_check_all(fn(), f"Activity Diagram 3.3.{idx}")

print(f"\n==========================================")
print(f"TOTAL DETECTED COLLISIONS: {total_collisions}")
print(f"==========================================")
