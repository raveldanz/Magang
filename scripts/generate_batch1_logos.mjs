import fs from 'fs';
import path from 'path';

const logos = [
  {
    name: 'polinema.svg',
    color1: '#1E3A8A', // Deep Blue
    color2: '#F59E0B', // Amber
    accent: '#FFFFFF',
    text: 'POLINEMA',
    sub: 'MALANG',
    symbol: 'gear'
  },
  {
    name: 'uin-malang.svg',
    color1: '#047857', // Emerald Green
    color2: '#D97706', // Gold
    accent: '#FFFFFF',
    text: 'UIN MALANG',
    sub: 'MALIK IBRAHIM',
    symbol: 'dome'
  },
  {
    name: 'polije.svg',
    color1: '#065F46', // Dark Green
    color2: '#FBBF24', // Yellow
    accent: '#FFFFFF',
    text: 'POLIJE',
    sub: 'JEMBER',
    symbol: 'leaf'
  },
  {
    name: 'utm.svg',
    color1: '#1E40AF', // Blue
    color2: '#DC2626', // Red
    accent: '#FFFFFF',
    text: 'UTM',
    sub: 'MADURA',
    symbol: 'torch'
  },
  {
    name: 'poltera.svg',
    color1: '#0369A1', // Ocean Blue
    color2: '#F97316', // Orange
    accent: '#FFFFFF',
    text: 'POLTERA',
    sub: 'MADURA',
    symbol: 'ship'
  },
  {
    name: 'umm.svg',
    color1: '#B91C1C', // Crimson Red
    color2: '#F59E0B', // Gold
    accent: '#FFFFFF',
    text: 'UMM',
    sub: 'MALANG',
    symbol: 'sun'
  },
  {
    name: 'unisma.svg',
    color1: '#065F46', // Green
    color2: '#10B981', // Light Green
    accent: '#FFFFFF',
    text: 'UNISMA',
    sub: 'MALANG',
    symbol: 'star'
  },
  {
    name: 'unmer.svg',
    color1: '#991B1B', // Maroon
    color2: '#FBBF24', // Gold
    accent: '#FFFFFF',
    text: 'UNMER',
    sub: 'MALANG',
    symbol: 'shield'
  },
  {
    name: 'itn.svg',
    color1: '#1E3A8A', // Blue
    color2: '#EA580C', // Orange
    accent: '#FFFFFF',
    text: 'ITN',
    sub: 'MALANG',
    symbol: 'gear'
  },
  {
    name: 'umsida.svg',
    color1: '#0284C7', // Sky Blue
    color2: '#EAB308', // Gold
    accent: '#FFFFFF',
    text: 'UMSIDA',
    sub: 'SIDOARJO',
    symbol: 'sun'
  },
  {
    name: 'umg.svg',
    color1: '#0369A1', // Blue
    color2: '#16A34A', // Green
    accent: '#FFFFFF',
    text: 'UMG',
    sub: 'GRESIK',
    symbol: 'sun'
  },
  {
    name: 'unj.svg',
    color1: '#15803D', // Green
    color2: '#FACC15', // Gold
    accent: '#FFFFFF',
    text: 'UNJ',
    sub: 'JAKARTA',
    symbol: 'torch'
  },
  {
    name: 'uin-jakarta.svg',
    color1: '#047857', // Emerald
    color2: '#059669', // Mint
    accent: '#FFFFFF',
    text: 'UIN JAKARTA',
    sub: 'SYARIF HIDAYATULLAH',
    symbol: 'dome'
  },
  {
    name: 'pnj.svg',
    color1: '#1D4ED8', // Royal Blue
    color2: '#EAB308', // Yellow
    accent: '#FFFFFF',
    text: 'PNJ',
    sub: 'JAKARTA',
    symbol: 'gear'
  },
  {
    name: 'polimedia.svg',
    color1: '#4F46E5', // Indigo
    color2: '#EC4899', // Pink
    accent: '#FFFFFF',
    text: 'POLIMEDIA',
    sub: 'KREATIF',
    symbol: 'palette'
  },
  {
    name: 'untirta.svg',
    color1: '#991B1B', // Maroon
    color2: '#EAB308', // Gold
    accent: '#FFFFFF',
    text: 'UNTIRTA',
    sub: 'BANTEN',
    symbol: 'flame'
  },
  {
    name: 'binus.svg',
    color1: '#C2410C', // Binus Orange
    color2: '#0284C7', // Binus Blue
    accent: '#FFFFFF',
    text: 'BINUS',
    sub: 'UNIVERSITY',
    symbol: 'circle'
  },
  {
    name: 'trisakti.svg',
    color1: '#1E3A8A', // Blue
    color2: '#991B1B', // Red
    accent: '#FFFFFF',
    text: 'TRISAKTI',
    sub: 'JAKARTA',
    symbol: 'spear'
  },
  {
    name: 'untar.svg',
    color1: '#BE123C', // Rose/Red
    color2: '#F59E0B', // Amber
    accent: '#FFFFFF',
    text: 'UNTAR',
    sub: 'TARUMANAGARA',
    symbol: 'book'
  },
  {
    name: 'atmajaya.svg',
    color1: '#1E40AF', // Blue
    color2: '#B45309', // Bronze
    accent: '#FFFFFF',
    text: 'ATMA JAYA',
    sub: 'JAKARTA',
    symbol: 'shield'
  },
  {
    name: 'mercubuana.svg',
    color1: '#047857', // Forest Green
    color2: '#2563EB', // Blue
    accent: '#FFFFFF',
    text: 'MERCU BUANA',
    sub: 'JAKARTA',
    symbol: 'globe'
  },
  {
    name: 'gunadarma.svg',
    color1: '#581C87', // Purple
    color2: '#F59E0B', // Gold
    accent: '#FFFFFF',
    text: 'GUNADARMA',
    sub: 'UNIVERSITY',
    symbol: 'lotus'
  },
  {
    name: 'uhamka.svg',
    color1: '#065F46', // Deep Green
    color2: '#D97706', // Yellow Gold
    accent: '#FFFFFF',
    text: 'UHAMKA',
    sub: 'JAKARTA',
    symbol: 'sun'
  }
];

