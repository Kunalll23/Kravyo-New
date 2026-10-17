import base64
import os

png_path = r"C:\Users\saisu\.gemini\antigravity-ide\brain\2ac7f005-3362-4512-b97d-437781639dc2\kravyo_exact_master.png"
svg_path = r"C:\Users\saisu\.gemini\antigravity-ide\brain\2ac7f005-3362-4512-b97d-437781639dc2\kravyo_exact_vector.svg"

with open(png_path, "rb") as f:
    b64 = base64.b64encode(f.read()).decode("ascii")

svg_content = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1960 984" width="100%" height="100%">
  <image href="data:image/png;base64,{b64}" width="1960" height="984"/>
</svg>"""

with open(svg_path, "w", encoding="utf-8") as f:
    f.write(svg_content)

print("SVG created successfully at", svg_path)
