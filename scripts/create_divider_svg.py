import os

svg_content = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 80" width="100%" height="auto" fill="none" class="overflow-visible">
  <defs>
    <!-- Teardrop petal template (Center Flower: length ~30, width ~16) -->
    <!-- Point at origin (0,0), bulbous teardrop rounded at top -->
    <path id="center-petal" d="M 0 -2 C -6 -9 -11 -18 -8 -25 C -5 -31 5 -31 8 -25 C 11 -18 6 -9 0 -2 Z" fill="#1d4ed8" />
    
    <!-- Side teardrop petal template (flanking: length ~22, width ~12) -->
    <path id="side-petal" d="M 0 -1.5 C -4 -6 -8 -13 -6 -18 C -4 -23 4 -23 6 -18 C 8 -13 4 -6 0 -1.5 Z" fill="#1d4ed8" />
    
    <linearGradient id="line-grad-left" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563eb" stop-opacity="0" />
      <stop offset="70%" stop-color="#2563eb" stop-opacity="0.7" />
      <stop offset="100%" stop-color="#1d4ed8" stop-opacity="1" />
    </linearGradient>

    <!-- One side wing (left half: flourish arcs, dot, 3 petals, tapered spear line) -->
    <g id="flourish-wing">
      <!-- Tapered horizontal spear line: from x=245 tapering to x=20 -->
      <polygon points="245,38.5 245,41.5 15,40" fill="url(#line-grad-left)" />
      
      <!-- 3 Flanking Petals pointing outward-left (center of cluster at x=280, y=40) -->
      <!-- Top petal angled at ~135 deg (up-left) -->
      <use href="#side-petal" transform="translate(280, 40) rotate(-45)" />
      <!-- Middle petal horizontal left (-90 deg from UP) -->
      <use href="#side-petal" transform="translate(280, 40) rotate(-90)" />
      <!-- Bottom petal angled at ~225 deg (down-left) -->
      <use href="#side-petal" transform="translate(280, 40) rotate(-135)" />

      <!-- Side Accent Dot (nestled between arcs at x=315, y=40) -->
      <circle cx="315" cy="40" r="5" fill="#0284c7" />

      <!-- Top curved flourish swoosh arc -->
      <path d="M 380,36 Q 338,32 300,16 Q 328,24 365,33 Z" fill="#0284c7" />
      
      <!-- Bottom curved flourish swoosh arc -->
      <path d="M 380,44 Q 338,48 300,64 Q 328,56 365,47 Z" fill="#0284c7" />
    </g>
  </defs>

  <!-- Left Wing -->
  <use href="#flourish-wing" />

  <!-- Right Wing (mirrored around center x=450) -->
  <use href="#flourish-wing" transform="translate(900, 0) scale(-1, 1)" />

  <!-- Center Flower (Centered at 450, 40) -->
  <g transform="translate(450, 40)">
    <!-- 6 Petals arranged radially: paired at -25, 25, 90, 155, 205, 270 deg -->
    <use href="#center-petal" transform="rotate(25)" />
    <use href="#center-petal" transform="rotate(90)" />
    <use href="#center-petal" transform="rotate(155)" />
    <use href="#center-petal" transform="rotate(205)" />
    <use href="#center-petal" transform="rotate(270)" />
    <use href="#center-petal" transform="rotate(335)" />

    <!-- Center Dot -->
    <circle cx="0" cy="0" r="4.5" fill="#0284c7" />
  </g>
</svg>
'''

os.makedirs('public/images', exist_ok=True)
with open('public/images/divider_dinas_vector.svg', 'w', encoding='utf-8') as f:
    f.write(svg_content.strip())

print("Successfully written public/images/divider_dinas_vector.svg")
