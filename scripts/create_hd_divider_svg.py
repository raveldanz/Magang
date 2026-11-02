import os

svg_template = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="-130 -18 260 36" class="w-64 sm:w-80 md:w-96 h-auto select-none pointer-events-none" fill="none">
  <defs>
    <!-- Center teardrop petal (slender, elegant, bulbous head) -->
    <path id="cp" d="M 0,-1 C -2.2,-3.5 -4.5,-7.5 -3.5,-11 C -2.5,-14 2.5,-14 3.5,-11 C 4.5,-7.5 2.2,-3.5 0,-1 Z" fill="#1d4ed8" />
    
    <!-- Side teardrop petal (graceful mini petal) -->
    <path id="sp" d="M -1,0 C -3,-1.5 -6,-3 -8.5,-2.5 C -11,-1.5 -11,1.5 -8.5,2.5 C -6,3 -3,1.5 -1,0 Z" fill="#1d4ed8" />

    <!-- Spear line gradient left to right -->
    <linearGradient id="spear-grad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#0284c7" stop-opacity="1" />
      <stop offset="60%" stop-color="#0284c7" stop-opacity="0.9" />
      <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.8" />
    </linearGradient>

    <!-- Wing (right side: flourish arcs, dot, 3 petals, tapered spear) -->
    <g id="wing">
      <!-- Upper Flourish Arc -->
      <path d="M 12,-2.5 Q 26,-5.5 38,-13.5 Q 28,-7.5 18,-3.5 Z" fill="#0284c7" />
      <!-- Lower Flourish Arc -->
      <path d="M 12,2.5 Q 26,5.5 38,13.5 Q 28,7.5 18,3.5 Z" fill="#0284c7" />
      
      <!-- Accent Dot nestled between arcs -->
      <circle cx="30" cy="0" r="2.2" fill="#0284c7" />

      <!-- 3 Flanking Petals (pointing outward right) -->
      <!-- Center petal pointing right -->
      <use href="#sp" transform="translate(48, 0) scale(-1, 1)" />
      <!-- Top petal pointing up-right (~40 deg) -->
      <use href="#sp" transform="translate(45, 0) scale(-1, 1) rotate(40)" />
      <!-- Bottom petal pointing down-right (~-40 deg) -->
      <use href="#sp" transform="translate(45, 0) scale(-1, 1) rotate(-40)" />

      <!-- Tapered Spear Line extending right -->
      <polygon points="53,-1.75 53,1.75 126,0.3 126,-0.3" fill="url(#spear-grad)" />
    </g>
  </defs>

  <!-- Left Wing (mirrored scale(-1, 1)) -->
  <use href="#wing" transform="scale(-1, 1)" />

  <!-- Right Wing -->
  <use href="#wing" />

  <!-- Center Flower (6 petals arranged radially) -->
  <g>
    <!-- Top-right / Top-left pair -->
    <use href="#cp" transform="rotate(25)" />
    <use href="#cp" transform="rotate(335)" />
    <!-- Horizontal pair -->
    <use href="#cp" transform="rotate(90)" />
    <use href="#cp" transform="rotate(270)" />
    <!-- Bottom pair -->
    <use href="#cp" transform="rotate(155)" />
    <use href="#cp" transform="rotate(205)" />

    <!-- Center Pivot Dot -->
    <circle cx="0" cy="0" r="2.6" fill="#0284c7" />
  </g>
</svg>'''

with open('public/images/pembatas_dinas_hd.svg', 'w', encoding='utf-8') as f:
    f.write(svg_template.strip())

print("HD Vector SVG created at public/images/pembatas_dinas_hd.svg")