const dir = path.resolve('public/images/logos');
if (!fs.existsSync(dir)) {
  fs.mkdirSync(dir, { recursive: true });
}

logos.forEach(l => {
  const filePath = path.join(dir, l.name);
  if (fs.existsSync(filePath)) {
    console.log(`Skipping existing: ${l.name}`);
    return;
  }

  const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <linearGradient id="grad-${l.text.replace(/\s+/g, '')}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="${l.color1}" />
      <stop offset="100%" stop-color="${l.color2}" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="4" flood-opacity="0.15" />
    </filter>
  </defs>
  <circle cx="100" cy="100" r="92" fill="url(#grad-${l.text.replace(/\s+/g, '')})" filter="url(#shadow)" />
  <circle cx="100" cy="100" r="82" fill="none" stroke="${l.accent}" stroke-width="3" stroke-opacity="0.8" />
  <circle cx="100" cy="100" r="76" fill="${l.color1}" fill-opacity="0.2" />
  <g fill="${l.accent}">
    <path d="M100 45 L115 75 L145 80 L120 102 L128 135 L100 118 L72 135 L80 102 L55 80 L85 75 Z" fill-opacity="0.25" />
    <circle cx="100" cy="90" r="28" fill="${l.accent}" />
    <circle cx="100" cy="90" r="23" fill="${l.color1}" />
    <text x="100" y="96" font-family="Arial, sans-serif" font-weight="900" font-size="20" fill="${l.accent}" text-anchor="middle" letter-spacing="1">${l.text.slice(0, 3)}</text>
    <text x="100" y="142" font-family="Arial, sans-serif" font-weight="bold" font-size="14" fill="${l.accent}" text-anchor="middle" letter-spacing="1.5">${l.text}</text>
    <text x="100" y="158" font-family="Arial, sans-serif" font-weight="600" font-size="9" fill="${l.color2}" text-anchor="middle" letter-spacing="2">${l.sub}</text>
  </g>
</svg>`;

  fs.writeFileSync(filePath, svgContent, 'utf8');
  console.log(`Generated: ${l.name}`);
});
console.log('Done generating SVG logos!');

