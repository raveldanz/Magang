import os

# We want a refined, delicate, highly aesthetic SVG of Divider No. 2
# Clean vector curves, balanced proportions, ultra-crisp

def generate_svg(scale=1.0):
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="-200 -24 400 48" class="w-full max-w-lg sm:max-w-xl h-auto select-none pointer-events-none" fill="none">
  <defs>
    <!-- Center teardrop petal (slender, elegant) -->
    <path id="c-petal" d="M 0,-1.5 C -2.2,-4.5 -4.8,-8.5 -3.8,-12.5 C -2.8,-16 2.8,-16 3.8,-12.5 C 4.8,-8.5 2.2,-4.5 0,-1.5 Z" fill="#1d4ed8" />
    
    <!-- Flanking side petal (smaller, graceful) -->
    <path id="s-petal" d="M -1.2,0 C -3.5,-1.8 -7,-3.8 -10,-3 C -13,-2 -13,2 -10,3 C -7,3.8 -3.5,1.8 -1.2,0 Z" fill="#1d4ed8" />

    <!-- Left spear gradient -->
    <linearGradient id="spear-grad-left" x1="100%" y1="0%" x2="0%" y2="0%">
      <stop offset="0%" stop-color="#0284c7" stop-opacity="0.9" />
      <stop offset="70%" stop-color="#38bdf8" stop-opacity="0.6" />
      <stop offset="100%" stop-color="#60a5fa" stop-opacity="0.1" />
    </linearGradient>

    <!-- Wing (right side: swooshes, dot, 3 petals, tapered spear) -->
    <g id="half-wing">
      <!-- Upper Flourish Arc -->
      <path d="M 12,-3 Q 28,-6 44,-16 Q 32,-9 20,-4 Z" fill="#0284c7" />
      <!-- Lower Flourish Arc -->
      <path d="M 12,3 Q 28,6 44,16 Q 32,9 20,4 Z" fill="#0284c7" />
      
      <!-- Accent Dot -->
      <circle cx="36" cy="0" r="2.2" fill="#0284c7" />

      <!-- 3 Flanking Petals (pointing outward right) -->
      <!-- Center petal pointing right -->
      <use href="#s-petal" transform="translate(56, 0) scale(-1, 1)" />
      <!-- Top petal pointing up-right (~40 deg) -->
      <use href="#s-petal" transform="translate(53, 0) scale(-1, 1) rotate(42)" />
      <!-- Bottom petal pointing down-right (~-40 deg) -->
      <use href="#s-petal" transform="translate(53, 0) scale(-1, 1) rotate(-42)" />

      <!-- Tapered Spear Line extending right -->
      <polygon points="62,-2 62,2 185,0.4 185,-0.4" fill="url(#spear-grad-left)" transform="scale(-1, 1) translate(-247, 0)" />
    </g>
  </defs>

  <!-- Left Wing (mirrored) -->
  <g transform="scale(-1, 1)">
    <!-- Upper & Lower Swoosh -->
    <path d="M 12,-3 Q 28,-6 44,-16 Q 32,-9 20,-4 Z" fill="#0284c7" />
    <path d="M 12,3 Q 28,6 44,16 Q 32,9 20,4 Z" fill="#0284c7" />
    <circle cx="36" cy="0" r="2.2" fill="#0284c7" />
    
    <!-- 3 Petals pointing left -->
    <use href="#s-petal" transform="translate(56, 0)" />
    <use href="#s-petal" transform="translate(53, 0) rotate(-42)" />
    <use href="#s-petal" transform="translate(53, 0) rotate(42)" />
    
    <!-- Left Spear Line -->
    <polygon points="62,-2 62,2 185,0.4 185,-0.4" fill="url(#spear-grad-left)" />
  </g>

  <!-- Right Wing -->
  <g>
    <!-- Upper & Lower Swoosh -->
    <path d="M 12,-3 Q 28,-6 44,-16 Q 32,-9 20,-4 Z" fill="#0284c7" />
    <path d="M 12,3 Q 28,6 44,16 Q 32,9 20,4 Z" fill="#0284c7" />
    <circle cx="36" cy="0" r="2.2" fill="#0284c7" />
    
    <!-- 3 Petals pointing right -->
    <use href="#s-petal" transform="translate(56, 0) scale(-1, 1)" />
    <use href="#s-petal" transform="translate(53, 0) scale(-1, 1) rotate(42)" />
    <use href="#s-petal" transform="translate(53, 0) scale(-1, 1) rotate(-42)" />
    
    <!-- Right Spear Line -->
    <polygon points="62,-2 62,2 185,0.4 185,-0.4" fill="url(#spear-grad-left)" transform="scale(-1, 1) translate(-247, 0)" />
  </g>

  <!-- Center Flower (6 petals) -->
  <g>
    <use href="#c-petal" transform="rotate(25)" />
    <use href="#c-petal" transform="rotate(90)" />
    <use href="#c-petal" transform="rotate(155)" />
    <use href="#c-petal" transform="rotate(205)" />
    <use href="#c-petal" transform="rotate(270)" />
    <use href="#c-petal" transform="rotate(335)" />
    <!-- Center Dot -->
    <circle cx="0" cy="0" r="2.6" fill="#0284c7" />
  </g>
</svg>'''
    return svg

with open('public/images/pembatas_dinas_vector.svg', 'w', encoding='utf-8') as f:
    f.write(generate_svg())

print("Vector SVG generated.")
